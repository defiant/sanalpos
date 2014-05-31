<?php
/**
 * Created by Sinan Taga.
 * User: sinan
 * Date: 01/06/14
 * Time: 00:38
 */
namespace SanalPos\Est;

use SanalPos\SanalPosResponseInterface;
use SimpleXMLElement;

class SanalPosResponseEst implements SanalPosResponseInterface{
    protected $response;
    protected $xml;

    public function __construct($response)
    {
        $this->response = $response;
        $this->xml      = new SimpleXMLElement($response);
    }

    public function success()
    {
        // if response code === '00'
        // then the transaction is approved
        // if code is anything other than '00' that means there's an error
        return (string) $this->xml->CC5Response->ProcReturnCode === '00';
    }

    public function errors()
    {
        if ($this->success()) {
            return [];
        }

        return $this->xml->CC5Response->ErrMsg;
    }

    public function response()
    {
        return $this->response;
    }
} 