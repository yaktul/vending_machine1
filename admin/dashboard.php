<?php 
session_start();

// LOGIKA: Jika sesi admin_logged_in TIDAK ADA, lempar ke login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

include '../config/database.php'; 

// Statistik Ringkas
$stok_habis_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE stock = 0");
$stok_habis = mysqli_fetch_assoc($stok_habis_res)['total'];

$total_item_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$total_item = mysqli_fetch_assoc($total_item_res)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Stok - Vending Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="bi bi-cpu"></i> VM ADMIN PANEL</a>
        <div class="d-flex">
            <a href="laporan.php" class="btn btn-outline-light me-2">Lihat Laporan</a>
            <a href="logout.php" class="btn btn-danger me-2">Logout</a>
            <a href="../index.php" class="btn btn-primary">Buka Web Tablet</a>
        </div>
    </div>
</nav>

<div class="container">
    <?php if(isset($_GET['status'])): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?php 
                $status = $_GET['status'];
                if($status === 'success') echo 'Operasi Berhasil!';
                elseif($status === 'deleted') echo 'Produk Berhasil Dihapus!';
                else echo 'Terjadi Perubahan!';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card p-3 shadow-sm border-0 border-start border-primary border-4">
                <small class="text-muted text-uppercase fw-bold">Total Slot Terisi</small>
                <h3><?= $total_item ?> Produk</h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 shadow-sm border-0 border-start border-danger border-4">
                <small class="text-muted text-uppercase fw-bold">Produk Kosong (Refill Needed)</small>
                <h3 class="text-danger"><?= $stok_habis ?> Item</h3>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold">Pengaturan Inventaris</h5>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Produk Baru
        </button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Slot</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = mysqli_query($conn, "SELECT * FROM products ORDER BY slot_code ASC");
                    while($row = mysqli_fetch_assoc($res)):
                    ?>
                    <tr>
                        <td><span class="badge bg-dark"><?= $row['slot_code'] ?></span></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                        <td>
                            <?php if($row['stock'] <= 0): ?>
                                <span class="text-danger fw-bold text-uppercase">Habis</span>
                            <?php else: ?>
                                <?= $row['stock'] ?> pcs
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEdit" 
                                    onclick="isiModalEdit(<?= htmlspecialchars(json_encode($row)) ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="actions/hapus_produk.php?id=<?= $row['id'] ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <form action="actions/tambah_produk.php" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Barang Ke Mesin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="small fw-bold">Kode Slot</label>
                    <input type="text" name="slot_code" class="form-control" placeholder="Contoh: A1" required>
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Nama Produk</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Coca Cola" required>
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Harga (Rp)</label>
                    <input type="number" name="price" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Stok Awal</label>
                    <input type="number" name="stock" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Unggah Gambar Produk</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*" required>
                    <small class="text-muted">Format: JPG, PNG, atau WebP</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan ke Mesin</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <form action="actions/edit_produk.php" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="old_image" id="edit_image_hidden">
                
                <div class="mb-2">
                    <label class="small fw-bold">Kode Slot</label>
                    <input type="text" name="slot_code" id="edit_slot" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Nama Produk</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Harga (Rp)</label>
                    <input type="number" name="price" id="edit_price" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Jumlah Stok</label>
                    <input type="number" name="stock" id="edit_stock" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="small fw-bold">Ganti Gambar Produk</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                    <small class="text-info">Kosongkan jika tidak ingin mengganti gambar.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>