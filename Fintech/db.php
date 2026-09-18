<?php
// ==============================================================================
// PENGATURAN KONEKSI DATABASE
// Ganti data di bawah ini sesuai database Anda (misal kredensial hosting)
// ==============================================================================
include_once "db_config.php";

// Pastikan variabel koneksi $conn dari db_config.php tersedia
if (!isset($conn) || $conn->connect_error) {
    die("<div style='color:red; font-family:sans-serif;'><h3>Koneksi ke Database Gagal!</h3>" . ($conn ? htmlspecialchars($conn->connect_error) : "Variabel koneksi tidak ditemukan.") . "</div>");
}

// ==============================================================================
// SCRIPT SQL (MEMBUAT TABEL & MEMASUKKAN DATA)
// ==============================================================================
$sql = "
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS `transaksi` (
  `referenceId` varchar(25) NOT NULL,
  `userName` varchar(50) NOT NULL,
  `userEmail` varchar(40) NOT NULL,
  `userPhone` varchar(20) NOT NULL,
  `remarks` text NOT NULL,
  `payAmount` bigint UNSIGNED NOT NULL,
  `items` text NOT NULL,
  `invoiceId` varchar(25) NOT NULL,
  `invoiceURL` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status` varchar(10) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`invoiceId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `transaksi` (`referenceId`, `userName`, `userEmail`, `userPhone`, `remarks`, `payAmount`, `items`, `invoiceId`, `invoiceURL`, `status`, `timestamp`) VALUES
('YPD0911025433', 'Ridwan Sanjaya', 'ridwan@unika.ac.id', '0818000000', '-', 10000, '[{\"itemName\":\"Barang 1\",\"itemType\":\"ITEM\",\"itemCount\":\"1\",\"itemTotalPrice\":\"1\"},{\"itemName\":\"Barang 2\",\"itemType\":\"ITEM\",\"itemCount\":\"2\",\"itemTotalPrice\":\"2\"}]', '2LQTDgFwNI5wv7XPxYEN', 'https://bills-invoice.aiyo.id/bills/invoice/2LQTDgFwNI5wv7XPxYEN?accessToken=6hWaebeeSQiyipBs53UKvy7TbPOtpfYnDsJPzjeXThXi1mYLPN', 'NEW', '2026-09-11 02:54:34'),
('YPD0911015413', 'Ridwan Sanjaya', 'ridwan@unika.ac.id', '0818000000', '-', 50000, '[{\"itemName\":\"Barang 1\",\"itemType\":\"ITEM\",\"itemCount\":\"1\",\"itemTotalPrice\":\"10000\"},{\"itemName\":\"Barang 2\",\"itemType\":\"ITEM\",\"itemCount\":\"2\",\"itemTotalPrice\":\"20000\"}]', 'jmtKI75H9iYCIi9TRW1f', '', 'NEW', '2026-09-11 01:54:14'),
('YPD0911015414', 'Ridwan Sanjaya', 'ridwan@unika.ac.id', '0818000000', '-', 50000, '[{\"itemName\":\"Barang 1\",\"itemType\":\"ITEM\",\"itemCount\":\"1\",\"itemTotalPrice\":\"10000\"},{\"itemName\":\"Barang 2\",\"itemType\":\"ITEM\",\"itemCount\":\"2\",\"itemTotalPrice\":\"20000\"}]', 'N4lDz674iTyjw3GBfwoN', '', 'NEW', '2026-09-11 01:54:14'),
('YPD0911025343', 'Ridwan Sanjaya', 'ridwan@unika.ac.id', '0818000000', '-', 10000, '[{\"itemName\":\"Barang 1\",\"itemType\":\"ITEM\",\"itemCount\":\"1\",\"itemTotalPrice\":\"1\"},{\"itemName\":\"Barang 2\",\"itemType\":\"ITEM\",\"itemCount\":\"2\",\"itemTotalPrice\":\"2\"}]', 'PZ9SiPDi5q9OsJXe8sBL', 'https://bills-invoice.aiyo.id/bills/invoice/PZ9SiPDi5q9OsJXe8sBL?accessToken=LEljnfXEHz63J0f6UMZv3oi0LMtYrCg5mQ50KDd39dZQVgB5g2', 'NEW', '2026-09-11 02:53:44'),
('YPD0911025514', 'Ridwan Sanjaya', 'ridwan@unika.ac.id', '0818000000', '-', 10000, '[{\"itemName\":\"Barang 1\",\"itemType\":\"ITEM\",\"itemCount\":\"1\",\"itemTotalPrice\":\"1\"},{\"itemName\":\"Barang 2\",\"itemType\":\"ITEM\",\"itemCount\":\"2\",\"itemTotalPrice\":\"2\"}]', 'Rl6HZnvw3Xtmj6UmievH', 'https://bills-invoice.aiyo.id/bills/invoice/Rl6HZnvw3Xtmj6UmievH?accessToken=qS3zJ5g4g273gsqaH3hzVRNwovRLzBGhEEUPjxM6d6ej1GlYLh', 'NEW', '2026-09-11 02:55:14'),
('YPD0911015352', 'Ridwan Sanjaya', 'ridwan@unika.ac.id', '0818000000', '-', 50000, '[{\"itemName\":\"Barang 1\",\"itemType\":\"ITEM\",\"itemCount\":\"1\",\"itemTotalPrice\":\"10000\"},{\"itemName\":\"Barang 2\",\"itemType\":\"ITEM\",\"itemCount\":\"2\",\"itemTotalPrice\":\"20000\"}]', 'wyeObZedXvh9bunCKgjB', '', 'NEW', '2026-09-11 01:53:53')
ON DUPLICATE KEY UPDATE status=VALUES(status);
";

echo "<div style='font-family: sans-serif; padding: 20px;'>";
echo "<h2>Proses Eksekusi Database (db.php)</h2>";

// Eksekusi semua query SQL
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    echo "<p style='color: green; font-size: 16px;'><strong>Sukses!</strong> Tabel <code>transaksi</code> dan seluruh data berhasil dibuat/diimpor.</p>";
    echo "<p style='color: #b30000;'><strong>PERHATIAN KEAMANAN:</strong> Jangan lupa segera <u>hapus</u> file <code>db.php</code> ini dari hosting/FTP Anda setelah berhasil!</p>";
} else {
    echo "<p style='color: red;'><strong>Gagal mengeksekusi SQL:</strong> " . htmlspecialchars($conn->error) . "</p>";
}

echo "</div>";

$conn->close();
?>
