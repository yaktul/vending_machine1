<?php
include '../../config/database.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // 1. Hapus transaksi terkait di tabel sales dulu
    mysqli_query($conn, "DELETE FROM sales WHERE product_id = '$id'");
    
    // 2. Baru hapus produknya
    $query = "DELETE FROM products WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        header("Location: ../dashboard.php?status=deleted");
        exit;
    } else {
        echo "Gagal menghapus: " . mysqli_error($conn);
    }
}
?>