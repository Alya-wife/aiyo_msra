<?php
 if (session_status() === PHP_SESSION_NONE) {
     session_start();
 }
 include "token.php";

 $userName = trim($_POST['userName'] ?? ($_POST['customer_name'] ?? ($_POST['customerName'] ?? ($_GET['userName'] ?? ($_GET['customer_name'] ?? ($_GET['customerName'] ?? ($_SESSION['user']['name'] ?? '')))))));
 $userEmail = trim($_POST['userEmail'] ?? ($_POST['customer_email'] ?? ($_POST['customerEmail'] ?? ($_GET['userEmail'] ?? ($_GET['customer_email'] ?? ($_GET['customerEmail'] ?? ($_SESSION['user']['email'] ?? '')))))));
 $userPhone = trim($_POST['userPhone'] ?? ($_POST['customer_phone'] ?? ($_POST['customerPhone'] ?? ($_GET['userPhone'] ?? ($_GET['customer_phone'] ?? ($_GET['customerPhone'] ?? ($_SESSION['user']['phone'] ?? '')))))));
 if (empty($userPhone) || $userPhone === '081234567890') {
     $userPhone = !empty($_SESSION['user']['phone']) && $_SESSION['user']['phone'] !== '081234567890' ? $_SESSION['user']['phone'] : '085329000345';
 }
 $remarks = $_POST['remarks'] ?? $_GET['remarks'] ?? 'Pemesanan Ruangan MSRA';
 $payAmount = isset($_POST['payAmount']) ? (int)$_POST['payAmount'] : (isset($_GET['payAmount']) ? (int)$_GET['payAmount'] : (isset($_POST['amount']) ? (int)$_POST['amount'] : (isset($_GET['amount']) ? (int)$_GET['amount'] : 0)));
 $invoiceName = $_POST['invoiceName'] ?? $_GET['invoiceName'] ?? ("MSRA - " . $remarks);

 if (empty($userName) || empty($userEmail)) {
     http_response_code(400);
     echo "Data pemesan (Nama dan Email) tidak ditemukan. Silakan login terlebih dahulu melalui akun Google.";
     exit;
 }

 //Create Invoice
 $bodyCreateInvoice = array(
 "invoiceName" => $invoiceName,
 "referenceId" => "MSRA".date("mdHis").rand(10,99),
 "userName" => $userName,
 "userEmail" => $userEmail,
 "userPhone" => $userPhone,
 "remarks" => $remarks,
 "payAmount" => $payAmount,
 "expireTime" => date('Y-m-d\TH:i', strtotime('+24 hour')),
 "billMasterId" => $billMasterId,
 "paymentMethod" => array(
 "type" => "VA_CLOSED",
 "bankCode" => "022"
 ),
"items" => array(
 array(
 "itemName" => "Barang 1",
 "itemType" => "ITEM",
 "itemCount" => "1",
 "itemTotalPrice" => "1",
 ),
 array(
 "itemName" => "Barang 2",
 "itemType" => "ITEM",
 "itemCount" => "2",
 "itemTotalPrice" => "2",
  )
 )
);
 //Signature
 $pathInvoice = '/api/v1/invoice';
 $urlCreateInvoice = $host . $pathInvoice;
 $signRelativeURLCreateInvoice = parse_url($urlCreateInvoice, PHP_URL_PATH);
 $rawBodyCreateInvoice = json_encode($bodyCreateInvoice);
 $dataToSignCreateInvoice = $api_key . $signRelativeURLCreateInvoice . $rawBodyCreateInvoice;
 $signatureCreateInvoice = hash_hmac('sha256', $dataToSignCreateInvoice, $api_secret);
 $chCreateInvoice = curl_init($urlCreateInvoice);
 $headersCreateInvoice = array(
 "Content-Type: application/json",
 "Authorization: Bearer ". $accessToken,
 "x-aiyo-key: " . $api_key,
 "x-aiyo-signature: " . $signatureCreateInvoice
 );
 curl_setopt($chCreateInvoice, CURLOPT_TIMEOUT, 30);
 curl_setopt($chCreateInvoice, CURLOPT_POST, 1);
 curl_setopt($chCreateInvoice, CURLOPT_RETURNTRANSFER, TRUE);
 curl_setopt($chCreateInvoice, CURLOPT_HTTPHEADER, $headersCreateInvoice);
 curl_setopt($chCreateInvoice, CURLOPT_POSTFIELDS, $rawBodyCreateInvoice);
 $sentHeaders = curl_getinfo($chCreateInvoice, CURLINFO_HEADER_OUT);
 $responseCreateInvoice = curl_exec($chCreateInvoice);
 $invoice=json_decode($responseCreateInvoice);
 if(isset($invoice->responseCode) && $invoice->responseCode == '2000000') {
     $invoiceId = $invoice->responseData->invoiceId;
     $accessToken = $invoice->responseData->accessToken;
     $invoiceURL = $invoice->responseData->invoiceURL ?? '';
     include "db_config.php";

     $referenceId = $bodyCreateInvoice['referenceId'];
     $userName = $bodyCreateInvoice['userName'];
     $userEmail = $bodyCreateInvoice['userEmail'];
     $userPhone = $bodyCreateInvoice['userPhone'];
     $remarks = $bodyCreateInvoice['remarks'] ?? '-';
     $payAmount = $bodyCreateInvoice['payAmount'];
     $detail = isset($_POST['items']) ? (is_array($_POST['items']) ? json_encode($_POST['items']) : $_POST['items']) : json_encode($bodyCreateInvoice['items']);
     $cleanRemarks = str_replace( array( '\'', '"', ',' , ';', '<', '>', '/' ), ' ', $remarks);

     $stmt = $conn->prepare("INSERT INTO `transaksi` (`referenceId`, `userName`, `userEmail`, `userPhone`, `remarks`, `payAmount`, `items`, `invoiceId`, `invoiceURL`, `status`, `timestamp`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'NEW', current_timestamp()) ON DUPLICATE KEY UPDATE `status`=VALUES(`status`), `invoiceURL`=VALUES(`invoiceURL`)");
     if ($stmt) {
         $stmt->bind_param("sssssisss", $referenceId, $userName, $userEmail, $userPhone, $cleanRemarks, $payAmount, $detail, $invoiceId, $invoiceURL);
         if ($stmt->execute()) {
             echo "Data inserted successfully<br/>";
         } else {
             echo "Error: " . $stmt->error . "<br>";
         }
         $stmt->close();
     } else {
         echo "Error preparing statement: " . $conn->error . "<br>";
     }

     // Close connection
     $conn->close();
 }
 curl_close($chCreateInvoice);

 if (isset($invoiceId) && isset($accessToken)) {
     echo "Invoice ID: ". $invoiceId."<br/>Access Token: ". $accessToken;
     echo "<br/><br/><a href='" . $invoiceURL . "' target='_blank'>Lanjutkan Pembayaran</a>";
 } else {
     echo "Gagal membuat invoice. Response API:<br>";
     echo "<pre>" . htmlspecialchars($responseCreateInvoice) . "</pre>";
 }
?>
