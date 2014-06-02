<?php
/**
 * Created by Sinan Taga.
 * User: sinan
 * Date: 31/05/14
 * Time: 21:39
 */
namespace SanalPos\Est;

use SanalPos\SanalPosBase;
use SanalPos\SanalPosInterface;
use DOMDocument;

class SanalPosEst extends SanalPosBase implements SanalPosInterface{
    protected $xml;
    protected $clientId;
    protected $username;
    protected $password;

    protected $banks = [
        'isbank'    => 'spos.isbank.com.tr',
        'akbank'    => 'www.sanalakpos.com',
        'finansbank'=> 'www.fbwebpos.com',
        'halkbank'  => 'sanalpos.halkbank.com.tr',
        'anadolubank'=>'anadolusanalpos.est.com.tr'
    ];

    protected $testServer = 'testsanalpos.est.com.tr';

    public function __construct($bank, $clientId, $username, $password)
    {
        if(!array_key_exists($bank, $this->banks)){
            throw new \Exception('Bilinmeyen Banka');
        }else{
            $this->server = $this->banks[$bank];
        }
        $this->clientId = $clientId;
        $this->username   = $username;
        $this->password   = $password;
    }

    public function getServer()
    {
        $this->server = $this->mode == 'TEST' ? 'https://'.$this->testServer.'/servlet/cc5ApiServer' : 'https://'.$this->server.'/servlet/cc5ApiServer';
        return $this->server;
    }

    public function pay($pre = false)
    {
        $mode = $pre ? 'PreAuth' : 'Auth';
        // Prepare XML CC5Request request
        $dom = new DOMDocument('1.0', 'ISO-8859-9');
        $root = $dom->createElement('CC5Request');

        // First level elements
        $x['name']      = $dom->createElement('Name', $this->username);
        $x['$password'] = $dom->createElement('Password', $this->password);
        $x['clientId']  = $dom->createElement('ClientId', $this->clientId);
        $x['mode']      = $dom->createElement('Mode', 'P');
        $x['orderId']   = $dom->createElement('OrderId', $this->order['orderId']);
        $x['type']      = $dom->createElement('Type', $mode);
        $x['currency']  = $dom->createElement('Currency', $this->getCurreny());
        $x['transId']   = $dom->createElement('TransId', '');
        $x['taksit']    = $dom->createElement('Taksit', $this->order['taksit']);
        $x['email']     = $dom->createElement('Email', $this->order['email']);
        $x['number']    = $dom->createElement('Number', $this->card['number']);
        $x['expires']   = $dom->createElement('Expires', $this->card['month'].$this->card['year']);
        $x['cvv']       = $dom->createElement('Cvv2Val', $this->card['cvv']);
        $x['ip']        = $dom->createElement('IPAddress', '192.168.1.1');// $_SERVER['REMOTE_ADDR']);
        $x['total']     = $dom->createElement('Total', $this->order['total']);
        /*$x['billTo']    = $dom->createElement('BillTo');
        $x['shipTo']    = $dom->createElement('ShipTo');*/

        foreach($x as $node)
        {
            $root->appendChild($node);
        }
        $dom->appendChild($root);

        $this->xml = $dom->saveXML();
        return $this->send();
    }

    public function postAuth($orderId)
    {
        $dom = new DOMDocument('1.0', 'ISO-8859-9');
        $root = $dom->createElement('CC5Request');

        $x['name']      = $dom->createElement('Name', $this->username);
        $x['$password'] = $dom->createElement('Password', $this->password);
        $x['clientId']  = $dom->createElement('ClientId', $this->clientId);
        $x['type']      = $dom->createElement('Type', 'PostAuth');
        $x['orderId']   = $dom->createElement('OrderId', $orderId);

        foreach($x as $node)
        {
            $root->appendChild($node);
        }
        $dom->appendChild($root);

        $this->xml = $dom->saveXML();
        return $this->send();
    }

    public function cancel($orderId)
    {
        $dom = new DOMDocument('1.0', 'ISO-8859-9');
        $root = $dom->createElement('CC5Request');

        $x['name']      = $dom->createElement('Name', $this->username);
        $x['$password'] = $dom->createElement('Password', $this->password);
        $x['clientId']  = $dom->createElement('ClientId', $this->clientId);
        $x['type']      = $dom->createElement('Type', 'Void');
        $x['orderId']   = $dom->createElement('OrderId', $orderId);

        foreach($x as $node)
        {
            $root->appendChild($node);
        }
        $dom->appendChild($root);

        $this->xml = $dom->saveXML();
        return $this->send();
    }

    public function refund($orderId, $amount = NULL)
    {
        $dom = new DOMDocument('1.0', 'ISO-8859-9');
        $root = $dom->createElement('CC5Request');

        $x['name']      = $dom->createElement('Name', $this->username);
        $x['$password'] = $dom->createElement('Password', $this->password);
        $x['clientId']  = $dom->createElement('ClientId', $this->clientId);
        $x['type']      = $dom->createElement('Type', 'Credit');
        if($amount){
            $x['amount'] = $dom->createElement('Total', $amount);
        }
        $x['orderId']   = $dom->createElement('OrderId', $orderId);

        foreach($x as $node)
        {
            $root->appendChild($node);
        }
        $dom->appendChild($root);

        $this->xml = $dom->saveXML();
        return $this->send();
    }

    public function send()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->getServer());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "data=" . $this->xml);
        curl_setopt ($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type" => "application/x-www-form-urlencoded"));
        $response= curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}