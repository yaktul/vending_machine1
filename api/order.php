<?php
header('Content-Type: application/json');
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];

    // 1. Cek ketersediaan stok produk
    $check_query = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id'");
    $product = mysqli_fetch_assoc($check_query);

    if ($product && $product['stock'] > 0) {
        $price = $product['price'];

        // 2. Kurangi stok produk
        $update_stok = mysqli_query($conn, "UPDATE products SET stock = stock - 1 WHERE id = '$product_id'");

        // 3. Masukkan ke riwayat penjualan (Sales)
        $insert_sales = mysqli_query($conn, "INSERT INTO sales (product_id, amount) VALUES ('$product_id', '$price')");

        if ($update_stok && $insert_sales) {
            echo json_encode(['status' => 'success', 'message' => 'Transaksi berhasil']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memproses ke database']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Stok habis atau barang tidak ditemukan']);
    }
}
?>