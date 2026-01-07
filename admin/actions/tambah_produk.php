<?php
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slot  = mysqli_real_escape_string($conn, $_POST['slot_code']);
    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // --- PROSES UPLOAD GAMBAR ---
    $namaFile = $_FILES['image_file']['name'];
    $ukuranFile = $_FILES['image_file']['size'];
    $error = $_FILES['image_file']['error'];
    $tmpName = $_FILES['image_file']['tmp_name'];

    // Cek apakah ada gambar yang diunggah
    if ($error === 0) {
        // Ambil ekstensi gambar
        $ekstensiGambar = explode('.', $namaFile);
        $ekstensiGambar = strtolower(end($ekstensiGambar));
        
        // Generate nama baru agar tidak bentrok (contoh: 659823_cola.png)
        $namaFileBaru = time() . '_' . $namaFile;

        // Tentukan folder tujuan (naik 2 level ke folder utama, lalu ke assets/img)
        $tujuan = '../../assets/img/' . $namaFileBaru;

        // Pindahkan file ke folder assets/img
        if (move_uploaded_file($tmpName, $tujuan)) {
            $query = "INSERT INTO products (slot_code, name, price, stock, image) 
                      VALUES ('$slot', '$name', '$price', '$stock', '$namaFileBaru')";

            if (mysqli_query($conn, $query)) {
                header("Location: ../dashboard.php?status=success");
                exit();
            } else {
                echo "Gagal input database: " . mysqli_error($conn);
            }
        } else {
            echo "Gagal mengunggah file ke folder tujuan.";
        }
    } else {
        echo "Terjadi kesalahan saat mengunggah file.";
    }
}
?>