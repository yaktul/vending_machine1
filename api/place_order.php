<?php
header('Content-Type: application/json');
error_reporting(0); 

include '../config/database.php';
include '../config/midtrans.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    die(json_encode(['status' => 'error', 'message' => 'Data input kosong']));
}

$order_id = 'VM-' . time();
$product_id = $data['id'];
$price = (int)$data['price'];
$product_name = substr($data['name'], 0, 50);

// SIMPAN KE DATABASE
$query = "INSERT INTO sales (order_id, product_id, amount, status) VALUES ('$order_id', '$product_id', '$price', 'pending')";
if (!mysqli_query($conn, $query)) {
    die(json_encode(['status' => 'error', 'message' => 'Database Error: ' . mysqli_error($conn)]));
}

// KE MIDTRANS
$payload = [
    'transaction_details' => ['order_id' => $order_id, 'gross_amount' => $price],
    'item_details' => [['id' => $product_id, 'price' => $price, 'quantity' => 1, 'name' => $product_name]]
];

$ch = curl_init($midtrans_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode($midtrans_server_key . ':')
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;