<?php
include '../config/database.php';

// Memberi tahu browser untuk mendownload file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Penjualan_Vendku.xls");
?>

<h3>LAPORAN PENJUALAN VENDKU MACHINE</h3>
<p>Tanggal Cetak: <?= date('d-m-Y H:i:s') ?></p>

<table border="1">
    <thead>
        <tr>
            <th style="background-color: #2c3e50; color: white;">No</th>
            <th style="background-color: #2c3e50; color: white;">Waktu Transaksi</th>
            <th style="background-color: #2c3e50; color: white;">Nama Produk</th>
            <th style="background-color: #2c3e50; color: white;">Slot</th>
            <th style="background-color: #2c3e50; color: white;">Harga Jual</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $total = 0;
        $sql = "SELECT sales.created_at, products.name, products.slot_code, sales.amount 
                FROM sales 
                JOIN products ON sales.product_id = products.id 
                WHERE sales.status = 'success'
                ORDER BY sales.created_at DESC";
        $query = mysqli_query($conn, $sql);
        
        while($row = mysqli_fetch_assoc($query)):
            $total += $row['amount'];
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['created_at'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['slot_code'] ?></td>
            <td><?= $row['amount'] ?></td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="4" align="right"><b>TOTAL PENDAPATAN:</b></td>
            <td><b><?= $total ?></b></td>
        </tr>
    </tbody>
</table>