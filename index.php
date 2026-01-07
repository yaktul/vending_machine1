<?php include 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Smart Vending Touch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary-color: #2c3e50; --accent-color: #3498db; }
        body { background: #f0f2f5; font-family: 'Segoe UI', Roboto, sans-serif; overflow: hidden; }
        
        /* Layout khusus tablet */
        .vending-wrapper { height: 100vh; display: flex; flex-direction: column; }
        header { background: var(--primary-color); color: white; padding: 20px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        .main-content { flex: 1; display: flex; overflow: hidden; }
        .product-section { flex: 3; overflow-y: auto; padding: 25px; }
        .control-section { flex: 1; background: white; border-left: 1px solid #ddd; padding: 25px; display: flex; flex-direction: column; }

        /* Card Produk Touch-Friendly */
        .product-card { 
            background: white; border-radius: 20px; border: none; 
            transition: all 0.3s ease; cursor: pointer; position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .product-card:active { transform: scale(0.95); background: #e8f4fd; }
        .product-card img { height: 120px; object-fit: contain; margin-top: 15px; }
        .slot-badge { 
            position: absolute; top: 10px; left: 10px; 
            background: var(--primary-color); color: white; 
            padding: 5px 12px; border-radius: 50px; font-size: 12px; 
        }
        
        /* Layar Status */
        .status-display { 
            background: #1a1a1a; color: #00ff41; padding: 20px; 
            border-radius: 15px; font-family: 'Courier New', monospace;
            margin-bottom: 20px; min-height: 150px; border: 4px solid #333;
        }
    </style>
</head>
<body>

<div class="vending-wrapper">
    <header>
        <h2 class="mb-0 fw-bold"><i class="bi bi-cpu"></i> TECH VENDING MACHINE</h2>
    </header>

    <div class="main-content">
        <div class="product-section">
            <div class="row g-4">
                <?php
                $query = mysqli_query($conn, "SELECT * FROM products");
                if(mysqli_num_rows($query) > 0) {
                    while($row = mysqli_fetch_assoc($query)):
                ?>
                <div class="col-md-4 col-sm-6">
                    <div class="card product-card h-100 p-2 text-center" 
                         onclick="pilihProduk(<?= $row['id'] ?>, '<?= $row['name'] ?>', <?= $row['price'] ?>, <?= $row['stock'] ?>)">
                        <span class="slot-badge"><?= $row['slot_code'] ?></span>
                        <img src="assets/img/<?= $row['image'] ?>" onerror="this.src='https://via.placeholder.com/150?text=No+Image'">
                        <div class="card-body">
                            <h5 class="fw-bold mb-1"><?= $row['name'] ?></h5>
                            <h4 class="text-primary fw-bold">Rp <?= number_format($row['price'], 0, ',', '.') ?></h4>
                            <small class="text-muted">Tersedia: <?= $row['stock'] ?></small>
                        </div>
                    </div>
                </div>
                <?php endwhile; 
                } else {
                    echo "<div class='alert alert-info'>Belum ada produk. Silakan tambah di Dashboard Admin.</div>";
                } ?>
            </div>
        </div>

        <div class="control-section shadow-lg">
            <h5 class="fw-bold mb-3 text-uppercase">Status Transaksi</h5>
            <div class="status-display shadow-inner" id="display-text">
                <div class="animate-pulse">SISTEM SIAP...</div>
                <div class="small mt-2">> Silakan pilih produk di layar kiri</div>
            </div>

            <div id="payment-area" style="display: none;">
                <p class="small text-muted mb-2 text-uppercase fw-bold">Instruksi Pembayaran</p>
                <div class="alert alert-warning border-0">
                    <i class="bi bi-qr-code-scan me-2"></i> Scan QR untuk membayar
                </div>
                <button class="btn btn-success w-100 py-3 fw-bold mb-3" onclick="prosesBeli()">
                    KONFIRMASI PEMBAYARAN
                </button>
            </div>

            <button class="btn btn-outline-danger w-100 py-2 mt-auto" onclick="resetSistem()">
                BATALKAN
            </button>
            
            <div class="mt-3 text-center">
                <a href="admin/index.php" class="text-decoration-none text-muted small"><i class="bi bi-gear-fill"></i> Panel Admin</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProses" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered text-center">
        <div class="modal-content p-5">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
            <h4>Sedang Memproses...</h4>
            <p class="text-muted">Jangan tinggalkan mesin</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let produkTerpilih = null;

    function pilihProduk(id, nama, harga, stok) {
        if(stok <= 0) {
            Swal.fire('Maaf', 'Stok produk ini sedang kosong', 'error');
            return;
        }
        produkTerpilih = { id, nama, harga };
        
        document.getElementById('display-text').innerHTML = `
            <div>PRODUK TERPILIH:</div>
            <div style="font-size: 1.2rem; color: white;">${nama}</div>
            <div class="mt-2 text-warning">Rp ${harga.toLocaleString('id-ID')}</div>
            <div class="small mt-1 text-info">> Menunggu Pembayaran...</div>
        `;
        document.getElementById('payment-area').style.display = 'block';
    }

    function prosesBeli() {
        if(!produkTerpilih) return;
        
        const modal = new bootstrap.Modal(document.getElementById('modalProses'));
        modal.show();

        // Simulasi pengiriman data ke backend
        fetch('api/order.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `product_id=${produkTerpilih.id}`
        })
        .then(res => res.json())
        .then(data => {
            setTimeout(() => {
                modal.hide();
                if(data.status === 'success') {
                    Swal.fire('Terima Kasih!', 'Silakan ambil produk Anda', 'success')
                    .then(() => location.reload());
                } else {
                    Swal.fire('Gagal', 'Terjadi kesalahan sistem', 'error');
                }
            }, 2000);
        });
    }

    function resetSistem() {
        location.reload();
    }
</script>

</body>
</html>