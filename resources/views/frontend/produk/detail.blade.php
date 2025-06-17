@extends('frontend.layouts.app')

@section('title', $produk->nama_produk)

@section('content')
<div class="container py-4">
    <!-- User Info Bar -->
    <!-- <div class="mb-4">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/profile-pic.jpg') }}" alt="Profile Picture" class="rounded-circle me-2" width="40" height="40">
            <span class="fw-medium">James Anderson</span>
        </div>
    </div> -->

    <div class="row">
        <!-- Product Images Section -->
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="bg-white rounded p-3 position-relative">
                <!-- Main Product Image -->
                <div class="text-center mb-3">
                    <img id="mainProductImageDetail" class="card-img-top mb-5 mb-md-0" src="{{ $produk->gambar_produk_url }}" alt="{{ $produk->nama_produk }}" style="max-height: 500px; object-fit: contain; border: 1px solid #eee; padding: 15px; border-radius: .25rem;" />
                </div>

                <!-- Thumbnail Navigation with Arrows -->
                <div class="position-relative">
                    <button class="position-absolute top-50 start-0 translate-middle-y bg-transparent border-0" id="prevThumb">
                        <i class="fas fa-chevron-left fs-4"></i>
                    </button>

                    <div class="d-flex justify-content-center">
                        {{-- Asumsi $produk->gambar adalah path gambar utama, dan kita akan gunakan itu sebagai thumbnail pertama --}}
                        {{-- Jika ada array gambar thumbnail, loop di sini --}}
                        <div class="mx-2 border border-2 border-primary p-1">
                            <img src="{{ $produk->gambar_produk_url }}" alt="{{ $produk->nama_produk }} - Thumbnail 1" width="70" class="product-thumbnail active" style="height: 70px; object-fit: cover;">
                        </div>
                        {{-- Placeholder untuk thumbnail lain jika ada --}}
                        {{-- <div class="mx-2 border p-1">
                            <img src="{{ asset('assets/img/placeholder-thumb2.jpg') }}" alt="Thumbnail 2 (Placeholder)" width="70" class="product-thumbnail" style="height: 70px; object-fit: cover;">
                    </div>
                    <div class="mx-2 border p-1">
                        <img src="{{ asset('assets/img/placeholder-thumb3.jpg') }}" alt="Thumbnail 3 (Placeholder)" width="70" class="product-thumbnail" style="height: 70px; object-fit: cover;">
                    </div> --}}
                </div>

                <button class="position-absolute top-50 end-0 translate-middle-y bg-transparent border-0" id="nextThumb">
                    <i class="fas fa-chevron-right fs-4"></i>
                </button>
            </div>

            <!-- 3D View Button -->
            <div class="text-center mt-3">
                <button class="btn btn-light btn-sm px-3">View 3d</button>
            </div>
        </div>
    </div>

    <!-- Product Details Section -->
    <div class="col-lg-6">
        <h1 class="display-5 fw-bolder">{{ $produk->nama_produk }}</h1>
        <p class="lead mb-5">{{ $produk->deskripsi_singkat ?? 'Tidak ada deskripsi singkat.' }}</p>

        <!-- Price -->
        <div class="fs-2 mb-4 fw-bold" style="color: #0d6efd;"> {{-- Ukuran font diperbesar dari fs-4 ke fs-3 --}}
            <span>Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
            @if($produk->harga_diskon)
            <span class="text-decoration-line-through text-secondary ms-2">Rp {{ number_format($produk->harga_diskon, 0, ',', '.') }}</span>
            @endif
        </div>

        <!-- Quantity Input and Action Buttons -->
        <div class="d-flex align-items-center mb-5">
            {{-- Quantity Input --}}
            <input class="form-control text-center" id="inputQuantity" name="quantity_display" type="number" value="1" min="1" max="{{ $produk->stok > 0 ? $produk->stok : 1 }}" style="max-width: 6rem; padding-top: 0.5rem; padding-bottom: 0.5rem;" {{ $produk->stok == 0 ? 'disabled' : '' }} />

            {{-- Tombol Konfirmasi Pesanan (jika tidak menggunakan Buy Now langsung) --}}
            @if($produk->stok == 0)
            <button class="btn btn-warning ms-2 d-flex align-items-center px-4 py-2" disabled>
                Stok Habis - Pre Order
                <i class="fas fa-clock ms-2 fs-5"></i>
            </button>
            @elseif(!$kendaraanAvailable)
            <button class="btn btn-warning ms-2 d-flex align-items-center px-4 py-2" disabled>
                Pre Order - Semua kendaraan sedang digunakan
                <i class="fas fa-truck ms-2 fs-5"></i>
            </button>
            @else
            <a href="#" id="confirmOrderBtn" class="btn btn-danger ms-2 d-flex align-items-center px-4 py-2">
                Beli Sekarang
                <i class="fas fa-bag-shopping ms-2 fs-5"></i>
            </a>
            @endif

            {{-- Form untuk tombol TROLI (Add to Cart) --}}
            <form action="{{ route('keranjang.add') }}" method="POST" class="d-inline-block ms-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $produk->id }}">
                {{-- Ambil kuantitas dari input quantity --}}
                <input type="hidden" name="quantity" id="add_to_cart_quantity" value="1">
                <button class="btn btn-success d-flex align-items-center px-4 py-2 btn-add-to-cart" type="submit" {{ $produk->stok == 0 ? 'disabled' : '' }}>
                    {{ $produk->stok == 0 ? 'Stok Habis' : 'Keranjang' }}
                    <i class="fas fa-cart-plus ms-2 fs-5"></i>
                </button>
            </form>
            {{-- Tombol Chat Penjual dipindahkan ke paling kanan --}}
            <a href="{{ route('pesan.index') }}?receiver=admin&product_id={{ $produk->id }}" class="btn btn-outline-secondary d-flex align-items-center px-4 py-2 ms-2" title="Chat Penjual">
                <i class="fas fa-comments fs-5"></i>
            </a>
        </div>

        <hr class="my-4">

        <!-- Additional Information -->
        <div>
            <h5 class="fw-semibold mb-3">Informasi Tambahan:</h5>
            <ul class="list-unstyled text-muted">
                <li class="mb-1"><strong> Kategori: </strong><a href="{{ route('home', ['categories[]' => $produk->kategori->slug]) }}">{{ $produk->kategori->nama_kategori ?? 'Tidak Berkategori' }}</a></li>
                @if($produk->warna)
                <li class="mb-1"><strong>Warna: </strong> {{ $produk->warna }}</li>
                @endif
                <li class="mb-1"><strong>Stok:</strong> {{ $produk->stok > 0 ? $produk->stok . ' unit' : 'Habis' }}</li>
            </ul>
        </div>

        <!-- Deskripsi Lengkap -->
        <div class="row mt-5">
            <div class="col-12">

                <h3 class="fw-bolder mb-3">Detail Produk</h3>
                <div style="white-space: pre-wrap;">{{ $produk->deskripsi_lengkap ?? 'Tidak ada deskripsi lengkap.' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Related items section-->
<section class="py-5 bg-light">
    <div class="container px-4 px-lg-5 mt-5">
        <h2 class="fw-bolder mb-4 text-center">Anda Mungkin Juga Suka</h2>
        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
            @forelse($relatedProducts as $related)
            <div class="col mb-5">
                <div class="card h-100 shadow-sm border-0 product-card">
                    <!-- Product image-->
                    <a href="{{ route('produk.detail', ['id' => $related->id]) }}">
                        <img class="card-img-top p-3" src="{{ $related->gambar_produk_url }}" alt="{{ $related->nama_produk }}" style="height: 200px; object-fit: contain;" />
                    </a>
                    <!-- Product details-->
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-semibold fs-6 mb-1" style="min-height: 40px;">{{ Str::limit($related->nama_produk, 45) }}</h5>
                        <p class="fw-bold mb-0" style="color: #0d6efd;">Rp {{ number_format($related->harga, 0, ',', '.') }}</p>
                    </div>
                    <!-- Product actions-->
                    <div class="card-footer p-4 pt-0 border-top-0 bg-transparent text-center">
                        <a class="btn btn-outline-primary mt-auto" href="{{ route('produk.detail', ['id' => $related->id]) }}">Lihat Detail</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-center text-muted">Tidak ada produk terkait.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>
</div>
@push('styles')
<style>
    .product-fly-to-cart {
        position: fixed;
        z-index: 9999;
        border-radius: 50%;
        /* Membuat gambar terbang menjadi bulat */
        transition: all 0.8s cubic-bezier(0.550, 0.085, 0.680, 0.530);
        /* Efek ease-in */
    }
</style>
@endpush
<!-- Optional JavaScript for functionality -->
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Thumbnail click handling to update main image
        const mainImage = document.getElementById('mainProductImageDetail');
        const productThumbnails = document.querySelectorAll('.product-thumbnail');
        const quantityInput = document.getElementById('inputQuantity'); // Untuk jumlah
        const confirmOrderBtn = document.getElementById('confirmOrderBtn');

        if (confirmOrderBtn) {
            confirmOrderBtn.addEventListener('click', function(event) {
                event.preventDefault();
                const currentQuantity = quantityInput.value;
                window.location.href = `{{ route('transaksi.detail', ['produkId' => $produk->id]) }}?jumlah=${currentQuantity}`;
            });
        }
        productThumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                if (mainImage) {
                    mainImage.src = this.src; // Update main image source
                }

                // Update active thumbnail border
                productThumbnails.forEach(thumb => {
                    thumb.parentElement.classList.remove('border-primary', 'border-2');
                    thumb.parentElement.classList.add('border');
                });
                this.parentElement.classList.add('border-primary', 'border-2');
                this.parentElement.classList.remove('border');
            });
        });

        // Carousel navigation for thumbnails (jika ada lebih dari 1 thumbnail)
        let currentThumbIndex = 0;
        const prevThumbButton = document.getElementById('prevThumb');
        const nextThumbButton = document.getElementById('nextThumb');

        if (productThumbnails.length > 1) {
            if (nextThumbButton) {
                nextThumbButton.addEventListener('click', function() {
                    currentThumbIndex = (currentThumbIndex + 1) % productThumbnails.length;
                    productThumbnails[currentThumbIndex].click();
                });
            }

            if (prevThumbButton) {
                prevThumbButton.addEventListener('click', function() {
                    currentThumbIndex = (currentThumbIndex - 1 + productThumbnails.length) % productThumbnails.length;
                    productThumbnails[currentThumbIndex].click();
                });
            }
        } else {
            // Sembunyikan tombol navigasi thumbnail jika hanya ada 1 thumbnail
            if (prevThumbButton) prevThumbButton.style.display = 'none';
            if (nextThumbButton) nextThumbButton.style.display = 'none';
        }


        // Update hidden quantity input for Add to Cart form when quantity changes
        const mainQuantityInput = document.getElementById('inputQuantity');
        const addToCartQuantityInput = document.getElementById('add_to_cart_quantity');

        if (mainQuantityInput && addToCartQuantityInput) {
            mainQuantityInput.addEventListener('change', function() {
                addToCartQuantityInput.value = this.value;
            });
        }


        // Animasi Tambah ke Keranjang
        const addToCartButtons = document.querySelectorAll('.btn-add-to-cart');
        // const navbarCartIcon = document.getElementById('navbarCartIcon'); // ID lama
        const navbarCartTarget = document.getElementById('navbarCartLink'); // Gunakan ID dari tag <a>

        addToCartButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                // Cek apakah produk tersedia
                if (this.disabled) {
                    return; // Jangan lakukan animasi jika tombol disabled (stok habis)
                }

                const mainProductImage = document.getElementById('mainProductImageDetail');

                if (mainProductImage && navbarCartTarget) {
                    // Untuk kesederhanaan, kita biarkan form submit seperti biasa,
                    // animasi akan berjalan paralel.

                    const imgToFly = mainProductImage.cloneNode(true);
                    imgToFly.classList.add('product-fly-to-cart');

                    const productRect = mainProductImage.getBoundingClientRect();
                    const cartTargetRect = navbarCartTarget.getBoundingClientRect();

                    // console.log('Product Image Rect:', productRect);
                    // console.log('Navbar Cart Target Rect:', cartTargetRect);

                    document.body.appendChild(imgToFly);

                    imgToFly.style.left = productRect.left + 'px';
                    imgToFly.style.top = productRect.top + 'px';
                    imgToFly.style.width = mainProductImage.offsetWidth + 'px';
                    imgToFly.style.height = mainProductImage.offsetHeight + 'px';
                    imgToFly.style.opacity = '0.8';

                    // Trigger reflow untuk memastikan transisi diterapkan
                    imgToFly.offsetHeight;

                    // Target tengah dari elemen <a> keranjang, dikurangi setengah dari ukuran akhir gambar terbang (30px/2 = 15px)
                    const targetX = cartTargetRect.left + (cartTargetRect.width / 2) - 15;
                    const targetY = cartTargetRect.top + (cartTargetRect.height / 2) - 15;
                    // console.log('Target X:', targetX, 'Target Y:', targetY);

                    imgToFly.style.left = targetX + 'px';
                    imgToFly.style.top = targetY + 'px';
                    imgToFly.style.width = '30px'; // Ukuran gambar saat mencapai keranjang
                    imgToFly.style.height = '30px';
                    imgToFly.style.opacity = '0';

                    setTimeout(() => {
                        if (imgToFly.parentNode) { // Cek apakah elemen masih ada di DOM
                            imgToFly.remove();
                        }
                    }, 800); // Sesuaikan dengan durasi transisi CSS
                }
            });
        });
    });
</script>
@endpush
@endsection