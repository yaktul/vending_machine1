// ==========================================
// 1. LOGIKA FEEDBACK LOGIN
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('#loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            const btn = this.querySelector('button');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
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
            <div class="small mt-2 animate-flicker">> Menunggu Pembayaran...</div>
        `;
    }
    
    const payArea = document.getElementById('payment-area');
    if (payArea) payArea.style.display = 'block';
}

function prosesBeli() {
    if (!produkTerpilih) return;

    const modalElement = document.getElementById('modalProses');
    if (!modalElement) return;

    const modal = new bootstrap.Modal(modalElement);
    modal.show();

    const formData = new URLSearchParams();
    formData.append('product_id', produkTerpilih.id);

    fetch('api/order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        setTimeout(() => {
            modal.hide();
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Sukses!',
                    text: 'Silakan ambil ' + produkTerpilih.nama + ' di laci bawah.',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); 
                });
            } else {
                Swal.fire('Gagal!', data.message, 'error');
            }
        }, 2000);
    })
    .catch(error => {
        modal.hide();
        console.error('Error:', error);
        Swal.fire('Error', 'Gagal menghubungi server', 'error');
    });
}

function resetSistem() {
    location.reload();
}

// ==========================================
// 3. LOGIKA ADMIN (EDIT MODAL)
// ==========================================
// PENTING: Fungsi ini HARUS berada di luar event listener agar bisa dipanggil onclick
function isiModalEdit(data) {
    console.log("Data diterima untuk Edit:", data); 
    
    // Pastikan ID elemen ada sebelum diisi nilainya
    const elId = document.getElementById('edit_id');
    const elSlot = document.getElementById('edit_slot');
    const elName = document.getElementById('edit_name');
    const elPrice = document.getElementById('edit_price');
    const elStock = document.getElementById('edit_stock');

    if (elId) elId.value = data.id;
    if (elSlot) elSlot.value = data.slot_code;
    if (elName) elName.value = data.name;
    if (elPrice) elPrice.value = data.price;
    if (elStock) elStock.value = data.stock;
}