<?php

namespace Hit\Pix;

require dirname(__DIR__, 2).'/config.php';

class Api{

  /**
   * Base PSP URL
   * @var string
   */
  private $baseUrl;

  /**
   * Client ID PSP oAuth2
   * @var string
   */
  private $clientId;

  /**
   * Client secret PSP oAuht2
   * @var string
   */
  private $clientSecret;


  /**
   * Construct class
   * @param string $baseUrl
   * @param string $clientId
   * @param string $clientSecret
   */
  public function __construct(){
    $this->baseUrl      = API_PIX_URL;
    $this->clientId     = API_PIX_CLIENT_ID;
    $this->clientSecret = API_PIX_CLIENT_SECRET;
  }


  /**
   * Get access token
   * @return string
   */
  private function getAccessToken(){

    $endpoint = $this->baseUrl.'/auth/oauth/v2/token';

    $headers = [
      'Content-Type: application/x-www-form-urlencoded'
    ];

    $scopes = [
      'cob.read',
      'cob.write',
      'pix.read',
      'pix.write',
      'webhook.read',
      'webhook.write',
    ];

    $request = 'grant_type=client_credentials&client_id=' . $this->clientId . '&client_secret=' . $this->clientSecret . '&scope=' . implode(' ', $scopes);

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api-h.banrisul.com.br/auth/oauth/v2/token',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $request,
      CURLOPT_HTTPHEADER => $headers
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response,true);
    $token = $data['access_token'];

    if (!$token) {
      echo $response;
      exit();
    }

    return $data['access_token'];
  }


  /**
   * Send request to PSP
   * @param  string $method
   * @param  string $resource
   * @param  array  $request
   * @return array
   */
  private function send($method,$resource,$request = []){

    $endpoint = $this->baseUrl.$resource;

    $headers = [
      'Cache-Control: no-cache',
      'Content-type: application/json',
      'Authorization: Bearer '.$this->getAccessToken()
    ];

    $curl = curl_init();
    curl_setopt_array($curl,[

      CURLOPT_URL => $endpoint,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => $headers,
    ]);



    switch ($method) {
      case 'POST':
      case 'PUT':
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($request));
        break;
    }

    $response = curl_exec($curl);

    curl_close($curl);

    return json_decode($response,true);
  }

  /**
   * Create charge
   * @param  array $request
   * @return array
   */
  public function createCob($request){
    return $this->send('POST','/pix/api/cob/', $request);
  }

  /**
   * Get charge
   * @param  string $txid
   * @return array
   */
  public function consultCob($txid){
    return $this->send('GET','/pix/api/cob/' . $txid);
  }

  /**
   * Refund charge
   * @param  string $txid
   * @return array
   */
  public function refundCob($e2eid, $id){
    return $this->send('GET','/pix/api/pix/' . $e2eid . '/devolucao/' . $txid);
  }

}
