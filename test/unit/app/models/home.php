<?php
// To run this test calling ./script/test unit/app/models/home// More about testing at http://www.rails.org/wiki/testing-guide

class HomeTestCase extends AkUnitTest
{
    function test_setup() {
        $this->installAndIncludeModels('Home');
    }
    
    function test_Home() {
        $this->assertTrue(false,'Unit test for Home not implemented');
    }
}

?>
