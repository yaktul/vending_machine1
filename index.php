<?php include 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Smart Vending Touch - Midtrans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <script type="text/javascript"
      src="https://app.sandbox.midtrans.com/snap/snap.js"
      data-client-key="Mid-client-9PESKghQQT-85cUe"></script>

    <style>
        :root { --primary-color: #2c3e50; --accent-color: #3498db; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Roboto, sans-serif; overflow: hidden; }
        .vending-wrapper { height: 100vh; display: flex; flex-direction: column; }
        header { background: var(--primary-color); color: white; padding: 20px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .main-content { flex: 1; display: flex; overflow: hidden; }
        .product-section { flex: 3; overflow-y: auto; padding: 25px; }
        .control-section { flex: 1; background: white; border-left: 1px solid #ddd; padding: 25px; display: flex; flex-direction: column; }
        
        /* Gaya Produk Normal */
        .product-card { 
            background: white; border-radius: 20px; border: none; 
            transition: all 0.3s ease; cursor: pointer; position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .product-card:active { transform: scale(0.95); background: #e8f4fd; }
        .product-card img { height: 120px; width: 100%; object-fit: contain; margin-top: 15px; }
        
        /* FITUR OUT OF STOCK: Gaya untuk produk habis */
        .product-card.out-of-stock {
            opacity: 0.6;
            filter: grayscale(1);
            cursor: not-allowed;
            pointer-events: none; /* Mencegah klik sama sekali */
        }
        .stock-label {
            font-weight: bold;
        }
        .text-danger-custom {
            color: #e74c3c;
            font-weight: bold;
            text-transform: uppercase;
        }

        .slot-badge { 
            position: absolute; top: 10px; left: 10px; 
            background: var(--primary-color); color: white; 
            padding: 5px 12px; border-radius: 50px; font-size: 12px; 
        }
        .status-display { 
            background: #1a1a1a; color: #00ff41; padding: 20px; 
            border-radius: 15px; font-family: 'Courier New', monospace;
            margin-bottom: 20px; min-height: 150px; border: 4px solid #333;
        }
        .animate-pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    </style>
</head>
<body>

<div class="vending-wrapper">
    <header>
        <h2 class="mb-0 fw-bold"><i class="bi bi-cpu"></i> VENDKU MACHINE</h2>
    </header>

    <div class="main-content">
        <div class="product-section">
            <div class="row g-4">
                <?php
                $query = mysqli_query($conn, "SELECT * FROM products");
                if(mysqli_num_rows($query) > 0) {
                    while($row = mysqli_fetch_assoc($query)):
                        // Logika Cek Stok Habis
                        $is_out_of_stock = ($row['stock'] <= 0);
                ?>
                <div class="col-md-4">
                    <div class="card product-card h-100 p-2 text-center <?= $is_out_of_stock ? 'out-of-stock' : '' ?>" 
                         onclick="pilihProduk(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>', <?= $row['price'] ?>, <?= $row['stock'] ?>)">
                        
                        <span class="slot-badge"><?= $row['slot_code'] ?></span>
                        <img src="assets/img/<?= $row['image'] ?>" onerror="this.src='https://via.placeholder.com/150?text=Produk'">
                        
                        <div class="card-body">
                            <h5 class="fw-bold mb-1"><?= $row['name'] ?></h5>
                            <h4 class="text-primary fw-bold">Rp <?= number_format($row['price'], 0, ',', '.') ?></h4>
                            
                            <small class="stock-label">
                                <?php if($is_out_of_stock): ?>
                                    <span class="text-danger-custom">HABIS</span>
                                <?php else: ?>
                                    <span class="text-muted">Stok: <?= $row['stock'] ?></span>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
                <?php endwhile; 
                } else {
                    echo "<div class='alert alert-info'>Produk tidak ditemukan.</div>";
                } ?>
            </div>
        </div>

        <div class="control-section shadow-lg">
            <h5 class="fw-bold mb-3 text-uppercase">Layar Monitor</h5>
            <div class="status-display shadow-inner" id="display-text">
                <div class="animate-pulse">SISTEM READY...</div>
                <div class="small mt-2">> Pilih produk untuk membeli</div>
            </div>

            <div id="payment-area" style="display: none;">
                <p class="small text-muted mb-2 text-uppercase fw-bold">Opsi Pembayaran</p>
                <div class="alert alert-primary border-0">
                    <i class="bi bi-shield-check me-2"></i> Pembayaran Aman via Midtrans
                </div>
                <button class="btn btn-success w-100 py-3 fw-bold mb-3 shadow" onclick="prosesBeli()">
                    BAYAR SEKARANG
                </button>
            </div>

            <button class="btn btn-outline-danger w-100 py-2 mt-auto" onclick="resetSistem()">
                BATALKAN
            </button>
            
            <div class="mt-3 text-center">
                <a href="admin/index.php" class="text-decoration-none text-muted small"><i class="bi bi-gear-fill"></i> Admin</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProses" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered text-center">
        <div class="modal-content p-5 border-0 shadow">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
            <h4>Menghubungkan ke Midtrans...</h4>
            <p class="text-muted">Mohon tunggu sebentar</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let produkTerpilih = { id: null, name: null, price: null };
    const loadingModal = new bootstrap.Modal(document.getElementById('modalProses'));

    function pilihProduk(id, nama, harga, stok) {
        // Double Check di sisi client (JavaScript)
        if(stok <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Stok Habis',
                text: 'Maaf, produk ini sudah tidak tersedia.',
                confirmButtonColor: '#2c3e50'
            });
            return;
        }
        
        produkTerpilih.id = id;
        produkTerpilih.name = nama;
        produkTerpilih.price = harga;
        
        document.getElementById('display-text').innerHTML = `
            <div class="text-info">KONFIRMASI PESANAN:</div>
            <div style="font-size: 1.1rem; color: white;">${nama}</div>
            <div class="mt-2 text-warning">Total: Rp ${harga.toLocaleString('id-ID')}</div>
            <div class="small mt-2">> Klik tombol bayar di bawah</div>
        `;
        document.getElementById('payment-area').style.display = 'block';
    }

    function prosesBeli() {
        if(!produkTerpilih.id || !produkTerpilih.name) {
             Swal.fire('Error', 'Data produk tidak lengkap. Silakan pilih ulang.', 'error');
             return;
        }
        
        loadingModal.show();

        fetch('api/place_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: produkTerpilih.id,
                name: produkTerpilih.name,
                price: produkTerpilih.price
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            loadingModal.hide();
            
            if(data.token) {
                window.snap.pay(data.token, {
                    onSuccess: function(result) {
                        Swal.fire('Sukses!', 'Pembayaran berhasil dilakukan.', 'success')
                        .then(() => location.reload());
                    },
                    onPending: function(result) {
                        Swal.fire('Pending', 'Selesaikan pembayaran Anda segera.', 'info');
                    },
                    onError: function(result) {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat pembayaran.', 'error');
                    },
                    onClose: function() {
                        console.log('User menutup popup tanpa membayar');
                    }
                });
            } else {
                Swal.fire('Error', 'Gagal membuat transaksi: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            loadingModal.hide();
            console.error('Error:', error);
            Swal.fire('Server Error', 'Periksa koneksi internet atau file place_order.php Anda.', 'error');
        });
    }

    function resetSistem() {
        location.reload();
    }
</script>

</body>
</html>