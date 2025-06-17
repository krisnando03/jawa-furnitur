@extends('frontend.layouts.app')
@section('title', 'Register')
@section('description', 'Buat akun baru untuk mendapatkan akses.') {{-- Ganti deskripsi jika perlu --}}
@section('content')
<div class="container-fluid min-vh-100 d-flex justify-content-center align-items-center bg-light">
    <div class="row w-100 shadow" style="max-width: 1100px; background: #fff; border-radius: 12px; overflow: hidden;">
        <!-- Kiri: Gambar dan Slogan -->
        <div class="col-md-6 d-none d-md-block p-0 position-relative">
            <img src="{{ asset('assets/img/bglgn.png') }}" alt="Interior" class="img-fluid h-100" style="object-fit: cover;">
        </div>

        <!-- Kanan: Form Register -->
        <div class="col-md-6 d-flex flex-column justify-content-center" style="padding: 4rem;">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/img/icon.png') }}" alt="Register" height="80">
                <h3 class="mt-3">Register</h3>
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

            <form method="POST" action="{{ route('register') }}" autocomplete="on">
                @csrf
                <div style="max-width: 320px; margin-left: auto; margin-right: auto;">
                    <div class="mb-3">
                        <input type="email" name="email" id="email" class="form-control rounded-pill @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email') }}" required style="padding-top: 1rem; padding-bottom: 1rem;">
                        <div class="valid-feedback" style="font-size: 0.8em;"></div>
                        <div class="invalid-feedback" style="font-size: 0.8em;">@error('email') {{ $message }} @enderror</div>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="username" id="username" class="form-control rounded-pill @error('username') is-invalid @enderror" placeholder="Username" value="{{ old('username') }}" required style="padding-top: 1rem; padding-bottom: 1rem;">
                        <div class="valid-feedback" style="font-size: 0.8em;"></div>
                        <div class="invalid-feedback" style="font-size: 0.8em;">@error('username') {{ $message }} @enderror</div>
                    </div>
                    <div class="mb-3">
                        <div class="position-relative">
                            <input type="password" name="password" id="password" class="form-control rounded-pill @error('password') is-invalid @enderror" placeholder="Password" required style="padding-top: 1rem; padding-bottom: 1rem;">
                            <span class="toggle-password-visibility position-absolute end-0 top-50 translate-middle-y me-3 text-muted" style="cursor: pointer;" data-input-id="password">
                            </span>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.75em; margin-top: 0.25rem;">
                            Min 8 karakter, huruf besar & kecil, angka/simbol.
                        </div>
                        <div class="valid-feedback" style="font-size: 0.8em;"></div>
                        <div class="invalid-feedback" style="font-size: 0.8em;">@error('password') {{ $message }} @enderror</div>
                    </div>
                    <div class="mb-3">
                        <div class="position-relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control rounded-pill" placeholder="Confirm Password" required style="padding-top: 1rem; padding-bottom: 1rem;">
                            <span class="toggle-password-visibility position-absolute end-0 top-50 translate-middle-y me-3 text-muted" style="cursor: pointer;" data-input-id="password_confirmation">
                            </span>
                        </div>
                        <div class="valid-feedback" style="font-size: 0.8em;"></div>
                        <div class="invalid-feedback" style="font-size: 0.8em;"></div>
                    </div>
                    <button type="submit" id="registerButton" class="btn btn-danger w-100 rounded-pill">REGISTER</button>
                </div>

                <div class="text-center my-3">
                    <span class="text-muted">Or login with</span>
                    <div class="d-flex justify-content-center gap-4 mt-2">
                        <a href="#"><img src="{{ asset('assets/img/logos/google.png') }}" alt="Google" width="30"></a>
                        <a href="#"><img src="{{ asset('assets/img/logos/facebook.png') }}" alt="Facebook" width="30"></a>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <small>Already have an account? <a href="{{ route('login') }}">Login</a></small>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    const checkAvailabilityUrl = '{{ route("check.availability") }}';
    // Baris console.log('Skrip registrasi dimuat.'); bisa diletakkan di sini atau di dalam verbatim jika tidak ada masalah.
    // Untuk amannya, kita letakkan di luar verbatim jika hanya untuk debugging awal.
    // console.log('Skrip registrasi dimuat. URL:', checkAvailabilityUrl);

    @verbatim
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('password_confirmation');
        const registerButton = document.getElementById('registerButton');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function setValidationState(inputElement, isValid, validMessage, invalidMessage) {
            const container = inputElement.closest('.mb-3');
            const validFeedback = container.querySelector('.valid-feedback');
            const invalidFeedback = container.querySelector('.invalid-feedback');

            inputElement.classList.remove('is-valid', 'is-invalid');
            if (validFeedback) validFeedback.textContent = '';
            if (invalidFeedback) invalidFeedback.textContent = ''; // Clear previous JS messages

            if (isValid === true) {
                inputElement.classList.add('is-valid');
                if (validFeedback) validFeedback.textContent = validMessage || '';
            } else if (isValid === false) {
                inputElement.classList.add('is-invalid');
                if (invalidFeedback) invalidFeedback.textContent = invalidMessage || 'Input tidak valid.';
            }
            // If isValid is null, all classes removed, feedback text cleared.
            // Server-side errors shown by @error will be cleared once JS validation runs.
            updateRegisterButtonState();
        }

        function updateRegisterButtonState() {
            const emailValid = emailInput.classList.contains('is-valid');
            const usernameValid = usernameInput.classList.contains('is-valid');
            const passwordValid = passwordInput.classList.contains('is-valid');
            const passwordConfirmationValid = passwordConfirmationInput.classList.contains('is-valid');

            if (emailValid && usernameValid && passwordValid && passwordConfirmationValid) {
                registerButton.disabled = false;
            } else {
                registerButton.disabled = true;
            }
        }

        async function checkAvailability(inputElement, fieldType) {
            const value = inputElement.value.trim();
            if (value.length === 0) {
                setValidationState(inputElement, null); // Clear validation
                return;
            }

            if (fieldType === 'email') {
                // Validasi email harus diakhiri @gmail.com
                if (!/^\S+@gmail\.com$/.test(value)) { // Tidak perlu @@ di dalam @verbatim
                    setValidationState(inputElement, false, '', 'Email harus diakhiri @gmail.com');
                    return;
                }
            } else if (fieldType === 'username' && value.length < 3) {
                setValidationState(inputElement, false, '', 'Username minimal 3 karakter.');
                return;
            }

            try {
                const response = await fetch(checkAvailabilityUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        field: fieldType,
                        value: value
                    })
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                if (data.available) {
                    let validMsg = fieldType === 'email' ? 'Email tersedia' : 'Username tersedia';
                    setValidationState(inputElement, true, validMsg, '');
                } else {
                    let invalidMsg = fieldType === 'email' ? 'Email sudah digunakan' : 'Username sudah digunakan';
                    setValidationState(inputElement, false, '', invalidMsg);
                }
            } catch (error) {
                console.error('Error checking availability:', error);
                setValidationState(inputElement, false, '', 'Terjadi kesalahan saat validasi server.');
            }
        }

        if (emailInput) {
            emailInput.addEventListener('blur', () => checkAvailability(emailInput, 'email'));
        }
        if (usernameInput) {
            usernameInput.addEventListener('blur', () => checkAvailability(usernameInput, 'username'));
        }

        function validatePassword() {
            const password = passwordInput.value;
            const container = passwordInput.closest('.mb-3');
            const invalidFeedbackEl = container.querySelector('.invalid-feedback');
            const validFeedbackEl = container.querySelector('.valid-feedback');

            // Clear previous states and messages
            passwordInput.classList.remove('is-valid', 'is-invalid');
            invalidFeedbackEl.innerHTML = '';
            validFeedbackEl.textContent = '';

            if (password.length === 0) {
                // Jika password kosong, hapus status validasi kecuali konfirmasi diisi
                if (passwordConfirmationInput.value.length === 0) {
                    passwordInput.classList.remove('is-valid', 'is-invalid');
                } else {
                    passwordInput.classList.add('is-invalid');
                    invalidFeedbackEl.textContent = 'Password tidak boleh kosong jika konfirmasi diisi.';
                }
                validatePasswordConfirmation();
                updateRegisterButtonState();
                return;
            }
            let criteriaMetOverall = true;
            const feedbackMessagesList = [];

            // Kriteria 1: Panjang
            if (password.length >= 8) {
                feedbackMessagesList.push('<small class="text-success d-block">✓ Minimal 8 karakter</small>');
            } else {
                feedbackMessagesList.push('<small class="text-danger d-block">✗ Minimal 8 karakter</small>');
                criteriaMetOverall = false;
            }

            // Kriteria 2: Huruf Kecil
            if (/[a-z]/.test(password)) {
                feedbackMessagesList.push('<small class="text-success d-block">✓ Minimal 1 huruf kecil</small>');
            } else {
                feedbackMessagesList.push('<small class="text-danger d-block">✗ Minimal 1 huruf kecil</small>');
                criteriaMetOverall = false;
            }

            // Kriteria 3: Huruf Besar
            if (/[A-Z]/.test(password)) {
                feedbackMessagesList.push('<small class="text-success d-block">✓ Minimal 1 huruf besar</small>');
            } else {
                feedbackMessagesList.push('<small class="text-danger d-block">✗ Minimal 1 huruf besar</small>');
                criteriaMetOverall = false;
            }

            // Kriteria 4: Angka atau Simbol
            if (/[\d\W]/.test(password)) { // \d untuk digit, \W untuk karakter non-kata (simbol)
                feedbackMessagesList.push('<small class="text-success d-block">✓ Minimal 1 angka atau simbol</small>');
            } else {
                feedbackMessagesList.push('<small class="text-danger d-block">✗ Minimal 1 angka atau simbol</small>');
                criteriaMetOverall = false;
            }

            invalidFeedbackEl.innerHTML = feedbackMessagesList.join('');

            if (criteriaMetOverall) {
                passwordInput.classList.add('is-valid');
                // validFeedbackEl.textContent = 'Password kuat!'; // Opsional
            } else {
                passwordInput.classList.add('is-invalid');
            }

            updateRegisterButtonState();
            validatePasswordConfirmation(); // Re-validate confirmation when password changes
        }

        if (passwordInput) {
            passwordInput.addEventListener('input', validatePassword);
        }

        function validatePasswordConfirmation() {
            if (passwordConfirmationInput.value.length === 0 && passwordInput.value.length === 0) {
                setValidationState(passwordConfirmationInput, null); // Hapus status validasi
                return;
            }
            if (passwordInput.value.length > 0 && passwordConfirmationInput.value.length === 0) {
                setValidationState(passwordConfirmationInput, false, '', 'Konfirmasi password diperlukan.');
                return;
            }
            if (passwordInput.value === passwordConfirmationInput.value && passwordConfirmationInput.value.length > 0) {
                setValidationState(passwordConfirmationInput, true, 'Password cocok!', '');
            } else if (passwordConfirmationInput.value.length > 0) {
                setValidationState(passwordConfirmationInput, false, '', 'Password tidak cocok.');
            } else { // Konfirmasi kosong, tapi password mungkin tidak. Hapus status jika konfirmasi kosong.
                setValidationState(passwordConfirmationInput, null);
            }
        }

        if (passwordConfirmationInput) {
            passwordConfirmationInput.addEventListener('input', validatePasswordConfirmation);
        }

        document.querySelectorAll('.toggle-password-visibility').forEach(span => {
            span.addEventListener('click', function() {
                const inputId = this.dataset.inputId;
                const input = document.getElementById(inputId);
                const icon = this.querySelector('i');
                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = "password";
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Initial validation for fields that might have old() values
        if (emailInput && emailInput.value) checkAvailability(emailInput, 'email');
        if (usernameInput && usernameInput.value) checkAvailability(usernameInput, 'username');
        if (passwordInput && passwordInput.value) validatePassword();
        else if (passwordConfirmationInput && passwordConfirmationInput.value) validatePasswordConfirmation(); // if only confirm has old value

        updateRegisterButtonState(); // Panggil sekali di awal untuk set status tombol

    });
    @endverbatim
</script>
@endpush