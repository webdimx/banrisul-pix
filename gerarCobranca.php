<?php

require __DIR__.'/vendor/autoload.php';

use Hit\Pix\Api;
use Hit\Pix\Payload;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

$obApiPix = new Api();

$request = [
  'calendario' => [
    'expiracao' => 3600
  ],
  'devedor' => [
    'cpf' => '12345678909',
    'nome' => 'Fulano de Tal'
  ],
  'valor' => [
    'original' => '10.00'
  ],
  'chave' => PIX_KEY,
  'solicitacaoPagador' => 'Pagamento do pedido 123'
];

$response = $obApiPix->createCob($request);


if(!isset($response['location'])){

  $data = [
    'message' => 'Error',
    'response' => $response
  ];
  echo json_encode($data);
  return;

}

$obPayload = (new Payload)->setMerchantName(PIX_MERCHANT_NAME)
                          ->setMerchantCity(PIX_MERCHANT_CITY)
                          ->setAmount($response['valor']['original'])
                          ->setTxid('***')
                          ->setUrl($response['location']);

$payloadQrCode = $obPayload->getPayload();
$QrCode = new QrCode($payloadQrCode);
$image = (new Output\Png)->output($QrCode,400);

$data = [
  'message' => 'Charge created',
  'value' => $response['valor']['original'],
  'image' => base64_encode($image),
  'code' => $payloadQrCode
];



?>

