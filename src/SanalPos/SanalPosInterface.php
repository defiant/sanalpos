<?php
/**
 * Created by Sinan Taga.
 * User: sinan
 * Date: 31/05/14
 * Time: 18:08
 */

namespace SanalPos;

interface SanalPosInterface {
    public function setCard($number, $expMonth, $expYear, $cvv);
    public function setOrder($orderId, $customerEmail, $total, $taksit = '', $extra = []);

    public function check();

    public function send();

    public function getMode();
    public function setMode($mode);

    public function setTransactionMode($mode = 'sales');
    public function getTransactionMode();
} 