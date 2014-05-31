<?php
/**
 * Created by Sinan Taga.
 * User: sinan
 * Date: 31/05/14
 * Time: 19:47
 */

require_once __DIR__ . '/vendor/autoload.php';

$order = [];
$order['orderId'] = 'order123';
$order['email'] =

$pos = new \SanalPos\Garanti\SanalPosGaranti('7000679', '30691297', 'PROVAUT', '123qweASD', 'PROVAUT');

$pos->setCard('4282209027132016', '05', '15', '232');
$pos->setOrder('deneme23', 'test@test.com', '1');
$pos->setMode('TEST');
var_dump($pos->setXml());
var_dump($pos->send());