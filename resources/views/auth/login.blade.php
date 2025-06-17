@extends('frontend.layouts.app')
@section('title', 'Login')
@section('description', 'Login to your account')
@section('content')
<div class="container-fluid min-vh-100 d-flex justify-content-center align-items-center bg-light">
    <div class="row w-100 shadow" style="max-width: 1100px; background: #fff; border-radius: 12px; overflow: hidden;">
        <!-- Kiri: Gambar dan Slogan -->
        <div class="col-md-6 d-none d-md-block p-0">
            <img src="{{ asset('assets/img/bglgn.png') }}" alt="Interior" class="img-fluid h-100" style="object-fit: cover;">
        </div>

        <!-- Kanan: Form Login -->
        <div class="col-md-6 d-flex flex-column justify-content-center" style="padding: 4rem;">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/img/icon.png') }}" alt="Login" height="80">
                <h3 class="mt-3">Login</h3>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if (session('success'))
            <div class="alert alert-success mb-3">
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" autocomplete="on">
                @csrf
                <div style="max-width: 320px; margin-left: auto; margin-right: auto;">
                    <div class="mb-3">
                        <input type="text" name="email" id="email" class="form-control rounded-pill @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email') }}" required style="padding-top: 1rem; padding-bottom: 1rem;">
                        <div class="valid-feedback" style="font-size: 0.8em;"></div>
                        <div class="invalid-feedback" style="font-size: 0.8em;">@error('email') {{ $message }} @enderror</div>
                    </div>
                    <div class="mb-3 position-relative">
                        <input type="password" name="password" id="password" class="form-control rounded-pill @error('password') is-invalid @enderror" placeholder="Password" required style="padding-top: 1rem; padding-bottom: 1rem;">
                        <span class="position-absolute end-0 top-50 translate-middle-y me-3 text-muted">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 rounded-pill">LOGIN</button>
                    <div class="text-end mt-2">
                        <a href="#" class="text-decoration-none small">Forgot password?</a>
                    </div>
                </div>

                <div class="text-center my-3">
                    <span class="text-muted">Or login with</span>
                    <div class="d-flex justify-content-center gap-4 mt-2">
                        <a href="#"><img src="{{ asset('assets/img/logos/google.png') }}" alt="Google" width="30"></a>
                        <a href="#"><img src="{{ asset('assets/img/logos/facebook.png') }}" alt="Facebook" width="30"></a>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <small>Don't have an account? <a href="{{ route('register') }}">Sign Up</a></small>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const checkLoginEmailUrl = '{{ route("check.login.email") }}'; // Kita akan buat route ini

    @verbatim
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function setLoginEmailValidationState(isValid, validMessage, invalidMessage) {
            const container = emailInput.closest('.mb-3');
            const validFeedback = container.querySelector('.valid-feedback');
            const invalidFeedback = container.querySelector('.invalid-feedback');

            emailInput.classList.remove('is-valid', 'is-invalid');
            if (validFeedback) validFeedback.textContent = '';
            if (invalidFeedback) invalidFeedback.textContent = '';

            if (isValid === true) {
                emailInput.classList.add('is-valid');
                if (validFeedback) validFeedback.textContent = validMessage || '';
            } else if (isValid === false) {
                emailInput.classList.add('is-invalid');
                if (invalidFeedback) invalidFeedback.textContent = invalidMessage || 'Input tidak valid.';
            }
        }

        async function checkEmailForLogin() {
            const value = emailInput.value.trim();
            if (value.length === 0) {
                setLoginEmailValidationState(null); // Clear validation
                return;
            }

            // Validasi format email sederhana di client-side
            if (!/^\S+@\S+\.\S+$/.test(value)) {
                setLoginEmailValidationState(false, '', 'Format email tidak valid.');
                return;
            }

            try {
                const response = await fetch(checkLoginEmailUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        email: value
                    })
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();
                setLoginEmailValidationState(data.exists, 'Email terdaftar.', 'Email tidak terdaftar.');

            } catch (error) {
                console.error('Error checking email for login:', error);
                setLoginEmailValidationState(false, '', 'Gagal memvalidasi email.');
            }
        }

        if (emailInput) {
            emailInput.addEventListener('blur', checkEmailForLogin);
            if (emailInput.value) { // Jika ada old value, validasi saat load
                checkEmailForLogin();
            }
        }
    });
    @endverbatim
</script>
@endpush