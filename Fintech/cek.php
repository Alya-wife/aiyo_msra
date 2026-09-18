<?php
 include "aiyo_config.php";
 $pathInvoice = '/api/v1/invoice';

 // Mengambil invoiceId (mendukung variasi invoiceId, invoiceID, maupun typo inoviceID)
 $invoiceId = $_GET['invoiceId'] ?? $_GET['invoiceID'] ?? $_GET['inoviceID'] ?? $_GET['inoviceId'] ?? '';
 $accessToken = $_GET['accessToken'] ?? '';

 if (empty($invoiceId) || empty($accessToken)) {
     die("Silakan sertakan parameter <b>invoiceId</b> dan <b>accessToken</b> pada URL.<br>Contoh: <code>cek.php?invoiceId=xxx&accessToken=yyy</code>");
 }

 $URLCekStatus = $host . $pathInvoice . "/" . $invoiceId . "?accessToken=" . $accessToken;
 $chCekInvoice = curl_init($URLCekStatus);
 curl_setopt($chCekInvoice, CURLOPT_TIMEOUT, 30);
 curl_setopt($chCekInvoice, CURLOPT_RETURNTRANSFER, TRUE);
 $responseCekInvoice = curl_exec($chCekInvoice);
 curl_close($chCekInvoice);

 $cekInvoice = json_decode($responseCekInvoice);

 if (isset($cekInvoice->responseCode) && $cekInvoice->responseCode == '2000000' && isset($cekInvoice->responseData)) {
     $data = $cekInvoice->responseData;
     $status = $data->invoiceStatus;
     echo "Invoice: " . $data->invoiceName;
     echo "<br/>Senilai: " . $data->payAmount;
     echo "<br/>Status: " . $status;
     echo "<br/><br/><a href='" . $data->invoiceURL . "' target='_blank'>Lanjutkan pembayaran</a>";
 } else {
     $pesan = isset($cekInvoice->responseMessage) ? $cekInvoice->responseMessage : 'Gagal mengambil data invoice.';
     echo "Error: " . $pesan;
 }
?>