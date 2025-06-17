@extends('frontend.layouts.app')

@section('title', 'Notifikasi Saya')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Notifikasi Saya</h2>
                <div>
                    @if($notifikasiList->where('sudah_dibaca', false)->count() > 0)
                    <form action="{{ route('notifikasi.read') }}" method="POST" class="d-inline me-2">
                        @csrf
                        <input type="hidden" name="id" value="all">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-check-double me-1"></i> Tandai Semua Dibaca
                        </button>
                    </form>
                    @endif
                    @if($notifikasiList->total() > 0) {{-- Gunakan total() untuk pagination --}}
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnHapusTerpilih" style="display: none;">
                        <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih
                    </button>
                    @endif
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if($notifikasiList->isEmpty())
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-center text-muted fs-5 py-5">
                        <i class="fas fa-bell-slash fa-3x mb-3"></i><br>
                        Tidak ada notifikasi untuk Anda.
                    </p>
                </div>
            </div>
            @else
            <form id="formHapusNotifikasi" action="{{ route('notifikasi.hapus.terpilih') }}" method="POST">
                @csrf
                @if($notifikasiList->total() > 0) {{-- Tampilkan hanya jika ada notifikasi --}}
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="selectAllNotifCheckbox">
                    <label class="form-check-label" for="selectAllNotifCheckbox">Pilih Semua Notifikasi</label>
                </div>
                @endif
                <div class="list-group shadow-sm">
                    @foreach($notifikasiList as $notif)
                    <div class="list-group-item list-group-item-action {{ !$notif->sudah_dibaca ? 'list-group-item-primary bg-light-subtle' : '' }}" aria-current="{{ !$notif->sudah_dibaca ? 'true' : 'false' }}">
                        <div class="d-flex w-100 align-items-start">
                            <div class="form-check me-3 pt-1">
                                <input class="form-check-input notif-checkbox" type="checkbox" name="selected_notif_ids[]" value="{{ $notif->id }}" id="notif_check_{{ $notif->id }}">
                            </div>
                            <a href="{{ $notif->link_aksi ?? '#' }}" class="text-decoration-none text-dark flex-grow-1">
                                <div class="d-flex w-100 align-items-start">
                                    <div class="me-3 pt-1">
                                        @php
                                        $iconClass = 'fa-bell'; // Default icon
                                        $iconColor = 'text-secondary';
                                        if (str_contains($notif->tipe_notifikasi, 'pembayaran')) {
                                        $iconClass = 'fa-credit-card';
                                        $iconColor = 'text-warning';
                                        } elseif (str_contains($notif->tipe_notifikasi, 'unggah_bukti')) {
                                        $iconClass = 'fa-upload';
                                        $iconColor = 'text-info';
                                        } elseif (str_contains($notif->tipe_notifikasi, 'konfirmasi_pembayaran')) {
                                        $iconClass = 'fa-hourglass-half';
                                        $iconColor = 'text-purple'; // Anda mungkin perlu menambahkan kelas warna ini di CSS
                                        } elseif (str_contains($notif->tipe_notifikasi, 'proses')) {
                                        $iconClass = 'fa-box-open';
                                        $iconColor = 'text-info';
                                        } elseif (str_contains($notif->tipe_notifikasi, 'kirim')) {
                                        $iconClass = 'fa-truck';
                                        $iconColor = 'text-primary';
                                        } elseif (str_contains($notif->tipe_notifikasi, 'selesai') || str_contains($notif->tipe_notifikasi, 'sampai')) {
                                        $iconClass = 'fa-check-circle';
                                        $iconColor = 'text-success';
                                        } elseif (str_contains($notif->tipe_notifikasi, 'dibatalkan')) {
                                        $iconClass = 'fa-times-circle';
                                        $iconColor = 'text-danger';
                                        }
                                        @endphp
                                        <i class="fas {{ $iconClass }} fa-lg {{ $iconColor }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1 fs-6 fw-bold">{{ $notif->judul }}</h5>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1 small">{{ $notif->pesan }}</p>
                                        @if(!$notif->sudah_dibaca)
                                        <form action="{{ route('notifikasi.read') }}" method="POST" class="mt-2 d-inline-block" onclick="event.stopPropagation();">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $notif->id }}">
                                            <button type="submit" class="btn btn-sm btn-link p-0 text-primary">
                                                <small><i class="fas fa-check me-1"></i>Tandai sudah dibaca</small>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </form>
            <div class="mt-4 d-flex justify-content-center">
                {{ $notifikasiList->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.notif-checkbox');
        const btnHapusTerpilih = document.getElementById('btnHapusTerpilih');
        const formHapusNotifikasi = document.getElementById('formHapusNotifikasi');
        const selectAllCheckbox = document.getElementById('selectAllNotifCheckbox');

        function toggleHapusTerpilihButton() {
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            if (btnHapusTerpilih) {
                btnHapusTerpilih.style.display = anyChecked ? 'inline-block' : 'none';
            }
        }
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                toggleHapusTerpilihButton();
                // Jika ada checkbox individu yang tidak dicentang, maka "Pilih Semua" juga tidak dicentang
                if (selectAllCheckbox && !this.checked) {
                    selectAllCheckbox.checked = false;
                }
                // Jika semua checkbox individu dicentang, maka "Pilih Semua" juga dicentang
                if (selectAllCheckbox && Array.from(checkboxes).every(cb => cb.checked)) {
                    selectAllCheckbox.checked = true;
                }
            });
        });

        if (btnHapusTerpilih && formHapusNotifikasi) {
            btnHapusTerpilih.addEventListener('click', function() {
                const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                if (selectedCount > 0 && confirm(`Apakah Anda yakin ingin menghapus ${selectedCount} notifikasi yang dipilih?`)) {
                    formHapusNotifikasi.submit();
                } else if (selectedCount === 0) {
                    alert('Pilih setidaknya satu notifikasi untuk dihapus.');
                    formHapusNotifikasi.submit();
                }
            });
        }
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleHapusTerpilihButton();
            });
        }

        // Panggil sekali saat load untuk inisialisasi tombol
        toggleHapusTerpilihButton();
    });
</script>