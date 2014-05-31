<?php
/**
 * Created by Sinan Taga.
 * User: sinan
 * Date: 31/05/14
 * Time: 18:21
 */
namespace SanalPos;

class SanalPosBase {
    protected $mode;

    protected $card  = [];
    protected $order = [];

    public function setCard($number, $expMonth, $expYear, $cvv)
    {
        $this->card['number']   = $number;
        $this->card['month']    = str_pad($expMonth, 2, 0, STR_PAD_LEFT);
        $this->card['year']     = str_pad($expYear, 2, 0, STR_PAD_LEFT);
        $this->card['cvv']      = $cvv;
    }

    public function setOrder($orderId, $customerEmail, $total, $taksit = '', $extra = [])
    {
        $this->order['orderId'] = $orderId;
        $this->order['email']   = $customerEmail;
        $this->order['total']   = $total;
        $this->order['taksit']  = $taksit;
        $this->order['extra']   = $extra;
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

    public function check()
    {

        return true;
    }

    public function checkExpiration()
    {

    }

    public function checkCard()
    {

    }

    public function checkCvv()
    {

    }

    public function checkLuhn()
    {

    }
} 