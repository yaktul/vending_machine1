<?php 
include '../config/database.php'; 
// Hitung Total Pendapatan (Hanya yang berstatus success)
$total_query = mysqli_query($conn, "SELECT SUM(amount) as total FROM sales WHERE status = 'success'");
$total_data = mysqli_fetch_assoc($total_query);
$omzet = $total_data['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Admin VM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">VM ADMIN PANEL</a>
        <div class="d-flex">
            <a href="dashboard.php" class="btn btn-outline-light me-2">Stok Barang</a>
            <a href="export_excel.php" class="btn btn-success me-2">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <a href="../index.php" class="btn btn-primary">Buka Web Tablet</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white p-3 shadow-sm border-0">
                <h6>Total Pendapatan (Omzet)</h6>
                <h3>Rp <?= number_format($omzet, 0, ',', '.') ?></h3>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Detail Transaksi Penjualan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu Transaksi</th>
                            <th>Nama Produk</th>
                            <th>Slot</th>
                            <th>Harga Jual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Filter hanya transaksi yang sukses
                        $sql = "SELECT sales.created_at, products.name, products.slot_code, sales.amount 
                                FROM sales 
                                JOIN products ON sales.product_id = products.id 
                                WHERE sales.status = 'success'
                                ORDER BY sales.created_at DESC";
                        $res = mysqli_query($conn, $sql);
                        while($row = mysqli_fetch_assoc($res)):
                        ?>
                        <tr>
                            <td><?= $row['created_at'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><span class="badge bg-secondary"><?= $row['slot_code'] ?></span></td>
                            <td>Rp <?= number_format($row['amount'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>