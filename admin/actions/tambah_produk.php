<?php
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $slot  = mysqli_real_escape_string($conn, $_POST['slot_code']);
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    // Ambil nama file hasil ketikan manual
    $image = mysqli_real_escape_string($conn, $_POST['image']);

    // Query untuk memasukkan data ke database
    $query = "INSERT INTO products (slot_code, name, price, stock, image) 
              VALUES ('$slot', '$name', '$price', '$stock', '$image')";

    if (mysqli_query($conn, $query)) {
        // Kembali ke dashboard jika sukses
        header("Location: ../dashboard.php?status=success");
        exit();
    } else {
        echo "Gagal menambahkan data: " . mysqli_error($conn);
    }
}
?>