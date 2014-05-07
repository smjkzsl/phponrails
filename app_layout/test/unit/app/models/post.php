<?php
// To run this test calling ./script/test unit/app/models/post// More about testing at http://www.rails.org/wiki/testing-guide

class PostTestCase extends AkUnitTest
{
    function test_setup() {
        $this->installAndIncludeModels('Post');
    }
    
    function test_Post() {
        $this->assertTrue(false,'Unit test for Post not implemented');
    }
}

?>
