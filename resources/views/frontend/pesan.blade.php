@extends('frontend.layouts.app')

@section('title', 'Pesan')

@section('content')
<div class="container py-5">
    <h1>Chat dengan Penjual</h1>

    @if(isset($chatContext))
    <div class="alert alert-info">
        <p class="mb-0">Anda sedang membahas: <strong>{{ $chatContext }}</strong></p>
    </div>
    @endif

    {{-- Menampilkan Informasi Produk Jika Ada Konteks Produk --}}
    @if(isset($produkKonteks) && $produkKonteks)
    <div class="card mb-3 shadow-sm">
        <div class="row g-0">
            <div class="col-md-2 d-flex align-items-center justify-content-center p-2">
                <img src="{{ $produkKonteks->gambar_produk_url }}" class="img-fluid rounded-start" alt="{{ $produkKonteks->nama_produk }}" style="max-height: 80px; object-fit: contain;">
            </div>
            <div class="col-md-10">
                <div class="card-body py-2">
                    <h5 class="card-title fs-6 mb-1">{{ $produkKonteks->nama_produk }}</h5>
                    <p class="card-text mb-0"><small class="text-muted">Klik <a href="{{ route('produk.detail', ['id' => $produkKonteks->id]) }}">di sini</a> untuk melihat detail produk.</small></p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Menampilkan Informasi Pesanan Jika Ada Konteks Pesanan --}}
    @if(isset($pesananKonteks) && $pesananKonteks)
    <div class="card mb-3 shadow-sm">
        <div class="row g-0">
            @php
            // Ambil produk pertama dari detail pesanan untuk ditampilkan gambarnya
            $produkPertamaPesanan = null;
            if ($pesananKonteks->detailPesanan->isNotEmpty()) {
            $produkPertamaPesanan = $pesananKonteks->detailPesanan->first()->produk;
            }
            @endphp
            @if($produkPertamaPesanan && $produkPertamaPesanan->gambar_produk_url)
            <div class="col-md-2 d-flex align-items-center justify-content-center p-2">
                <img src="{{ $produkPertamaPesanan->gambar_produk_url }}" class="img-fluid rounded-start" alt="{{ $produkPertamaPesanan->nama_produk }}" style="max-height: 80px; object-fit: contain;">
            </div>
            @endif
            <div class="{{ $produkPertamaPesanan && $produkPertamaPesanan->gambar_produk_url ? 'col-md-10' : 'col-md-12' }}">
                <div class="card-body py-2">
                    <h5 class="card-title fs-6 mb-1">Pesanan: {{ $pesananKonteks->nomor_pesanan }}</h5>
                    <p class="card-text mb-1">
                        <small class="text-muted">
                            Status: <span class="badge bg-info">{{ Str::title(str_replace('_', ' ', $pesananKonteks->status_pesanan)) }}</span>
                            | Total: Rp {{ number_format($pesananKonteks->total_pembayaran, 0, ',', '.') }}
                        </small>
                    </p>
                    <p class="card-text mb-0">
                        <small class="text-muted">Klik <a href="{{ route('pesanan.saya.detail', ['id' => $pesananKonteks->id]) }}" target="_blank">di sini</a> untuk melihat detail pesanan.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Area untuk menampilkan histori chat --}}
    <div id="chatHistory" class="mb-3 border p-3" style="height: 300px; overflow-y: scroll;">
        @if($chatMessages->isEmpty())
        <p class="text-muted text-center" id="noMessagesText">Belum ada percakapan.</p>
        @else
        @foreach($chatMessages as $msg)
        <div class="mb-2 {{ $msg->pengirim_adalah_admin ? 'text-start' : 'text-end' }}">
            <div class="d-inline-block p-2 rounded {{ $msg->pengirim_adalah_admin ? 'bg-light border' : 'bg-primary text-white' }}" style="max-width: 75%;">
                <p class="mb-1">{{ $msg->isi_pesan }}</p>
                <small class="text-muted {{ $msg->pengirim_adalah_admin ? '' : 'text-white-50' }}" style="font-size: 0.75rem;">
                    {{ \Carbon\Carbon::parse($msg->created_at)->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}
                </small>
            </div>
        </div>
        @endforeach
        @endif
    </div>

    {{-- Form untuk mengirim pesan --}}
    <form id="chatForm" action="{{ route('pesan.kirim') }}" method="POST">
        @csrf
        <input type="hidden" name="id_produk_konteks" value="{{ $currentProductIdKonteks ?? '' }}">
        <input type="hidden" name="nomor_pesanan_konteks" value="{{ $currentOrderIdKonteks ?? '' }}">
        {{-- <input type="hidden" name="receiver_id" value="{{ $receiver ?? 'admin' }}"> --}}

        <div class="input-group">
            <textarea class="form-control" name="isi_pesan" id="chatMessageInput" rows="3" placeholder="Ketik pesan Anda..." required>{{ $prefillText ?? '' }}</textarea>
            <button class="btn btn-primary" type="submit" id="sendChatButton">
                <i class="fas fa-paper-plane"></i> Kirim
            </button>
        </div>
        <div id="chatError" class="text-danger mt-2"></div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Logika JavaScript untuk mengirim dan menerima pesan chat (misalnya menggunakan AJAX atau WebSockets)
    document.addEventListener('DOMContentLoaded', function() {
        const chatHistory = document.getElementById('chatHistory');
        const chatForm = document.getElementById('chatForm');
        const chatMessageInput = document.getElementById('chatMessageInput');
        const sendChatButton = document.getElementById('sendChatButton');
        const chatError = document.getElementById('chatError');
        const noMessagesText = document.getElementById('noMessagesText');

        // Scroll ke bawah chat history saat halaman dimuat
        chatHistory.scrollTop = chatHistory.scrollHeight;

        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = chatMessageInput.value.trim();
            if (!message) return;

            sendChatButton.disabled = true;
            chatError.textContent = '';

            const formData = new FormData(chatForm);

            fetch(chatForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) { // Memeriksa status HTTP 4xx/5xx
                        return response.text().then(text => {
                            console.error('Server Error Response Text:', text); // Log respons mentah
                            let errorData = {
                                error: `Server merespons dengan status ${response.status}. Coba lagi nanti.`
                            };
                            try {
                                // Coba parse sebagai JSON jika itu adalah error validasi Laravel (422)
                                if (response.status === 422) {
                                    errorData = JSON.parse(text);
                                }
                            } catch (e) {
                                // Biarkan errorData default jika parsing gagal
                            }
                            throw errorData; // Lemparkan objek error
                        });
                    }
                    return response.json(); // Hanya panggil .json() jika respons.ok
                })
                .then(data => {
                    if (data.success && data.message) {
                        appendMessageToChat(data.message.isi_pesan, false, data.message.created_at); // false = bukan dari admin
                        chatMessageInput.value = '';
                        if (noMessagesText) noMessagesText.remove(); // Hapus teks "Belum ada percakapan"
                    } else if (data.error) { // Tangani pesan error spesifik dari server
                        chatError.textContent = data.error;
                    } else if (data.errors) { // Tangani error validasi Laravel
                        chatError.textContent = Object.values(data.errors).flat().join(' ');
                    } else {
                        chatError.textContent = 'Gagal mengirim pesan: Respons tidak dikenal.';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    // Menampilkan pesan error yang lebih informatif
                    chatError.textContent = error.error || (error.message ? (error.message.includes('Failed to fetch') ? 'Terjadi kesalahan jaringan. Periksa koneksi Anda.' : `Error: ${error.message}`) : 'Terjadi kesalahan yang tidak diketahui. Silakan coba lagi.');
                })
                .finally(() => {
                    sendChatButton.disabled = false;
                    chatMessageInput.focus();
                });
        });

        function appendMessageToChat(message, isAdmin, timestamp) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('mb-2');
            messageDiv.classList.add(isAdmin ? 'text-start' : 'text-end');

            const bubbleDiv = document.createElement('div');
            bubbleDiv.classList.add('d-inline-block', 'p-2', 'rounded');
            bubbleDiv.classList.add(isAdmin ? 'bg-light' : 'bg-primary', isAdmin ? 'border' : 'text-white');
            bubbleDiv.style.maxWidth = '75%';

            const pMessage = document.createElement('p');
            pMessage.classList.add('mb-1');
            pMessage.textContent = message;

            const smallTimestamp = document.createElement('small');
            smallTimestamp.classList.add('text-muted', isAdmin ? '' : 'text-white-50');
            smallTimestamp.style.fontSize = '0.75rem';
            smallTimestamp.textContent = new Date(timestamp).toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            }) + ', ' + new Date(timestamp).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short'
            });

            bubbleDiv.appendChild(pMessage);
            bubbleDiv.appendChild(smallTimestamp);
            messageDiv.appendChild(bubbleDiv);
            chatHistory.appendChild(messageDiv);
            chatHistory.scrollTop = chatHistory.scrollHeight; // Auto scroll ke bawah
        }
    });
</script>
@endpush