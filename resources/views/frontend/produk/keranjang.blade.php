@extends('frontend.layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h2 class="fw-bold mb-4">Keranjang Belanja Anda</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            {{-- Nanti di sini akan ada daftar item di keranjang --}}
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    @if($itemsKeranjang->isEmpty())
                    <p class="text-center text-muted fs-5 py-5">
                        <i class="fas fa-shopping-cart fa-3x mb-3"></i><br>
                        Keranjang belanja Anda masih kosong.
                    </p>
                    <div class="text-center">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">Mulai Belanja</a>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 5%;" class="text-center">
                                        <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
                                    </th>
                                    <th scope="col" style="width: 50%;">Produk</th>
                                    <th scope="col" class="text-center">Jumlah</th>
                                    <th scope="col" class="text-end">Harga Satuan</th>
                                    <th scope="col" class="text-end">Subtotal</th>
                                    {{-- Kolom Aksi per item dihapus --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotal = 0; @endphp
                                @foreach($itemsKeranjang as $item)
                                @php $grandTotal += $item->subtotal_harga; @endphp
                                <tr>
                                    <td class="text-center">
                                        <input class="form-check-input item-checkbox" type="checkbox"
                                            value="{{ $item->id }}"
                                            data-subtotal="{{ $item->subtotal_harga }}"
                                            aria-label="Pilih item {{ $item->produk->nama_produk ?? '' }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{ $item->produk ? route('produk.detail', ['id' => $item->produk->id]) : '#' }}">
                                                <img src="{{ $item->produk->gambar_produk_url ?? asset('assets/img/placeholder_produk.jpg') }}"
                                                    alt="{{ $item->produk->nama_produk ?? 'Produk tidak tersedia' }}"
                                                    class="img-fluid rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                            </a>
                                            <div>
                                                <h6 class="mb-0">{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}</h6>
                                                @if($item->produk && $item->produk->kategori)
                                                <small class="text-muted">Kategori: {{ $item->produk->kategori->nama_kategori }}</small><br>
                                                @endif
                                                <small class="text-muted">Berat: {{ number_format($item->berat_satuan_saat_dibeli, 2) }} kg/item</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('keranjang.update', $item->id) }}" method="POST" class="d-inline-flex align-items-center">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-quantity-minus" data-itemid="{{ $item->id }}">-</button>
                                            <input type="number" name="jumlah" value="{{ $item->jumlah }}"
                                                class="form-control form-control-sm text-center mx-1"
                                                style="width: 60px;"
                                                min="1"
                                                id="jumlah-{{ $item->id }}"
                                                onchange="this.form.submit()"> {{-- Submit form saat nilai berubah --}}
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-quantity-plus" data-itemid="{{ $item->id }}">+</button>
                                            {{-- Tombol submit tersembunyi untuk kasus jika JS tidak aktif atau untuk trigger programatik --}}
                                            <button type="submit" class="d-none">Update</button>
                                        </form>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($item->harga_saat_dibeli, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($item->subtotal_harga, 0, ',', '.') }}</td>
                                    {{-- Kolom Aksi per item dihapus --}}
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-start mt-3">
                        <form action="{{ route('keranjang.hapus.terpilih') }}" method="POST" id="deleteSelectedForm" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item terpilih?');">
                            @csrf
                            {{-- Input hidden akan diisi oleh JavaScript --}}
                            <div id="selectedIdsContainer"></div>
                            <button type="submit" class="btn btn-outline-danger" id="btnDeleteSelected" disabled>
                                <i class="fas fa-trash-alt me-2"></i>Hapus Terpilih
                            </button>
                        </form>
                    </div>

                    <hr>

                    <div class="row mt-4">
                        <!-- Kolom Kiri untuk Input Diskon -->
                        <div class="col-md-6 col-lg-7 mb-4 mb-md-0">
                            @if(!$itemsKeranjang->isEmpty())
                            <h5 class="fw-bold mb-3">Gunakan Kode Diskon</h5>
                            <form action="{{ route('keranjang.apply_discount') }}" method="POST" id="discountForm">
                                @csrf
                                <label for="discount_code" class="form-label">Pilih diskon yang tersedia:</label>
                                <div class="input-group">
                                    <select class="form-select" name="discount_code" id="discount_code_select">
                                        <option value="">-- Tidak Menggunakan Diskon --</option>
                                        @foreach($availableVouchers as $voucher)
                                        <option value="{{ $voucher->kode }}" {{ (isset($discountInfo) && $discountInfo['code'] == $voucher->kode) ? 'selected' : '' }}>
                                            {{ $voucher->deskripsi }}
                                            @if($voucher->min_pembelian > 0)
                                            (min. Rp {{ number_format($voucher->min_pembelian, 0, ',', '.') }})
                                            @endif
                                        </option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-secondary" type="submit">Terapkan</button>
                                </div>
                                @if(isset($discountInfo) && isset($discountInfo['description']))
                                <p class="mt-2 text-success small"><i class="fas fa-check-circle me-1"></i> Diskon "{{ $discountInfo['description'] }}" diterapkan.</p>
                                @endif
                            </form>
                            @endif
                        </div>

                        <!-- Kolom Kanan untuk Ringkasan Belanja -->
                        <div class="col-md-6 col-lg-5">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="fw-bold mb-3">Ringkasan Belanja</h5>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal (Terpilih):</span>
                                        <span class="fw-semibold" id="dynamicSubtotal">Rp 0</span>
                                    </div>

                                    <div id="discountAppliedSection" style="display: none;">
                                        <div class="d-flex justify-content-between mb-1 text-success">
                                            <span id="discountDescription">Diskon:</span>
                                            <span class="fw-semibold" id="dynamicDiscountAmount">- Rp 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2 text-primary small">
                                            <span>Anda Hemat:</span>
                                            <span class="fw-semibold" id="dynamicSavingsAmount">Rp 0</span>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between mb-3 fw-bold fs-5">
                                        <span>Total Akhir:</span>
                                        <span id="dynamicGrandTotal">Rp 0</span>
                                    </div>
                                    <div class="d-grid">
                                        {{-- Tombol ini akan memicu submit form checkout --}}
                                        <button type="button" class="btn btn-success btn-lg" id="btnLanjutPembayaran" disabled>
                                            Lanjut ke Pembayaran
                                        </button>
                                        {{-- Form tersembunyi untuk proses checkout --}}
                                        <form id="checkoutForm" action="{{ route('checkout.confirm.cart') }}" method="POST" class="d-none">
                                            @csrf
                                            {{-- Input hidden untuk item terpilih akan diisi oleh JavaScript --}}
                                            <div id="selectedItemsForCheckout"></div>
                                            {{-- Input hidden untuk kode diskon akan diisi oleh JavaScript --}}
                                            <input type="hidden" name="applied_discount_code" id="appliedDiscountCode">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        {{-- Nanti di sini bisa ada ringkasan pesanan dan tombol checkout --}}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        // Variabel global untuk informasi diskon dari PHP
        const discountInfo = @json($discountInfo);

        // --- Bagian Update Jumlah (tetap ada) ---
        const quantityForms = document.querySelectorAll('form[action*="/keranjang/update/"]');
        quantityForms.forEach(form => {
            const inputJumlah = form.querySelector('input[name="jumlah"]');
            const btnMinus = form.querySelector('.btn-quantity-minus');
            const btnPlus = form.querySelector('.btn-quantity-plus');
            // Simpan nilai awal untuk perbandingan
            let initialValue = parseInt(inputJumlah.value);
            inputJumlah.addEventListener('focus', function() {
                initialValue = parseInt(this.value);
            });

            if (btnMinus) {
                btnMinus.addEventListener('click', function() {
                    let currentValue = parseInt(inputJumlah.value);
                    if (currentValue > 1) {
                        inputJumlah.value = currentValue - 1;
                        form.submit(); // Submit form setelah mengurangi
                    }
                });
            }

            if (btnPlus) {
                btnPlus.addEventListener('click', function() {
                    inputJumlah.value = parseInt(inputJumlah.value) + 1;
                    form.submit(); // Submit form setelah menambah
                });
            }
            // Submit on change hanya jika nilai benar-benar berubah
            inputJumlah.addEventListener('change', function() {
                if (parseInt(this.value) !== initialValue) {
                    form.submit();
                }
            });
        });

        // --- Bagian Checkbox dan Kalkulasi Total ---
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const dynamicSubtotalEl = document.getElementById('dynamicSubtotal');
        const dynamicDiscountAmountEl = document.getElementById('dynamicDiscountAmount');
        const dynamicSavingsAmountEl = document.getElementById('dynamicSavingsAmount');
        const discountDescriptionEl = document.getElementById('discountDescription');
        const discountAppliedSection = document.getElementById('discountAppliedSection');
        const dynamicGrandTotalEl = document.getElementById('dynamicGrandTotal');
        const btnDeleteSelected = document.getElementById('btnDeleteSelected');
        const deleteSelectedForm = document.getElementById('deleteSelectedForm');
        const selectedIdsContainer = document.getElementById('selectedIdsContainer');
        const btnLanjutPembayaran = document.getElementById('btnLanjutPembayaran');
        const checkoutForm = document.getElementById('checkoutForm');
        const selectedItemsForCheckoutContainer = document.getElementById('selectedItemsForCheckout');


        function updateRingkasanBelanja() {
            let subtotalTerpilih = 0;
            let countSelected = 0;
            itemCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    subtotalTerpilih += parseFloat(checkbox.dataset.subtotal);
                    countSelected++;
                }
            });

            dynamicSubtotalEl.textContent = 'Rp ' + subtotalTerpilih.toLocaleString('id-ID');
            btnDeleteSelected.disabled = countSelected === 0;

            let actualDiscountApplied = 0;
            if (discountInfo && subtotalTerpilih > 0) {
                // Validasi min_spend jika ada
                if (discountInfo.min_spend && subtotalTerpilih < discountInfo.min_spend) {
                    actualDiscountApplied = 0; // Tidak memenuhi min_spend
                    // Bisa tambahkan notifikasi di sini jika mau
                } else {
                    // Pastikan nilai diskon adalah angka
                    const discountValue = parseFloat(discountInfo.value);
                    if (isNaN(discountValue)) {
                        console.error('Nilai diskon tidak valid:', discountInfo.value);
                        actualDiscountApplied = 0;
                    } else {
                        if (discountInfo.type === 'persen') { // Sesuai dengan 'tipe_diskon' dari controller
                            actualDiscountApplied = (subtotalTerpilih * discountValue) / 100;
                        } else if (discountInfo.type === 'tetap') { // Sesuai dengan 'tipe_diskon' dari controller
                            actualDiscountApplied = discountValue;
                        }
                    }
                }
                // Pastikan diskon tidak melebihi subtotal
                actualDiscountApplied = Math.min(actualDiscountApplied, subtotalTerpilih);
            }

            if (actualDiscountApplied > 0 && discountAppliedSection) {
                let fullDescription = discountInfo.description || 'Diskon';
                const maxLength = 50; // Batas maksimal karakter untuk deskripsi
                if (fullDescription.length > maxLength) {
                    discountDescriptionEl.textContent = fullDescription.substring(0, maxLength) + '...:';
                } else {
                    discountDescriptionEl.textContent = fullDescription + ':';
                }
                dynamicDiscountAmountEl.textContent = '- Rp ' + actualDiscountApplied.toLocaleString('id-ID');
                dynamicSavingsAmountEl.textContent = 'Rp ' + actualDiscountApplied.toLocaleString('id-ID');
                discountAppliedSection.style.display = 'block';
            } else if (discountAppliedSection) {
                discountAppliedSection.style.display = 'none';
            }

            const grandTotal = subtotalTerpilih - actualDiscountApplied;
            dynamicGrandTotalEl.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');

            if (btnLanjutPembayaran) {
                btnLanjutPembayaran.classList.toggle('disabled', countSelected === 0);
                if (countSelected === 0) {
                    // Jika tidak ada item terpilih, tombol dinonaktifkan
                    btnLanjutPembayaran.disabled = true;
                } else {
                    // Jika ada item terpilih, tombol diaktifkan
                    btnLanjutPembayaran.disabled = false;
                }

                // Siapkan data untuk form checkout (tidak submit di sini, hanya siapkan)
                selectedItemsForCheckoutContainer.innerHTML = ''; // Kosongkan container
                itemCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'selected_items[]'; // Nama input untuk item terpilih
                        hiddenInput.value = checkbox.value; // ID item keranjang
                        selectedItemsForCheckoutContainer.appendChild(hiddenInput);
                    }
                });

                // Set kode diskon yang diterapkan (jika ada)
                const appliedDiscountCodeInput = document.getElementById('appliedDiscountCode');
                if (appliedDiscountCodeInput) {
                    appliedDiscountCodeInput.value = discountInfo ? discountInfo.code : '';
                }

            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateRingkasanBelanja();
            });
        }

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    selectAllCheckbox.checked = false;
                } else {
                    // Cek apakah semua item tercentang untuk update selectAllCheckbox
                    let allChecked = true;
                    itemCheckboxes.forEach(cb => {
                        if (!cb.checked) allChecked = false;
                    });
                    selectAllCheckbox.checked = allChecked;
                }
                updateRingkasanBelanja();
            });
        });

        if (deleteSelectedForm) {
            deleteSelectedForm.addEventListener('submit', function(event) {
                selectedIdsContainer.innerHTML = ''; // Kosongkan container
                itemCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'selected_ids[]';
                        hiddenInput.value = checkbox.value;
                        selectedIdsContainer.appendChild(hiddenInput);
                    }
                });
            });
        }

        // Auto-submit form diskon saat pilihan dropdown berubah (pastikan ID elemen benar)
        const discountCodeSelect = document.getElementById('discount_code_select'); // Pastikan ID ini sesuai
        if (discountCodeSelect) {
            discountCodeSelect.addEventListener('change', function() {
                const formToSubmit = document.getElementById('discountForm'); // Pastikan ID ini sesuai
                if (formToSubmit) {
                    formToSubmit.submit();
                }
            });
        }

        // Event listener untuk tombol "Lanjut ke Pembayaran"
        if (btnLanjutPembayaran && checkoutForm) {
            btnLanjutPembayaran.addEventListener('click', function() {
                // Pastikan ada item terpilih sebelum submit
                if (!this.disabled) {
                    checkoutForm.submit(); // Submit form checkout
                }
            });
        }

        // Panggil sekali saat load untuk inisialisasi
        updateRingkasanBelanja();
    });
</script>
@endpush
@endsection