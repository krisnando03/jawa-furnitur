@extends('frontend.layouts.app')

@section('title', 'Beranda')

@section('content')
<!-- Bagian Informasi Alamat Pengguna -->
@if(session('pelanggan') && isset($alamatUtama) && $alamatUtama)
<section class="py-3 bg-light-subtle border-bottom mb-4">
    <div class="container">
        <div class="d-flex align-items-center">
            <i class="fas fa-map-marker-alt fa-lg text-success me-2"></i>
            <span class="text-muted me-2">Dikirim ke:</span>
            <span class="fw-semibold">
                {{ Str::limit($alamatUtama->alamat_lengkap, 50) }}, {{ $alamatUtama->kota }}, {{ $alamatUtama->provinsi }}, {{ $alamatUtama->kode_pos }}
            </span>
            {{-- Tautan untuk mengubah alamat bisa ditambahkan di sini jika perlu --}}
            {{-- <a href="{{ route('profile.show') }}#alamat" class="ms-auto btn btn-sm btn-outline-primary">Ubah Alamat</a> --}}
        </div>
    </div>
</section>
@elseif(session('pelanggan'))
<section class="py-3 bg-light-subtle border-bottom mb-4">
    <div class="container">
        <a href="{{ route('profile.show') }}#tambah-alamat-baru" class="text-decoration-none d-flex align-items-center"> {{-- Arahkan ke bagian tambah alamat di profil --}}
            <i class="fas fa-map-marker-alt fa-lg text-primary me-2"></i>
            <span class="text-muted me-2">Anda belum mengatur alamat utama.</span> <span class="fw-semibold text-primary">Atur Alamat Pengiriman</span>
        </a>
    </div>
</section>
@endif

<!-- Bagian Produk dengan Filter (Menghapus sidebar filter sebelumnya) -->
<section class="py-5">
    <div class="container position-relative"> {{-- Tambahkan position-relative jika filter akan absolute thd container --}}
        <div class="row">
            <!-- Kolom Slider Foto dengan margin bawah lebih besar -->
            <div class="col-12 mb-5" style="margin-bottom: 5rem !important;">
                <div id="homePageSlider" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#homePageSlider" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#homePageSlider" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#homePageSlider" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div>
                    <div class="carousel-inner rounded">
                        <div class="carousel-item active" data-bs-interval="3000">
                            {{-- Gambar furnitur ruang tamu modern --}}
                            <img src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1200&h=400&q=80" class="d-block w-100" alt="Ruang Tamu Modern" style="height: 400px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block">
                                <h5>Koleksi Terbaru</h5>
                                <p>Temukan furnitur impian Anda dengan desain terkini.</p>
                            </div>
                        </div>
                        <div class="carousel-item" data-bs-interval="3000">
                            {{-- Gambar detail kursi kayu elegan --}}
                            <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1200&h=400&q=80" class="d-block w-100" alt="Kursi Kayu Elegan" style="height: 400px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block">
                                <h5>Kualitas Terbaik</h5>
                                <p>Dibuat dengan material pilihan untuk daya tahan maksimal.</p>
                            </div>
                        </div>
                        <div class="carousel-item" data-bs-interval="3000">
                            {{-- Gambar interior kamar tidur nyaman --}}
                            <img src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1200&h=400&q=80" class="d-block w-100" alt="Kamar Tidur Nyaman" style="height: 400px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Kolom Daftar Produk -->
            <div class="col-12" id="productListingColumn">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0">Jelajahi Produk Kami</h2>
                    <button class="btn btn-outline-primary" id="toggleFilterBtn" type="button">
                        <i class="fas fa-filter me-2"></i> <span id="toggleFilterBtnText">Sembunyikan</span> Filter
                    </button>
                </div>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4"> {{-- Changed to 4 columns --}}
                    @forelse ($products as $product)
                    <div class="col">
                        <a href="{{ route('produk.detail', ['id' => $product->id]) }}" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm product-card"> {{-- Menghapus text-center dari card utama --}}
                                <img src="{{ $product->gambar_produk_url }}" class="card-img-top p-3" alt="{{ $product->nama_produk }}" style="height: 200px; object-fit: contain;">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title fs-6 fw-semibold text-dark mb-0 me-2" style="min-height: 40px;">{{ Str::limit($product->nama_produk, 45) }}</h5>
                                        <p class="fw-bold text-end mb-0 flex-shrink-0" style="color: #0d6efd;">Rp{{ number_format($product->harga, 0, ',', '.') }}</p> {{-- Menggunakan warna biru standar Bootstrap --}}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Produk tidak ditemukan dengan kriteria pencarian Anda.
                        </div>
                    </div>
                    @endforelse
                </div>
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            </div>

            <!-- Kolom Filter (akan diposisikan sebagai overlay) -->
            <div id="filterColumn"> {{-- Hapus class col-lg-3 dan order-lg-2 --}}
                <div class="card shadow-sm"> {{-- Hapus style sticky-top --}}
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Produk</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('home') }}" method="GET" id="filterForm">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kategori</label>
                                @php $selectedCategorySlug = $request->input('category_filter', ''); @endphp

                                <!-- Opsi Semua Kategori -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="category_filter" id="cat_all" value="" {{ $selectedCategorySlug == '' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cat_all">
                                        Semua Kategori
                                    </label>
                                </div>

                                @forelse($kategoriList as $kategori)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="category_filter" value="{{ $kategori->slug }}" id="cat_{{ $kategori->slug }}" {{ $selectedCategorySlug == $kategori->slug ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cat_{{ $kategori->slug }}">
                                        {{ $kategori->nama_kategori }}
                                    </label>
                                </div>
                                @empty
                                <p class="text-muted small">Tidak ada kategori tersedia.</p>
                                @endforelse
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Price</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="min_price" placeholder="Min" value="{{ $request->input('min_price') }}">
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="max_price" placeholder="Max" value="{{ $request->input('max_price') }}">
                                </div>
                            </div>

                            <hr>

                            {{-- Color filter example (currently commented out in original) --}}
                            <!-- <div class="mb-3"> ... </div> <hr> -->

                            <div class="mb-3">
                                <label for="sort_by" class="form-label fw-semibold">Sort by</label>
                                <select class="form-select" id="sort_by" name="sort_by">
                                    <option value="" {{ !$request->input('sort_by') ? 'selected' : '' }}>Relevan</option>
                                    <option value="price_asc" {{ $request->input('sort_by') == 'price_asc' ? 'selected' : '' }}>Price: Lowest to Highest</option>
                                    <option value="price_desc" {{ $request->input('sort_by') == 'price_desc' ? 'selected' : '' }}>Price: Highest to Lowest</option>
                                </select>
                            </div>

                            @if($request->hasAny(['search', 'min_price', 'max_price', 'sort_by', 'category_filter', 'colors']))
                            <div class="d-grid mt-2">
                                <a href="{{ route('home') }}" class="btn btn-outline-secondary">Reset Filter</a>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    #filterColumn {
        position: fixed;
        top: 55%;
        /* Posisi tengah vertikal */
        left: 85%;
        /* Posisi tengah horizontal */
        transform: translate(-50%, -50%);
        /* Geser ke tengah sebenarnya */
        width: 350px;
        /* Lebar filter (sedikit diperbesar) */
        max-height: 90vh;
        /* Tinggi maksimal filter (90% dari tinggi layar) */
        background-color: #fff;
        z-index: 1050;
        /* Pastikan di atas konten lain (misal navbar 1000) */
        box-shadow: -3px 0 10px rgba(0, 0, 0, 0.1);
        transition: right 0.3s ease-in-out;
        /* Awalnya sembunyikan dengan opacity 0 dan pointer-events none */
        opacity: 0;
        pointer-events: none;

    }

    #filterColumn.active {
        opacity: 1;
        pointer-events: auto;

    }

    #filterColumn .card {
        width: 100%;
        /* Card mengisi lebar #filterColumn */
        /* height: 100%; dihapus, biarkan tinggi card menyesuaikan kontennya */
        border: none;
        box-shadow: none;
        border-radius: 0;
        display: flex;
        flex-direction: column;
        /* flex-grow: 1; dihapus */
    }

    #filterColumn .card-body {
        overflow-y: auto;
        /* Scroll jika konten filter panjang */
        flex-grow: 1;
        /* Agar card-body mengisi sisa ruang di dalam card setelah header */
        padding-bottom: 20px;
        /* Estetika, padding di bawah konten filter */
        font-size: 1.3rem;
        /* Ukuran font konten filter */

    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');

        // 1. Penanganan untuk label filter Warna (jika ada dan digunakan)
        // Menggabungkan logika visual toggle dan submit form
        const colorLabels = document.querySelectorAll('label[for^="color_"]');
        colorLabels.forEach(label => {
            label.addEventListener('click', function(event) {
                event.preventDefault(); // Mencegah aksi default label
                const checkboxId = this.htmlFor;
                const checkbox = document.getElementById(checkboxId);

                if (checkbox) {
                    checkbox.checked = !checkbox.checked; // Toggle checkbox terkait

                    // Update tampilan visual label (contoh: border)
                    if (checkbox.checked) {
                        this.style.border = '2px solid #0d6efd';
                    } else {
                        this.style.border = ''; // Kembali ke style default
                    }

                    // Submit form setelah perubahan
                    if (filterForm) {
                        setTimeout(() => filterForm.submit(), 50); // Timeout kecil untuk memastikan state checkbox terupdate
                    }
                }
            });
        });

        // 2. Auto-submit untuk elemen form filter umum
        if (filterForm) {
            const formElements = filterForm.elements;
            for (let i = 0; i < formElements.length; i++) {
                const element = formElements[i];

                // Lewati checkbox warna karena sudah ditangani oleh event listener pada labelnya
                if (element.name && element.name.startsWith('colors[')) {
                    continue;
                }

                if (element.type === 'checkbox' || element.type === 'select-one' || element.type === 'radio') {
                    element.addEventListener('change', function() {
                        filterForm.submit();
                    });
                } else if (element.type === 'number' || element.type === 'text') {
                    element.addEventListener('blur', function() { // Submit saat fokus hilang dan ada perubahan
                        if (this.defaultValue !== this.value) { // Cek apakah nilai benar-benar berubah
                            filterForm.submit();
                        }
                    });
                    element.addEventListener('keypress', function(event) { // Submit saat tekan Enter
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            filterForm.submit();
                        }
                    });
                }
            }
        }

        // 3. Toggle Filter Column Visibility (sebagai overlay)
        const filterColumn = document.getElementById('filterColumn');
        const toggleFilterBtn = document.getElementById('toggleFilterBtn');
        const toggleFilterBtnText = document.getElementById('toggleFilterBtnText');

        if (toggleFilterBtn && filterColumn && toggleFilterBtnText) {
            // Kondisi awal: filter tersembunyi (oleh CSS), tombol bertuliskan "Tampilkan"
            toggleFilterBtnText.textContent = 'Tampilkan';

            toggleFilterBtn.addEventListener('click', () => {
                filterColumn.classList.toggle('active'); // Class 'active' akan mengontrol via CSS

                if (filterColumn.classList.contains('active')) {
                    toggleFilterBtnText.textContent = 'Sembunyikan';
                } else {
                    toggleFilterBtnText.textContent = 'Tampilkan';
                }
            });

            // Menutup filter jika klik di luar area filter
            document.addEventListener('click', function(event) {
                const isClickInsideFilter = filterColumn.contains(event.target);
                const isClickOnToggleButton = toggleFilterBtn.contains(event.target);

                if (filterColumn.classList.contains('active') && !isClickInsideFilter && !isClickOnToggleButton) {
                    filterColumn.classList.remove('active');
                    toggleFilterBtnText.textContent = 'Tampilkan';
                }
            });
        }
    });
</script>
@endpush
@endsection