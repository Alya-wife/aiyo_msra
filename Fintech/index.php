<?php
session_start();
include "db_config.php";

$data = file_get_contents('php://input');
$data_decode = json_decode($data, true);

// Periksa apakah file diakses oleh Webhook Aiyo (memiliki data JSON dan invoiceId)
if (empty($data) || !is_array($data_decode) || empty($data_decode['invoiceId'])) {
    echo "Webhook Callback Endpoint siap menerima notifikasi pembayaran dari Aiyo.<br>";
    echo "<i>Catatan: Halaman ini dipanggil secara otomatis oleh sistem Aiyo melalui HTTP POST JSON saat terjadi pembayaran.</i>";
    exit;
}

$invoiceId = $data_decode['invoiceId'] ?? '';
$paymentAccountId = $data_decode['paymentAccountId'] ?? '';
$paymentAccountType = $data_decode['paymentAccountType'] ?? '';
$paymentAccountNumber = $data_decode['paymentAccountNumber'] ?? '';
$paymentAccountName = $data_decode['paymentAccountName'] ?? '';
$amount = $data_decode['amount'] ?? '';
$status = $data_decode['status'] ?? '';
$_SESSION['status'] = "PAID";

// SQL query to select based on invoiceId
$escapedInvoiceId = $conn->real_escape_string($invoiceId);
$sql = "SELECT * FROM `transaksi` WHERE `invoiceId` = '".$escapedInvoiceId."'";

$result = $conn->query($sql);

if ($result && ($row = $result->fetch_assoc())) {
    $_SESSION['referenceId'] = $row['referenceId'];
    $_SESSION['userName'] = $row['userName'];
    $_SESSION['userEmail'] = $row['userEmail'];
    $_SESSION['userPhone'] = $row['userPhone'];
    $_SESSION['remarks'] = $row['remarks'];
    $_SESSION['payAmount'] = $row['payAmount'];
    $_SESSION['detail'] = $row['items'];
} else {
    $teks = "Invoice tidak ditemukan atau error: " . $conn->error;
    $myfile = fopen("error.txt", "a");
    if ($myfile) {
        fwrite($myfile, date('Y-m-d H:i:s') . " - " . $teks . "\n");
        fclose($myfile);
    }
}

//Notifikasi Email dan WhatsApp (buat sendiri ^_^)
//require("../email/sendmail.php");
//require("../fonnte/index.php");

// SQL query to update status based on invoiceId
$sql = "UPDATE `transaksi` SET `status` = 'PAID', `timestamp` = current_timestamp() WHERE `invoiceId` = '".$escapedInvoiceId."'";

if ($conn->query($sql) === TRUE) {
    // Status updated successfully
} else {
    $teks = "Error updating status: " . $conn->error;
    $myfile = fopen("error.txt", "a");
    if ($myfile) {
        fwrite($myfile, date('Y-m-d H:i:s') . " - " . $teks . "\n");
        fclose($myfile);
    }
}

// Close connection
$conn->close();

// log payment
$myfile = fopen("payment.txt", "a");
if ($myfile) {
    fwrite($myfile, date('Y-m-d H:i:s') . " - " . $data . "\n");
    fclose($myfile);
}

// Response JSON ke server Aiyo
header('Content-Type: application/json');
echo json_encode(["status" => "success", "message" => "Callback received"]);
?>