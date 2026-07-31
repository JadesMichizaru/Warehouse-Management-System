<?php

namespace App\Http\Controllers;

use App\Models\InboundDetails;
use App\Models\OutbondDetails;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $request->input('message');
        $apiKey = env('GEMINI_API_KEY');

        /* =========================================================
           1. AMBIL DATA DARI DATABASE (RETRIEVAL)
           ========================================================= */

        // A. Ambil Data Stok Produk & Kalkulasi Per Kategori
        $products = Products::get();

        $stockInfo = "Data Stok Produk Saat Ini:\n";
        $categoryTotals = [];

        if ($products->isEmpty()) {
            $stockInfo .= "- Stok atau category tidak tersedia.\n";
        } else {
            foreach ($products as $p) {
                // MENGAMBIL LANGSUNG DARI KOLOM 'category' DI TABEL PRODUCTS
                // Jika kolom category kosong (null), maka akan diisi 'Tanpa Kategori'
                $catName = $p->category ?: 'Tanpa Kategori';

                $stockInfo .= "- Produk: {$p->name}, Kategori: {$catName}, Sisa Stok: {$p->stock}\n";

                // Menjumlahkan stok berdasarkan kategori
                if (!isset($categoryTotals[$catName])) {
                    $categoryTotals[$catName] = 0;
                }
                $categoryTotals[$catName] += $p->stock;
            }
        }

        // B. Tambahkan Rangkuman Kategori ke Konteks
        $stockInfo .= "\nTotal Stok per Kategori:\n";
        foreach ($categoryTotals as $cat => $total) {
            $stockInfo .= "- Kategori {$cat}: Total ada {$total} stok produk\n";
        }

        // C. Ambil Data Inbound (Barang Masuk) & Supplier
        // Mengambil 10 data terbaru agar AI tidak kelebihan beban membaca data
        $inbounds = InboundDetails::with(['products', 'suppliers'])->latest()->take(10)->get();
        $inboundInfo = "\nRiwayat Barang Masuk (Inbound) Terbaru:\n";
        if ($inbounds->isEmpty()) {
            $inboundInfo .= "- Belum ada data barang masuk.\n";
        } else {
            foreach ($inbounds as $in) {
                $prodName = $in->products ? $in->products->name : 'Unknown';
                $supName = $in->suppliers ? $in->suppliers->name : 'Unknown';
                // Sesuaikan kolom tanggal dan kuantitas (misal $in->qty atau $in->quantity)
                $inboundInfo .= "- {$prodName} masuk sebanyak {$in->quantity} dari supplier {$supName} pada tanggal {$in->created_at->format('Y-m-d')}.\n";
            }
        }

        // D. Ambil Data Outbound (Barang Keluar)
        $outbounds = OutbondDetails::with('products','customers')->latest()->take(10)->get();
        $outboundInfo = "\nRiwayat Barang Keluar (Outbound) Terbaru:\n";
        if ($outbounds->isEmpty()) {
            $outboundInfo .= "- Belum ada data barang keluar.\n";
        } else {
            foreach ($outbounds as $out) {
                $prodName = $out->products ? $out->products->name : 'Unknown';
                $custCode = $out->customers ? $out->customers->code : 'Unknown';
                $outboundInfo .= "- {$prodName} keluar sebanyak {$out->quantity} dan dikirimkan ke customers dengan kode {$custCode} pada tanggal {$out->created_at->format('Y-m-d')}.\n";
            }
        }

        /* =========================================================
           2. SUSUN SYSTEM INSTRUCTION (GABUNGKAN DATA KE AI)
           ========================================================= */
        $systemPrompt = "Kamu adalah AI Asisten Gudang (Warehouse Management System).
        Tugas utamamu adalah menjawab pertanyaan seputar stok, barang masuk (inbound), barang keluar (outbound), dan supplier berdasarkan data aktual berikut ini.

        ATURAN PENTING:
        1. Selalu gunakan data aktual di bawah ini untuk menjawab.
        2. Jika user bertanya total stok di kategori tertentu (misal: 'kategori baju'), cari di bagian 'Total Stok per Kategori'. Jika kategori yang ditanya tidak ada di data, jawab dengan persis kalimat ini: 'Stok atau category tidak tersedia'.
        3. Jika user bertanya sisa stok spesifik, berikan nama produk dan sisa stoknya.
        4. Jawab dengan ramah, rapi, dan menggunakan Bahasa Indonesia.

        === DATA AKTUAL GUDANG SAAT INI ===
        {$stockInfo}
        {$inboundInfo}
        {$outboundInfo}
        ===================================
        ";

        /* =========================================================
           3. KIRIM REQUEST KE GEMINI API (MENGGUNAKAN VERSI 2.5)
           ========================================================= */
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemPrompt]
                    ]
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $userMessage]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $botReply = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, saya tidak mengerti.';

                return response()->json([
                    'status' => 'success',
                    'reply'  => $botReply,
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Error API: ' . $response->body(),
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSuggestions()
    {
        // Daftar saran pertanyaan sesuai konteks Warehouse Management System
        $suggestions = [
            "Berapa total barang masuk hari ini?",
            "Tampilkan total pengeluaran (barang keluar) hari ini.",
            "Barang apa saja yang stoknya hampir habis?",
            "Tolong buatkan ringkasan aktivitas gudang minggu ini."
        ];

        return response()->json([
            'status' => 'success',
            'data'   => $suggestions
        ]);
    }
}
