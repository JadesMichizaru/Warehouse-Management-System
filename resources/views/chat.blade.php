<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot Integration</title>
    <!-- Pastikan token CSRF ada di meta tag -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Contoh styling sederhana, bisa diganti pakai Tailwind/Bootstrap yang sudah ada di repo kamu -->
    <style>
        body { font-family: sans-serif; background-color: #f4f4f9; padding: 20px; }
        #chat-box { background: white; border-radius: 8px; padding: 20px; height: 400px; overflow-y: auto; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .message { margin-bottom: 15px; padding: 10px; border-radius: 5px; max-width: 80%; }
        .user-msg { background: #e0f2fe; margin-left: auto; text-align: right; }
        .bot-msg { background: #f1f5f9; margin-right: auto; }
        .input-area { display: flex; gap: 10px; }
        input[type="text"] { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #0284c7; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:disabled { background: #94a3b8; }

        /* Styling untuk suggestion chips */
        #suggestions-container {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            overflow-x: auto; /* Agar bisa digeser ke samping jika layarnya kecil */
            padding-bottom: 5px;
        }
        .suggestion-chip {
            background: #e2e8f0;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.2s;
        }
        .suggestion-chip:hover {
            background: #cbd5e1;
        }
    </style>
</head>
<body>

    <div style="max-width: 600px; margin: 0 auto;">
        <h2>Asisten AI</h2>

        <div id="chat-box">
            <div class="message bot-msg">Halo! Ada yang bisa saya bantu terkait sistem ini?</div>
        </div>

        <!-- Tempat menampilkan suggestion -->
                <div id="suggestions-container"></div>

                <div class="input-area">
                    <input type="text" id="message-input" placeholder="Ketik pesan di sini..." onkeypress="handleEnter(event)">
                    <button id="send-btn" onclick="sendMessage()">Kirim</button>
                </div>
    </div>

    <script>
        async function sendMessage() {
            const inputField = document.getElementById('message-input');
            const chatBox = document.getElementById('chat-box');
            const sendBtn = document.getElementById('send-btn');
            const message = inputField.value.trim();

            if (!message) return;

            // 1. Tampilkan pesan user di layar
            appendMessage(message, 'user-msg');
            inputField.value = '';

            // Disable tombol saat loading
            sendBtn.disabled = true;
            sendBtn.innerText = 'Mengetik...';

            try {
                // 2. Kirim request ke Laravel Backend
                const response = await fetch("{{ route('chat.send') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();

                // 3. Tampilkan balasan AI
                if (data.status === 'success') {
                    // Render markdown sederhana (opsional, karena Gemini sering membalas dengan markdown)
                    appendMessage(data.reply, 'bot-msg');
                } else {
                    appendMessage("Error: " + data.message, 'bot-msg');
                }

            } catch (error) {
                appendMessage("Terjadi kesalahan koneksi.", 'bot-msg');
            } finally {
                // Enable tombol kembali
                sendBtn.disabled = false;
                sendBtn.innerText = 'Kirim';
                // Scroll ke bawah
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        function appendMessage(text, className) {
            const chatBox = document.getElementById('chat-box');
            const msgDiv = document.createElement('div');
            msgDiv.className = `message ${className}`;
            msgDiv.innerText = text; // Gunakan innerText agar aman dari XSS
            chatBox.appendChild(msgDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function handleEnter(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        // Fungsi untuk mengambil dan merender suggestions dari backend
                async function loadSuggestions() {
                    try {
                        const response = await fetch("{{ route('chat.suggestions') }}");
                        const data = await response.json();

                        if (data.status === 'success') {
                            const container = document.getElementById('suggestions-container');

                            data.data.forEach(text => {
                                const btn = document.createElement('button');
                                btn.className = 'suggestion-chip';
                                btn.innerText = text;

                                // Jika diklik, masukkan ke input dan langsung kirim
                                btn.onclick = () => {
                                    document.getElementById('message-input').value = text;
                                    sendMessage();
                                    // Opsional: Sembunyikan container setelah salah satu diklik
                                    // container.style.display = 'none';
                                };

                                container.appendChild(btn);
                            });
                        }
                    } catch (error) {
                        console.error("Gagal memuat suggestions", error);
                    }
                }

                // Jalankan fungsi saat halaman selesai dimuat
                document.addEventListener("DOMContentLoaded", () => {
                    loadSuggestions();
                });
    </script>
</body>
</html>
