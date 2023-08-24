<?php

namespace Hit\Pix;

class Payload{

  /**
  * Payload IDs from Pix
  * @var string
  */
  const ID_PAYLOAD_FORMAT_INDICATOR = '00';
  const ID_POINT_OF_INITIATION_METHOD = '01';
  const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
  const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
  const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
  const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
  const ID_MERCHANT_ACCOUNT_INFORMATION_URL = '25';
  const ID_MERCHANT_CATEGORY_CODE = '52';
  const ID_TRANSACTION_CURRENCY = '53';
  const ID_TRANSACTION_AMOUNT = '54';
  const ID_COUNTRY_CODE = '58';
  const ID_MERCHANT_NAME = '59';
  const ID_MERCHANT_CITY = '60';
  const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
  const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
  const ID_CRC16 = '63';

  /**
   * PIX Key
   * @var string
   */
  private $pixKey;

  /**
   * Payment Description
   * @var string
   */
  private $description;

  /**
   * Account Name
   * @var string
   */
  private $merchantName;

  /**
   * Account City
   * @var string
   */
  private $merchantCity;

  /**
   * Transaction ID
   * @var string
   */
  private $txid;

  /**
   * Amount
   * @var string
   */
  private $amount;

  /**
   * URL dynamic payload
   * @var string
   */
  private $url;

  /**
   * Defines pix key
   * @param string $pixKey
   */
  public function setPixKey($pixKey){
    $this->pixKey = $pixKey;
    return $this;
  }

  /**
   * Set URL
   * @param string $url
   */
  public function setUrl($url){
    $this->url = $url;
    return $this;
  }

  /**
   * Set description
   * @param string $description
   */
  public function setDescription($description){
    $this->description = $description;
    return $this;
  }

  /**
   * Set TXID
   * @param string $txid
   */
  public function setTxid($txid){
    $this->txid = $txid;
    return $this;
  }

  /**
   * Set merchant name
   * @param string $merchantName
   */
  public function setMerchantName($merchantName){
    $this->merchantName = $merchantName;
    return $this;
  }

  /**
   * Set merchant city
   * @param string $merchantCity
   */
  public function setMerchantCity($merchantCity){
    $this->merchantCity = $merchantCity;
    return $this;
  }

  /**
   * Set charge value
   * @param float $amount
   */
  public function setAmount($amount){
    $this->amount = (string)number_format($amount,2,'.','');
    return $this;
  }

  /**
   * Get object payload
   * @param  string $id
   * @param  string $value
   * @return string $id.$size.$value
   */
  private function getValue($id,$value){
    $size = str_pad(mb_strlen($value),2,'0',STR_PAD_LEFT);
    return $id.$size.$value;
  }

  /**
   * Get account info
   * @return string
   */
  private function getMerchantAccountInformation(){

    $gui = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI,'br.gov.bcb.pix');

    $key = strlen($this->pixKey) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY,$this->pixKey) : '';

    $description = strlen($this->description) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION,$this->description) : '';

    $url = strlen($this->url) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_URL,preg_replace('/^https?\:\/\//','',$this->url)) : '';

    return $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION,$gui.$key.$description.$url);
  }

  /**
   * Get additional fields
   * @return string
   */
  private function getAdditionalDataFieldTemplate(){

    $txid = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID,$this->txid);
    return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE,$txid);
  }

  /**
   * Get payload
   * @return string
   */
  public function getPayload(){

    $payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR,'01').
               $this->getValue(self::ID_POINT_OF_INITIATION_METHOD,'12').
               $this->getMerchantAccountInformation().
               $this->getValue(self::ID_MERCHANT_CATEGORY_CODE,'0000').
               $this->getValue(self::ID_TRANSACTION_CURRENCY,'986').
               $this->getValue(self::ID_TRANSACTION_AMOUNT,$this->amount).
               $this->getValue(self::ID_COUNTRY_CODE,'BR').
               $this->getValue(self::ID_MERCHANT_NAME,$this->merchantName).
               $this->getValue(self::ID_MERCHANT_CITY,$this->merchantCity).
               $this->getAdditionalDataFieldTemplate();

    return $payload.$this->getCRC16($payload);
  }

  /**
   * Calculating the validation hash value of the pix code
   * @return string
   */
  private function getCRC16($payload) {

      $payload .= self::ID_CRC16.'04';

      $polinomio = 0x1021;
      $resultado = 0xFFFF;

      if (($length = strlen($payload)) > 0) {
          for ($offset = 0; $offset < $length; $offset++) {
              $resultado ^= (ord($payload[$offset]) << 8);
              for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                  if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                  $resultado &= 0xFFFF;
              }
          }
      }
      return self::ID_CRC16.'04'.strtoupper(dechex($resultado));
  }

}
