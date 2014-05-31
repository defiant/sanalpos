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

class SanalPosGaranti extends SanalPosBase implements SanalPosInterface{
    protected $mode = 'PROD';
    protected $transactionMode;

    protected  $xml = '';

    protected $server     = 'https://sanalposprov.garanti.com.tr/VPServlet';
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



    public function send(){
        $server = $this->mode == 'TEST' ? $server = $this->testServer : $this->server;
        $this->setXml();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . $this->xml);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function setXml(){
        $ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : $_SERVER['SERVER_ADDR'];

        $xml= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <GVPSRequest>
        <Mode>{$this->mode}</Mode>
        <Version>v0.01</Version>
        <Terminal>
            <ProvUserID>{$this->provisionUser}</ProvUserID>
            <HashData>{$this->createHash()}</HashData>
            <UserID>{$this->provisionUser}</UserID>
            <ID>{$this->terminalId}</ID>
            <MerchantID>{$this->merchantId}</MerchantID>
        </Terminal>
        <Customer>
            <IPAddress>{$ip}</IPAddress>
            <EmailAddress>{$this->order['email']}</EmailAddress>
            <Description></Description>
        </Customer>
        <Card>
            <Number>{$this->card['number']}</Number>
            <ExpireDate>{$this->card['month']}{$this->card['year']}</ExpireDate>
            <CVV2>{$this->card['cvv']}</CVV2>
        </Card>
        <Order>
            <OrderID>{$this->order['orderId']}</OrderID>
            <GroupID></GroupID>
        </Order>
        <Transaction>
            <Type>sales</Type>
            <InstallmentCnt>{$this->order['taksit']}</InstallmentCnt>
            <Amount>{$this->order['total']}</Amount>
            <CurrencyCode>949</CurrencyCode>
            <CardholderPresentCode>0</CardholderPresentCode>
            <MotoInd>N</MotoInd>
        </Transaction>
        </GVPSRequest>";

        $this->xml = $xml;
        return $this->xml;
    }

    public function getXml(){
        return $this->xml;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this->mode;
    }

    public function getTransactionMode()
    {
        return $this->getTransactionMode;
    }

    public function setTransactionMode($mode = 'sales')
    {
        $this->transactionMode = $mode;
        return $this->transactionMode;
    }

    protected function createHash(){
        $SecurityData = strtoupper(sha1($this->password . str_pad($this->terminalId, 9, '0', STR_PAD_LEFT)));
        $HashData = strtoupper(sha1($this->order['orderId'] . $this->terminalId . $this->card['number'] . $this->order['total'] . $SecurityData));
        return $HashData;
    }
}