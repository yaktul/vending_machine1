// ==========================================
// 1. LOGIKA FEEDBACK LOGIN
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('#loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            const btn = this.querySelector('button');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyiapkan Panel...';
            btn.disabled = true;
        });
    }
});

// ==========================================
// 2. LOGIKA VENDING MACHINE (CUSTOMER SIDE)
// ==========================================
let produkTerpilih = null;

function pilihProduk(id, nama, harga, stok) {
    if (stok <= 0) {
        Swal.fire({
            icon: 'error',
            title: 'Maaf...',
            text: 'Stok ' + nama + ' sedang kosong!',
            confirmButtonColor: '#2c3e50'
        });
        return;
    }

    produkTerpilih = { id, nama, harga };
    
    const display = document.getElementById('display-text');
    if (display) {
        display.innerHTML = `
            <div class="text-info fw-bold mb-1">PRODUK TERPILIH:</div>
            <div class="fs-5 text-white">${nama}</div>
            <div class="text-warning mt-1">Rp ${harga.toLocaleString('id-ID')}</div>
            <div class="small mt-2 animate-flicker">> Silakan Konfirmasi Pembayaran</div>
        `;
    }
    
    const payArea = document.getElementById('payment-area');
    if (payArea) payArea.style.display = 'block';
}

/**
 * INTEGRASI MIDTRANS SNAP
 */
function prosesBeli() {
    if (!produkTerpilih) return;

    Swal.fire({
        title: 'Menyiapkan Pembayaran...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch('api/place_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(produkTerpilih)
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        Swal.close();

        if (data.token) {
            window.snap.pay(data.token, {
                onSuccess: function(result) {
                    Swal.fire('Berhasil!', 'Pembayaran diterima. Silakan ambil barang.', 'success')
                        .then(() => { location.reload(); });
                },
                onPending: function(result) {
                    Swal.fire('Menunggu', 'Selesaikan pembayaran QRIS Anda.', 'info');
                },
                onError: function(result) {
                    Swal.fire('Gagal', 'Terjadi kesalahan sistem pembayaran.', 'error');
                },
                onClose: function() {
                    console.log('User menutup popup');
                }
            });
        } else {
            Swal.fire('Error', data.message || 'Gagal mendapatkan token', 'error');
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error:', error);
        Swal.fire('Error', 'Gagal menghubungi server pembayaran', 'error');
    });
}

function resetSistem() {
    location.reload();
}

// ==========================================
// 3. LOGIKA ADMIN (EDIT MODAL)
// ==========================================
function isiModalEdit(data) {
    console.log("Data diterima untuk Edit:", data); 
    
    const elId = document.getElementById('edit_id');
    const elSlot = document.getElementById('edit_slot');
    const elName = document.getElementById('edit_name');
    const elPrice = document.getElementById('edit_price');
    const elStock = document.getElementById('edit_stock');
    const elOldImage = document.getElementById('edit_image_hidden'); // Input Hidden untuk nama file lama

    // Reset input file setiap kali modal dibuka (keamanan browser tidak mengizinkan isi value input file)
    const elImageFile = document.querySelector('input[name="image_file"]');
    if (elImageFile) elImageFile.value = '';

    if (elId) elId.value = data.id;
    if (elSlot) elSlot.value = data.slot_code;
    if (elName) elName.value = data.name;
    if (elPrice) elPrice.value = data.price;
    if (elStock) elStock.value = data.stock;
    if (elOldImage) elOldImage.value = data.image; // Simpan nama file gambar lama agar tidak hilang jika tidak diganti
}