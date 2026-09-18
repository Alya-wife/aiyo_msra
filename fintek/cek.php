<?php
 include "aiyo_config.php";
 $pathInvoice = '/api/v1/invoice';
 $invoiceId = $_GET['invoiceId'];
 $accessToken = $_GET['accessToken'];
 $pathInvoice."/".$invoiceId."?accessToken=".$accessToken;
 $URLCekStatus = $host . $pathInvoice."/".$invoiceId."?accessToken=".$accessToken;
 $chCekInvoice = curl_init($URLCekStatus);
 curl_setopt($chCekInvoice, CURLOPT_TIMEOUT, 30);
 curl_setopt($chCekInvoice, CURLOPT_RETURNTRANSFER, TRUE);
 $responseCekInvoice = curl_exec($chCekInvoice);
 $cekInvoice=json_decode($responseCekInvoice);
 $status=$cekInvoice->responseData->invoiceStatus;
 echo "Invoice: ".$cekInvoice->responseData->invoiceName;
 echo "<br/>Senilai: ".$cekInvoice->responseData->payAmount;
 echo "<br/>Status: ".$status;
 echo "<br/><br/><a href='".$cekInvoice->responseData->invoiceURL."' target='_blank'>Lanjutkan pembayaran</a>";
?>