<?php
include '../../config/database.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "DELETE FROM products WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        header("Location: ../dashboard.php?status=deleted");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>