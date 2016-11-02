<?php

use SlimCD\Transact\CloseBatchRequest;

class CloseBatchRequestTest extends PHPUnit_Framework_TestCase
{
    public function testCloseBatch()
    {
        $slimcd = new \SlimCD\Transact\Transact();
        $cbr = new CloseBatchRequest();
        $cbr->username = '1032';
        $cbr->password = '289075';
        $cbr->siteid = 228226448;
        $result = $slimcd->closeBatch($cbr);
        $this->assertInternalType('string', $result->response);
    }
}
