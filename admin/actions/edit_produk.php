<?php
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $slot = $_POST['slot_code'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = $_POST['image']; // Menangkap input image baru

    $query = "UPDATE products SET 
              slot_code = '$slot', 
              name = '$name', 
              price = '$price', 
              stock = '$stock', 
              image = '$image' 
              WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        header("Location: ../dashboard.php?status=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>