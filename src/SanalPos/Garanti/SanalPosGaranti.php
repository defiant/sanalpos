<?php
/**
 * Created by Sinan Taga.
 * User: sinan
 * Date: 31/05/14
 * Time: 18:24
 */
namespace SanalPos\Garanti;

use SanalPos\SanalPosBase;
use SanalPos\SanalPosInterface;
use DOMDocument;

class SanalPosGaranti extends SanalPosBase implements SanalPosInterface{
    protected $mode = 'PROD';

    protected  $xml = '';

    protected $prodServer = 'https://sanalposprov.garanti.com.tr/VPServlet';
    protected $testServer = 'https://sanalposprovtest.garanti.com.tr/VPServlet';

    protected $merchantId;
    protected $terminalId;
    protected $userId;
    protected $password;
    protected $provisionUser;

    public function __construct($merchantId, $terminalId, $userId, $password, $provisionUser){
        $this->merchantId = $merchantId;
        $this->terminalId = $terminalId;
        $this->userId     = $userId;
        $this->password   = $password;
        $this->provisionUser = $provisionUser;
    }

    public function getServer()
    {
        $this->server = $this->mode == 'TEST' ? $server = $this->testServer : $this->prodServer;
        return $this->server;
    }

    public function setOrder($orderId, $customerEmail, $total, $taksit = '', $extra = [])
    {
        $this->order['orderId'] = $orderId;
        $this->order['email']   = $customerEmail;
        $this->order['total']   = $total;
        $this->order['taksit']  = $taksit;
        $this->order['extra']   = $extra;
        $this->order['total']   = $this->order['total'] * 100; // garanti 1.00 yerine 100 bekliyor
    }

    public function pay($pre = false)
    {
        $mode = $pre ? 'preauth' : 'sales';

        // prepare Request data
        $x['Transaction']=[
            'Type' => $mode,
            'Amount' => $this->order['total'],
            'CurrencyCode' => $this->getCurreny(),
            'CardholderPresentCode' => 0,
            'MotoInd' => 'N',
            'InstallmentCnt' => $this->order['taksit']
        ];

        $this->setXml($x);
        return $this->send();
    }

    public function postAuth($orderId)
    {
        $this->order['orderId'] = $orderId;

        $x['Transaction']=[
            'Type' => 'postauth',
            'Amount' => $this->order['total'],
            'CurrencyCode' => $this->getCurreny(),
            'CardholderPresentCode' => 0,
            'MotoInd' => 'H',
            'InstallmentCnt' => $this->order['taksit']
        ];

        $this->setXml($x);
        return $this->send();
    }

    public function cancel($orderId)
    {
        $x['Transaction']=[
            'Type' => 'void',
            'Amount' => $this->order['total'],
            'CurrencyCode' => $this->getCurreny(),
            'CardholderPresentCode' => 0,
            'MotoInd' => 'N',
            'InstallmentCnt' => $this->order['taksit']
        ];

        $this->setXml($x);
        return $this->send();
    }

    public function refund($orderId, $amount = NULL)
    {
        $amount = $amount ? $amount*100: $this->order['total'];
        $x['Transaction']=[
            'Type' => 'void',
            'Amount' => $amount,
            'CurrencyCode' => $this->getCurreny(),
            'CardholderPresentCode' => 0,
            'MotoInd' => 'N',
            'InstallmentCnt' => $this->order['taksit']
        ];

        $this->setXml($x);
        return $this->send();
    }

    public function send(){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getServer());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . $this->xml);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function setXml($xmlData){
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElement('GVPSRequest');

        $ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '192.168.1.1';
        $ip = '192.168.1.1'; // for cli testing

        $x['Mode']      = $this->mode;
        $x['Version']   = 'v0.01';
        $x['Terminal'] = [
            'ProvUserID' => $this->provisionUser,
            'HashData' => $this->createHash(),
            'UserID' => $this->provisionUser,
            'ID' => $this->terminalId,
            'MerchantID' => $this->merchantId
        ];
        $x['Customer']  = [
            'IPAddress' => $ip,
            'EmailAddress' => $this->order['email'],
            'Description'  => ''
        ];
        $x['Card']      = [
            'Number' => $this->card['number'],
            'ExpireDate' => $this->card['month'].$this->card['year'],
            'CVV2' => $this->card['cvv']
        ];
        $x['Order']     = [
            'OrderID' => $this->order['orderId'],
            'GroupID' => ''
        ];

        foreach(array_merge($x, $xmlData) as $nodeKey => $nodeValue){
            if(is_array($nodeValue)){
                $node = $dom->createElement($nodeKey);
                $root->appendChild($node);
                foreach($nodeValue as $childKey => $childValue){
                    $textNode = $dom->createTextNode(strval($childValue));
                    $child = $dom->createElement($childKey);
                    $child->appendChild($textNode);
                    $node->appendChild($child);
                }
            }else{
                $textNode = $dom->createTextNode(strval($nodeValue));
                $node = $dom->createElement($nodeKey);
                $node->appendChild($textNode);
                $root->appendChild($node);
            }
        }
        $dom->appendChild($root);

        $this->xml = $dom->saveXML();
        return $this->xml;
    }

    public function getXml(){
        return $this->xml;
    }

    protected function createHash(){
        $SecurityData = strtoupper(sha1($this->password . str_pad($this->terminalId, 9, '0', STR_PAD_LEFT)));
        $HashData = strtoupper(sha1($this->order['orderId'] . $this->terminalId . $this->card['number'] . $this->order['total'] . $SecurityData));
        return $HashData;
    }
}
