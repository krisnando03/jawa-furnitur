@extends('frontend.layouts.app')

@section('title', 'Unggah Bukti Pembayaran - Pesanan ' . $pesanan->nomor_pesanan)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Unggah Bukti Pembayaran</h4>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Nomor Pesanan:</strong> {{ $pesanan->nomor_pesanan }}</p>
                    <p class="mb-2"><strong>Total Tagihan:</strong> <span class="fw-bold text-danger">Rp {{ number_format($pesanan->total_pembayaran, 0, ',', '.') }}</span></p>
                    @if($pesanan->metode_pembayaran)
                    <p class="mb-3"><strong>Metode Pembayaran Dipilih:</strong> {{ $pesanan->metode_pembayaran }}</p>
                    @endif

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if (Session::has('success'))
                    <div class="alert alert-success">
                        {{ Session::get('success') }}
                    </div>
                    @endif
                    @if (Session::has('error'))
                    <div class="alert alert-danger">
                        {{ Session::get('error') }}
                    </div>
                    @endif

                    <form action="{{ route('pesanan.prosesUnggahBukti', ['id' => $pesanan->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="bukti_pembayaran_file" class="form-label">Pilih File Bukti Pembayaran <span class="text-danger">*</span></label>
                            <input class="form-control @error('bukti_pembayaran_file') is-invalid @enderror" type="file" id="bukti_pembayaran_file" name="bukti_pembayaran_file" accept="image/jpeg,image/png,image/jpg,image/gif" required>
                            @error('bukti_pembayaran_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Format yang diterima: JPG, JPEG, PNG, GIF. Maksimal ukuran: 2MB.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-upload me-2"></i>Unggah Bukti
                            </button>
                            <a href="{{ route('pesanan.saya.detail', ['id' => $pesanan->id]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection