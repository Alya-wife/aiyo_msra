<?php
session_start();
include __DIR__ . "/../db_config.php";

$data = file_get_contents('php://input');
$data_decode = json_decode($data, true);
//var_dump($data_decode);

// Validasi jika file dibuka langsung dari browser tanpa payload JSON dari payment gateway
if (empty($data_decode) || !isset($data_decode['invoiceId'])) {
    http_response_code(400);
    echo "Endpoint Callback siap. Endpoint ini hanya menerima data POST JSON dari server payment gateway.";
    exit;
}

$invoiceId = $data_decode['invoiceId'] ?? '';
$paymentAccountId = $data_decode['paymentAccountId'] ?? '';
$paymentAccountType = $data_decode['paymentAccountType'] ?? '';
$paymentAccountNumber = $data_decode['paymentAccountNumber'] ?? '';
$paymentAccountName = $data_decode['paymentAccountName'] ?? '';
$amount = $data_decode['amount'] ?? 0;
$status = $data_decode['status'] ?? '';
$_SESSION['status']="PAID";

// SQL query to select based on invoiceId
$sql = "SELECT * FROM `transaksi` WHERE `invoiceId` = '".$invoiceId."'";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['referenceId']=$row['referenceId'] ?? '';
    $_SESSION['userName']=$row['userName'] ?? '';
    $_SESSION['userEmail']=$row['userEmail'] ?? '';
    $_SESSION['userPhone']=$row['userPhone'] ?? '';
    $_SESSION['remarks']=$row['remarks'] ?? '';
    $_SESSION['payAmount']=$row['payAmount'] ?? 0;
    $_SESSION['detail']=$row['items'] ?? '';
} else {
    $teks = "Invoice tidak ditemukan atau error: " . $conn->error;
    $myfile = fopen("error.txt", "a") or die("Unable to open file!");
    fwrite($myfile, $teks . "\n");
    fclose($myfile);    
}

//Notifikasi Email dan WhatsApp (buat sendiri ^_^)
//require("../email/sendmail.php");
//require("../fonnte/index.php");

// SQL query to update status based on invoiceId
$sql = "UPDATE `transaksi` SET `status` = 'PAID', `timestamp` = current_timestamp() WHERE `invoiceId` = '".$invoiceId."'";

if ($conn->query($sql) === TRUE) {
    //echo "Status updated successfully";
    
} else {
    $teks = "Error updating status: " . $conn->error;
    $myfile = fopen("error.txt", "a") or die("Unable to open file!");
    fwrite($myfile, $teks . "\n");
    fclose($myfile);    
}

// Close connection
$conn->close();

// Sync with SpaceKey Booking system
try {
    require_once __DIR__ . '/../../backend/src/Services/AiyoBillsService.php';
    $aiyoService = new AiyoBillsService();
    $aiyoService->handleWebhook($data_decode);
} catch (Throwable $e) {
    @file_put_contents(__DIR__ . '/error.txt', "SpaceKey booking sync error: " . $e->getMessage() . "\n", FILE_APPEND);
}

// log payment
$myfile = fopen("payment.txt", "a") or die("Unable to open file!");
fwrite($myfile, $data . "\n");
fclose($myfile);
?>