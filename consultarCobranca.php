<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/config-pix.php';

use Hit\Pix\Api;
use Hit\Pix\Payload;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

$obApiPix = new Api();


$obApiPix->consultCob('HITD1234123412340000000004');

if(!isset($response['location'])){
  echo 'Problemas ao consultar Pix dinâmico';
  echo "<pre>";
  print_r($response);
  echo "</pre>"; exit;
}

echo "<pre>";
print_r($response);
echo "</pre>"; exit;


