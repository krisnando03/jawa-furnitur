@extends('frontend.layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0 text-center">Profil Saya</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Kolom Kiri: Foto Profil --}}
                        <div class="col-md-4 text-center d-flex flex-column justify-content-center"> {{-- Ubah col-md-3 menjadi col-md-4 --}}
                            <div class="mb-3">
                                @if($pelanggan->profile_photo_path)
                                <img src="{{ asset('storage/' . $pelanggan->profile_photo_path) }}" alt="Foto Profil" class="img-fluid rounded-circle mb-2 border border-primary border-3" style="width: 200px; height: 200px; object-fit: cover;">
                                @else
                                <i class="fas fa-user-circle fa-10x text-secondary mb-2"></i> {{-- Icon default --}}
                                @endif
                                <h4 class="text-muted">{{ $pelanggan->nama ?? 'Pengguna' }}</h4>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Form Profil --}}
                        <div class="col-md-8"> {{-- Ubah col-md-9 menjadi col-md-8 --}}
                            @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            @endif
                            @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                                {{ session('error') ?? '' }} {{-- Pastikan tidak error jika session error tidak ada --}}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            @endif


                            @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                                <h5 class="alert-heading">Oops! Ada kesalahan validasi:</h5>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            @endif

                            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Pengguna</label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $pelanggan->nama ?? '') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ $pelanggan->email ?? '' }}" readonly disabled>
                                    <small class="form-text text-muted">Email tidak dapat diubah melalui halaman ini.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="no_telepon" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $pelanggan->no_telepon ?? '') }}" placeholder="Masukkan nomor telepon">
                                </div>

                                <!-- <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat lengkap">{{ old('alamat', $pelanggan->alamat ?? '') }}</textarea>
                                </div> -->

                                <hr> {{-- Pemisah untuk bagian password --}}

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengganti password">
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
                                </div>

                                <div class="mb-3">
                                    <label for="profile_photo" class="form-label">Unggah Foto Profil</label>
                                    <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" accept="image/*">
                                    <small class="form-text text-muted">Ukuran maksimal 2MB.</small>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bagian Alamat Pengiriman --}}
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0 text-center">Alamat Pengiriman Saya</h4>
                </div>
                <div class="card-body">
                    @if ($errors->alamat->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading">Gagal Menambahkan Alamat:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->alamat->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @forelse($alamatList as $alamat)
                    <div class="card mb-3 {{ $alamat->is_utama ? 'border-primary' : '' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>{{ $alamat->label_alamat ?? 'Alamat' }} @if($alamat->is_utama) <span class="badge bg-primary">Utama</span> @endif</h5>
                                    <p class="mb-0"><strong>{{ $alamat->nama_penerima }}</strong> ({{ $alamat->nomor_telepon }})</p>
                                    <p class="mb-0">{{ $alamat->alamat_lengkap }}</p>
                                    <p class="mb-0">{{ $alamat->kota }}, {{ $alamat->provinsi }} {{ $alamat->kode_pos }}</p>
                                </div>
                                <div class="d-flex flex-column align-items-end">
                                    <div class="mb-2">
                                        <button type="button" class="btn btn-sm btn-outline-info me-1"
                                            data-bs-toggle="modal" data-bs-target="#editAlamatModal"
                                            data-id="{{ $alamat->id }}"
                                            data-nama_penerima="{{ $alamat->nama_penerima }}"
                                            data-nomor_telepon="{{ $alamat->nomor_telepon }}"
                                            data-label_alamat="{{ $alamat->label_alamat }}"
                                            data-alamat_lengkap="{{ $alamat->alamat_lengkap }}"
                                            data-provinsi="{{ $alamat->provinsi }}"
                                            data-kota="{{ $alamat->kota }}"
                                            data-kode_pos="{{ $alamat->kode_pos }}"
                                            data-is_utama="{{ $alamat->is_utama ? '1' : '0' }}">
                                            {{-- data-latitude="{{ $alamat->latitude }}" --}} {{-- Hapus, tidak diisi manual lagi --}}
                                            {{-- data-longitude="{{ $alamat->longitude }}"> --}} {{-- Hapus, tidak diisi manual lagi --}}
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('profile.alamat.destroy', $alamat->id) }}" method="POST" class="d-inline delete-alamat-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @if(!$alamat->is_utama)
                                    <form action="{{ route('profile.alamat.setUtama', $alamat->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check-circle"></i> Jadikan Utama
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                            @if($errors->hasBag('editAlamat_' . $alamat->id)) {{-- Menampilkan error spesifik untuk modal edit --}}
                            <div class="alert alert-danger mt-2">
                                <ul class="mb-0">
                                    @foreach($errors->getBag('editAlamat_' . $alamat->id)->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-muted">Anda belum memiliki alamat pengiriman.</p>
                    @endforelse

                    <div class="mt-4">
                        <button class="btn btn-success w-100" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTambahAlamat" aria-expanded="false" aria-controls="collapseTambahAlamat" id="btnToggleTambahAlamat">
                            <i class="fas fa-plus-circle me-2"></i>Tambah Alamat Pengiriman Baru
                        </button>
                    </div>

                    <div class="collapse mt-3" id="collapseTambahAlamat">
                        <h5 class="mb-3">Form Tambah Alamat Baru</h5>
                        <form action="{{ route('profile.alamat.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_penerima" class="form-label">Nama Penerima</label>
                                <input type="text" class="form-control @error('nama_penerima', 'alamat') is-invalid @enderror" id="nama_penerima" name="nama_penerima" value="{{ old('nama_penerima') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nomor_telepon_alamat" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control @error('nomor_telepon', 'alamat') is-invalid @enderror" id="nomor_telepon_alamat" name="nomor_telepon" value="{{ old('nomor_telepon') }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="label_alamat" class="form-label">Label Alamat (Contoh: Rumah, Kantor)</label>
                            <input type="text" class="form-control @error('label_alamat', 'alamat') is-invalid @enderror" id="label_alamat" name="label_alamat" value="{{ old('label_alamat') }}">
                        </div>
                        <div class="mb-3">
                            <label for="alamat_lengkap" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control @error('alamat_lengkap', 'alamat') is-invalid @enderror" id="alamat_lengkap" name="alamat_lengkap" rows="3" required>{{ old('alamat_lengkap') }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="provinsi" class="form-label">Provinsi</label>
                                <input type="text" class="form-control @error('provinsi', 'alamat') is-invalid @enderror" id="provinsi" name="provinsi" value="{{ old('provinsi') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kota" class="form-label">Kota/Kabupaten</label>
                                {{-- Dropdown untuk Kota/Kabupaten, awalnya disembunyikan --}}
                                <select class="form-select @error('kota', 'alamat') is-invalid @enderror" id="kota_select" style="display:none;">
                                    <option value="" disabled selected>Pilih Kota/Kabupaten</option>
                                </select>
                                {{-- Input teks untuk Kota/Kabupaten, defaultnya terlihat dan membawa atribut name --}}
                                <input type="text" class="form-control @error('kota', 'alamat') is-invalid @enderror" id="kota_text" name="kota" value="{{ old('kota') }}" required>
                            </div>
                        </div>
                        <div class="mb-3"> {{-- Kode Pos sekarang full width jika lat/long dihilangkan --}}
                            {{-- <div class="col-md-4 mb-3"> --}}
                            <label for="kode_pos" class="form-label">Kode Pos</label>
                            <input type="text" class="form-control @error('kode_pos', 'alamat') is-invalid @enderror" id="kode_pos" name="kode_pos" value="{{ old('kode_pos') }}">
                            {{-- </div> --}}
                            {{-- <div class="col-md-4 mb-3">
                                <label for="latitude" class="form-label">Latitude (Manual)</label>
                                <input type="text" class="form-control @error('latitude', 'alamat') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="-6.1234567">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="longitude" class="form-label">Longitude (Manual)</label>
                            <input type="text" class="form-control @error('longitude', 'alamat') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="110.1234567"> --}}
                            {{-- </div> --}}
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="is_utama" name="is_utama" {{ old('is_utama') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_utama">
                                Jadikan Alamat Utama
                            </label>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Simpan Alamat</button>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Alamat -->
<div class="modal fade" id="editAlamatModal" tabindex="-1" aria-labelledby="editAlamatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAlamatModalLabel">Edit Alamat Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAlamatForm" method="POST" action=""> {{-- Action akan diisi oleh JS --}}
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="alamat_id" id="edit_alamat_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_nama_penerima" class="form-label">Nama Penerima</label>
                            <input type="text" class="form-control" id="edit_nama_penerima" name="edit_nama_penerima" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_nomor_telepon" class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" id="edit_nomor_telepon" name="edit_nomor_telepon" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_label_alamat" class="form-label">Label Alamat (Contoh: Rumah, Kantor)</label>
                        <input type="text" class="form-control" id="edit_label_alamat" name="edit_label_alamat">
                    </div>
                    <div class="mb-3">
                        <label for="edit_alamat_lengkap" class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control" id="edit_alamat_lengkap" name="edit_alamat_lengkap" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_provinsi" class="form-label">Provinsi</label>
                            <input type="text" class="form-control" id="edit_provinsi" name="edit_provinsi" value="" required> {{-- Value akan diisi JS --}}
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_kota" class="form-label">Kota/Kabupaten</label>
                            <select class="form-select" id="edit_kota_select" style="display:none;">
                                <option value="" disabled selected>Pilih Kota/Kabupaten</option>
                            </select>
                            <input type="text" class="form-control" id="edit_kota_text" name="edit_kota" value="" required> {{-- Value akan diisi JS --}}
                        </div>
                    </div>
                    <div class="mb-3"> {{-- Kode Pos sekarang full width jika lat/long dihilangkan --}}
                        {{-- <div class="col-md-4 mb-3"> --}}
                        <label for="edit_kode_pos" class="form-label">Kode Pos</label>
                        <input type="text" class="form-control" id="edit_kode_pos" name="edit_kode_pos">
                        {{-- </div> --}}
                        {{-- <div class="col-md-4 mb-3">
                            <label for="edit_latitude" class="form-label">Latitude (Manual)</label>
                            <input type="text" class="form-control" id="edit_latitude" name="edit_latitude" placeholder="-6.1234567">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_longitude" class="form-label">Longitude (Manual)</label>
                            <input type="text" class="form-control" id="edit_longitude" name="edit_longitude" placeholder="110.1234567"> --}}
                        {{-- </div> --}}
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="1" id="edit_is_utama" name="edit_is_utama">
                        <label class="form-check-label" for="edit_is_utama">
                            Jadikan Alamat Utama
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const regionalData = {
            "Jawa Tengah": {
                "kota_kabupaten": [
                    "Kota Semarang", "Kota Surakarta", "Kota Salatiga", "Kota Magelang", "Kota Pekalongan", "Kota Tegal",
                    "Kabupaten Semarang", "Kabupaten Kendal", "Kabupaten Demak", "Kabupaten Grobogan", "Kabupaten Boyolali",
                    "Kabupaten Klaten", "Kabupaten Sukoharjo", "Kabupaten Wonogiri", "Kabupaten Karanganyar", "Kabupaten Sragen",
                    "Kabupaten Magelang", "Kabupaten Temanggung", "Kabupaten Wonosobo", "Kabupaten Purworejo", "Kabupaten Kebumen",
                    "Kabupaten Banjarnegara", "Kabupaten Purbalingga", "Kabupaten Banyumas", "Kabupaten Cilacap",
                    "Kabupaten Brebes", "Kabupaten Tegal", "Kabupaten Pemalang", "Kabupaten Pekalongan", "Kabupaten Batang",
                    "Kabupaten Jepara", "Kabupaten Kudus", "Kabupaten Pati", "Kabupaten Rembang", "Kabupaten Blora"
                ]
            }
            // Anda bisa menambahkan data provinsi lain di sini jika diperlukan
        };

        function setupKotaKabupatenField(provinsiValue, kotaSelectElement, kotaTextElement, defaultKotaValue = '') {
            const isJawaTengah = provinsiValue.toLowerCase().trim() === 'jawa tengah';
            const nameAttribute = kotaSelectElement.id.startsWith('edit_') ? 'edit_kota' : 'kota';

            if (isJawaTengah) {
                kotaSelectElement.innerHTML = '<option value="" selected disabled>Pilih Kota/Kabupaten</option>';
                regionalData["Jawa Tengah"].kota_kabupaten.forEach(kota => {
                    const option = document.createElement('option');
                    option.value = kota;
                    option.textContent = kota;
                    if (kota === defaultKotaValue) {
                        option.selected = true;
                    }
                    kotaSelectElement.appendChild(option);
                });
                kotaSelectElement.style.display = '';
                kotaSelectElement.setAttribute('name', nameAttribute);
                kotaSelectElement.required = true;

                kotaTextElement.style.display = 'none';
                kotaTextElement.removeAttribute('name');
                kotaTextElement.required = false;
                kotaTextElement.value = ''; // Kosongkan input teks
            } else {
                kotaSelectElement.style.display = 'none';
                kotaSelectElement.removeAttribute('name');
                kotaSelectElement.required = false;
                kotaSelectElement.innerHTML = '<option value=""></option>'; // Kosongkan pilihan

                kotaTextElement.style.display = '';
                kotaTextElement.setAttribute('name', nameAttribute);
                kotaTextElement.required = true;
                kotaTextElement.value = defaultKotaValue; // Isi input teks jika ada nilai default dari provinsi lain
            }
        }
        var editAlamatModal = document.getElementById('editAlamatModal');
        if (editAlamatModal) {
            editAlamatModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget; // Tombol yang memicu modal
                // Ekstrak info dari atribut data-*
                var id = button.getAttribute('data-id');
                var namaPenerima = button.getAttribute('data-nama_penerima');
                var nomorTelepon = button.getAttribute('data-nomor_telepon');
                var labelAlamat = button.getAttribute('data-label_alamat');
                var alamatLengkap = button.getAttribute('data-alamat_lengkap');
                var provinsi = button.getAttribute('data-provinsi');
                var kota = button.getAttribute('data-kota');
                var kodePos = button.getAttribute('data-kode_pos');
                // var latitude = button.getAttribute('data-latitude'); // Hapus, tidak diisi manual lagi
                // var longitude = button.getAttribute('data-longitude'); // Hapus, tidak diisi manual lagi

                var isUtama = button.getAttribute('data-is_utama') === '1';

                // Update action form modal
                var form = editAlamatModal.querySelector('#editAlamatForm');
                form.action = "{{ url('profile/alamat') }}/" + id;

                // Isi field-field form
                editAlamatModal.querySelector('#edit_alamat_id').value = id;
                editAlamatModal.querySelector('#edit_nama_penerima').value = namaPenerima;
                editAlamatModal.querySelector('#edit_nomor_telepon').value = nomorTelepon;
                editAlamatModal.querySelector('#edit_label_alamat').value = labelAlamat;
                editAlamatModal.querySelector('#edit_alamat_lengkap').value = alamatLengkap;
                editAlamatModal.querySelector('#edit_provinsi').value = provinsi;
                // editAlamatModal.querySelector('#edit_kota').value = kota; // Dihapus, akan dihandle oleh setupKotaKabupatenField
                editAlamatModal.querySelector('#edit_kode_pos').value = kodePos;
                // editAlamatModal.querySelector('#edit_latitude').value = latitude; // Hapus, tidak diisi manual lagi
                // editAlamatModal.querySelector('#edit_longitude').value = longitude; // Hapus, tidak diisi manual lagi

                editAlamatModal.querySelector('#edit_is_utama').checked = isUtama;

                // Setup Kota/Kabupaten field for edit modal
                const editProvinsiInput = editAlamatModal.querySelector('#edit_provinsi');
                const editKotaSelect = editAlamatModal.querySelector('#edit_kota_select');
                const editKotaText = editAlamatModal.querySelector('#edit_kota_text');
                setupKotaKabupatenField(provinsi, editKotaSelect, editKotaText, kota);
            });
        }

        // Konfirmasi sebelum hapus
        const deleteForms = document.querySelectorAll('.delete-alamat-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!confirm('Apakah Anda yakin ingin menghapus alamat ini?')) {
                    event.preventDefault();
                }
            });
        });

        // Jika ada error validasi pada modal edit, buka kembali modalnya
        @if(session('open_modal_edit_id') && $errors-> hasBag('editAlamat_'.session('open_modal_edit_id')))
        var modalToOpen = new bootstrap.Modal(document.getElementById('editAlamatModal'));
        modalToOpen.show();
        @endif

        // --- Logika untuk Form Tambah Alamat Baru ---
        const provinsiInputAdd = document.getElementById('provinsi');
        const kotaSelectAdd = document.getElementById('kota_select');
        const kotaTextAdd = document.getElementById('kota_text');

        // Inisialisasi saat halaman dimuat (untuk menangani old input)
        if (provinsiInputAdd) {
            const initialProvinsi = provinsiInputAdd.value || "{{ old('provinsi') }}";
            const initialKota = kotaTextAdd.value || "{{ old('kota') }}"; // Ambil dari text input karena itu yang punya name by default
            setupKotaKabupatenField(initialProvinsi, kotaSelectAdd, kotaTextAdd, initialKota);

            provinsiInputAdd.addEventListener('input', function() {
                setupKotaKabupatenField(this.value, kotaSelectAdd, kotaTextAdd, ''); // Saat provinsi diubah, reset kota
            });
        }

        // --- Logika untuk Form Edit Alamat (saat input provinsi di modal diubah) ---
        const provinsiInputEdit = document.getElementById('edit_provinsi');
        const kotaSelectEdit = document.getElementById('edit_kota_select');
        const kotaTextEdit = document.getElementById('edit_kota_text');

        if (provinsiInputEdit) {
            provinsiInputEdit.addEventListener('input', function() {
                setupKotaKabupatenField(this.value, kotaSelectEdit, kotaTextEdit, ''); // Saat provinsi diubah, reset kota
            });
        }

        // Mengubah teks tombol tambah alamat saat form collapse/expand
        const collapseTambahAlamat = document.getElementById('collapseTambahAlamat');
        const btnToggleTambahAlamat = document.getElementById('btnToggleTambahAlamat');

        if (collapseTambahAlamat && btnToggleTambahAlamat) {
            collapseTambahAlamat.addEventListener('show.bs.collapse', function () {
                btnToggleTambahAlamat.innerHTML = '<i class="fas fa-minus-circle me-2"></i>Sembunyikan Form Tambah Alamat';
            });

            collapseTambahAlamat.addEventListener('hide.bs.collapse', function () {
                btnToggleTambahAlamat.innerHTML = '<i class="fas fa-plus-circle me-2"></i>Tambah Alamat Pengiriman Baru';
            });
        }
    });
</script>
@endpush