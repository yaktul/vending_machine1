<?php
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = $_POST['id'];
    $slot  = mysqli_real_escape_string($conn, $_POST['slot_code']);
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    if(empty($id)) {
        die("Error: ID Produk Kosong. Cek JavaScript anda.");
    }

    $query = "UPDATE products SET 
              slot_code = '$slot', 
              name = '$name', 
              price = '$price', 
              stock = '$stock' 
              WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        header("Location: ../dashboard.php?status=success");
        exit();
    } else {
        echo "Gagal memperbarui data: " . mysqli_error($conn);
    }
}
?>