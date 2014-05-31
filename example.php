<?php
/**
 * Created by Sinan Taga.
 * User: sinan
 * Date: 31/05/14
 * Time: 19:47
 */

require_once __DIR__ . '/vendor/autoload.php';

/*
 * Garanti Sanal Pos Örneği
 * Test Bilgileri ile test sunucuna gönderiliyor
$pos = new \SanalPos\Garanti\SanalPosGaranti('7000679', '30691297', 'PROVAUT', '123qweASD', 'PROVAUT');
$pos->setCard('4282209027132016', '05', '15', '232');
$pos->setOrder('deneme23', 'test@test.com', '1');
$pos->setMode('TEST');
$result = new \SanalPos\Garanti\SanalPosReponseGaranti($pos->pay());

if($result->succes()){
    // transaction successful
}else{
    //transaction failed
    var_dump($result->errors());
}
*/

$est = new \SanalPos\Est\SanalPosEst('isbank', '700100000', 'ISBANKAPI', 'ISBANK07');
$est->setCard('4508034508034509', '12', '16', '000');
$est->setOrder('deneme123', 'test@test.com', '1');
$est->setMode('TEST');

$result = new \SanalPos\Est\SanalPosResponseEst($est->pay());
var_dump($result);