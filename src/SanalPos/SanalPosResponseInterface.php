<?php
/**
 * Created by Sinan Taga.
 * User: sinan
 * Date: 31/05/14
 * Time: 21:08
 */

namespace SanalPos;

interface SanalPosResponseInterface {

    public function success();
    public function errors();
    public function response();

} 