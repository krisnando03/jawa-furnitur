<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jawa Furnitur - @yield('title')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome icons (free version)-->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
    @stack('styles') {{-- Untuk CSS spesifik halaman --}}

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }

        header {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            color: #0d6efd;
        }

        .nav-link {
            font-weight: 500;
        }

        .nav-link:hover {
            color: #0d6efd !important;
        }

        #pesananTab .nav-link.active {
            color: #ffffff !important;
            /* Warna teks putih agar kontras dengan background */
            background-color: #007bff !important;
            /* Warna biru solid (Bootstrap primary) */
            border-color: #007bff #007bff #ffffff;
            /* Sesuaikan border jika perlu */
        }

        #pesananTab .nav-link {
            color: #495057;
            /* Warna teks abu-abu untuk link tidak aktif */
        }

        #pesananTab .nav-link:hover {
            color: #0056b3;
            /* Warna teks saat hover */
            border-color: #dee2e6 #dee2e6 #0056b3;
            /* Sesuaikan border hover jika perlu */
        }

        footer {
            background-color: #343a40;
            color: white;
            padding: 1rem 0;
            margin-top: auto;
        }

        main {
            min-height: 70vh;
            padding: 2rem 1rem;
        }

        input::placeholder {
            color: #999;
            font-style: italic;
        }

        input.form-control:focus {
            box-shadow: none;
            border-color: #ccc;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Header/Navbar -->
    <header class="bg-light border-bottom py-3"> {{-- Mengubah py-2 menjadi py-3 untuk menambah padding vertikal --}}
        <div class="container d-flex align-items-center justify-content-between">
            <!-- Logo -->
            <div class="d-flex align-items-center">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" style="height: 50px; margin-right: 5px;">
                <span class="fw-bold text-danger fs-4">JAWAFURNITUR</span>
            </div>

            <!-- Search Bar -->
            <form action="{{ route('home') }}" method="GET" class="flex-grow-1 mx-4" style="max-width: 600px;"> {{-- Tingkatkan max-width --}}
                <div class="input-group rounded-pill border border-1">
                    <input type="text" name="search" class="form-control border-0 rounded-start-pill py-3" placeholder="Search product..." value="{{ request('search') }}"> {{-- Tambahkan py-2 untuk padding vertikal --}}
                    <button class="btn bg-light rounded-end-pill" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <!-- Navigation -->
            <div class="d-flex align-items-center">
                <a href="{{ url('/') }}" class="text-dark mx-2" title="Beranda"><i class="fas fa-home fa-xl"></i></a>

                @if(session('pelanggan'))
                <div class="vr mx-2"></div>

                <!-- Icons hanya muncul jika pelanggan login, tambahkan fa-lg untuk memperbesar -->
                <a href="{{ route('pesan.index') }}" class="text-dark mx-3 position-relative" title="Pesan Saya">
                    <i class="fas fa-comment-dots fa-xl"></i>
                    @if(isset($unreadMessagesCount) && $unreadMessagesCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $unreadMessagesCount }}
                        <span class="visually-hidden">unread messages</span>
                    </span>
                    @endif
                </a>

                <a id="navbarCartLink" href="{{ route('keranjang.index') }}" class="text-dark mx-3 position-relative" title="Keranjang Belanja">
                    <i id="navbarCartIcon" class="fas fa-shopping-bag fa-xl"></i>
                    {{-- Variabel dari View Composer --}}
                    @if(isset($jumlahItemKeranjang) && $jumlahItemKeranjang > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $jumlahItemKeranjang }}
                    </span>
                    @endif
                </a>
                <a class="nav-link position-relative mx-3" title="Pesanan Saya" href="{{ route('pesanan.saya.index') }}">
                    <i class="fas fa-receipt fa-xl"></i>
                    {{-- Variabel dari View Composer --}}
                    @if(isset($unfinishedOrdersCount) && $unfinishedOrdersCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $unfinishedOrdersCount }}
                        <span class="visually-hidden">unfinished orders</span>
                    </span>
                    @endif
                </a>
                <a href="{{ route('notifikasi.index') }}" class="text-danger mx-3 position-relative" title="Notifikasi Saya">
                    <i class="fas fa-bell fa-xl"></i>
                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                        {{ $unreadNotificationsCount }}
                        <span class="visually-hidden">unread notifications</span>
                    </span>
                    @endif
                </a>

                <div class="vr mx-2"></div>

                <!-- Profil -->
                <a href="{{ route('profile.show') }}" class="text-dark mx-2 profile-icon-link" title="Profil Saya">
                    @php
                    $pelangganSession = Session::get('pelanggan');
                    // Pastikan $pelangganSession adalah array atau objek yang memiliki properti 'profile_photo_path'
                    $profilePhotoPath = null;
                    if (is_array($pelangganSession) && isset($pelangganSession['profile_photo_path'])) {
                    $profilePhotoPath = $pelangganSession['profile_photo_path'];
                    } elseif (is_object($pelangganSession) && isset($pelangganSession->profile_photo_path)) {
                    $profilePhotoPath = $pelangganSession->profile_photo_path;
                    }
                    @endphp
                    @if($profilePhotoPath)
                    <img src="{{ asset('storage/' . $profilePhotoPath) }}" alt="Foto Profil" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
                    @else {{-- Jika tidak ada foto profil, tampilkan ikon user yang lebih besar --}}
                    <i class="fas fa-user-circle fa-xl"></i>
                    @endif
                </a>
                <!-- Tombol Logout -->
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link text-dark mx-2 p-0" style="text-decoration: none;" title="Logout">
                        <i class="fas fa-sign-out-alt fa-xl"></i>
                    </button>
                </form>
                @else
                <div class="vr mx-2"></div>
                <!-- Tampilkan icon login jika belum login -->
                <a href="{{ route('login') }}" class="text-dark mx-2">
                    <i class="fas fa-user fa-xl border rounded-circle p-2"></i>
                </a>
                @endif
            </div>
        </div>
    </header>



    <!-- Main Content -->
    <main class="flex-grow-1 container">
        @yield('content')
    </main>

    <footer class="bg-white py-5 border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4 mb-md-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" style="height: 50px; margin-right: 5px;">
                        <span class="text-danger fw-bold fs-5">JawaFurnitur</span>
                    </div>
                    <p class="text-dark fw-semibold">Berbagai Kebutuhan Interior<br>Rumah Anda</p>
                </div>

                <div class="col-md-3 mb-4 mb-md-0">
                    <h5 class="text-uppercase text-dark mb-4">SERVICE</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-dark">Products</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-dark">Contact Us</a></li>
                    </ul>
                </div>

                <div class="col-md-3 mb-4 mb-md-0">
                    <h5 class="text-uppercase text-dark mb-4">SUPPORT</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-dark">About Supply3dArsitek.Com</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-dark">Privacy Policy & Terms</a></li>
                    </ul>
                </div>

                <div class="col-md-3">
                    <h5 class="text-uppercase text-dark mb-4">FOLLOW US ON</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-dark">Instagram</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-dark">Facebook</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>