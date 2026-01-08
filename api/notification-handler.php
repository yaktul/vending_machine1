<?php
header('Content-Type: application/json');
include '../config/database.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data && ($data['transaction_status'] == 'settlement' || $data['transaction_status'] == 'capture')) {
    $order_id = $data['order_id'];

    // Ambil data product_id dari tabel sales
    $res = mysqli_query($conn, "SELECT product_id FROM sales WHERE order_id = '$order_id'");
    $sale = mysqli_fetch_assoc($res);
    
    if ($sale) {
        $p_id = $sale['product_id'];
        // 1. KURANGI STOK
        mysqli_query($conn, "UPDATE products SET stock = stock - 1 WHERE id = '$p_id'");
        // 2. UPDATE STATUS JADI SUCCESS
        mysqli_query($conn, "UPDATE sales SET status = 'success' WHERE order_id = '$order_id'");
        
        file_put_contents('log_pembayaran.txt', "SUKSES: Order $order_id lunas. Stok ID $p_id berkurang.\n", FILE_APPEND);
    }
}
echo json_encode(['status' => 'OK']);