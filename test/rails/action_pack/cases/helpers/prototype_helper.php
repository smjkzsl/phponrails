<?php

require_once(dirname(__FILE__).'/../helpers.php');

class PrototypeHelper_TestCase extends HelperUnitTest
{
    public function test_setup()
    {
        $this->controller = new AkActionController();
        $this->controller->Request = new MockAkRequest($this);
        $this->controller->Request->setReturnValue('getParametersFromRequestedUrl',array('controller'=>'test')); 
        $this->controller->controller_name = 'test';
        
        $this->PrototypeHelper = $this->controller->prototype_helper;
    }
    
    /*
     * TODO: Complete test_remote_form_for() and test_form_remote_for()
     */

    public function test_link_to_remote()
    {
        $this->assertEqual(
            $this->PrototypeHelper->link_to_remote('test', array('url' => 'http://www.rails.org', 'update' => 'listing')),
            '<a href="#" onclick="new Ajax.Updater(\'listing\', \'http://www.rails.org\', {}); return false;">test</a>'
        );
        $this->assertEqual(
            $this->PrototypeHelper->link_to_remote('test', array('url' => array('controller' => 'foo', 'action' => 'bar'), 'update' => 'listing')),
            '<a href="#" onclick="new Ajax.Updater(\'listing\', \'/foo/bar\', {}); return false;">test</a>'
        );

        $this->assertEqual(
            $this->PrototypeHelper->link_to_remote('test', array('url' => array('controller' => 'foo', 'action' => 'bar', 'm' => 'ore', 'b' => 'eer'), 'update' => 'listing')),
            '<a href="#" onclick="new Ajax.Updater(\'listing\', \'/foo/bar?m=ore&amp;b=eer\', {}); return false;">test</a>'
        );

    }

    public function test_periodically_call_remote()
    {
        $this->assertEqual(
            $this->PrototypeHelper->periodically_call_remote(array('url' => 'http://localhost', 'update' => 'listing', 'frequency' => 1)),
            "<script type=\"text/javascript\">\n//<![CDATA[\nnew PeriodicalExecuter(function() {new Ajax.Updater('listing', 'http://localhost', {})}, 1)\n//]]>\n</script>"
        );

        $this->assertEqual(
            $this->PrototypeHelper->periodically_call_remote(array('url' => array('controller' => 'foo', 'action' => 'bar'), 'update' => 'listing', 'frequency' => 1)),
            "<script type=\"text/javascript\">\n//<![CDATA[\nnew PeriodicalExecuter(function() {new Ajax.Updater('listing', '/foo/bar', {})}, 1)\n//]]>\n</script>"
        );
    }

    public function test_form_remote_tag()
    {
        $this->assertEqual(
            $this->PrototypeHelper->form_remote_tag(array('url' => array('controller' => 'foo', 'action' => 'bar'), 'update' => 'div_to_update', 'html' => array('id' => 'form_id'))),
            '<form action="/foo/bar" id="form_id" method="post" onsubmit="new Ajax.Updater(\'div_to_update\', \'/foo/bar\', {parameters:Form.serialize(this)}); return false;">'
        );
        $this->assertEqual(
            $this->PrototypeHelper->form_remote_tag(array('url' => 'http://www.rails.org', 'update' => 'div_to_update', 'html' => array('id' => 'form_id'))),
            '<form action="http://www.rails.org" id="form_id" method="post" onsubmit="new Ajax.Updater(\'div_to_update\', \'http://www.rails.org\', {parameters:Form.serialize(this)}); return false;">'
        );
        $this->assertEqual(
            $this->PrototypeHelper->form_remote_tag(array('url' => array('controller' => 'foo', 'action' => 'bar'), 'update' => 'div_to_update', 'html' => array('id' => 'form_id', 'action' => $this->controller->url_helper->url_for(array('controller' => 'some', 'action' => 'place'))))),
            '<form action="/some/place" id="form_id" method="post" onsubmit="new Ajax.Updater(\'div_to_update\', \'/foo/bar\', {parameters:Form.serialize(this)}); return false;">'
        );
    }

    public function test_remote_form_for()
    {
    }

    public function test_form_remote_for()
    {
    }

    public function test_submit_to_remote()
    {
        $this->assertEqual(
            $this->PrototypeHelper->submit_to_remote("More beer!", "1000000", array('update' => 'empty_bottle','url'=>'http://www.example.com/')),
            '<input name="More beer!" onclick="new Ajax.Updater(\'empty_bottle\', \'http://www.example.com/\', {parameters:Form.serialize(this.form)}); return false;" type="button" value="1000000" />'
        );

        $this->assertEqual(
            $this->PrototypeHelper->submit_to_remote("More beer!", "1000000", array('update' => 'empty_bottle', 'url' => array('controller' => 'foo', 'action' => 'bar'))),
            '<input name="More beer!" onclick="new Ajax.Updater(\'empty_bottle\', \'/foo/bar\', {parameters:Form.serialize(this.form)}); return false;" type="button" value="1000000" />'
        );
    }

    public function test_update_element_function()
    {
        $this->assertEqual(
            $this->PrototypeHelper->update_element_function('products', array('position' => 'bottom'), array('content' => '<p>New product!</p>')),
            "new Insertion.Bottom('products','Array');\n"
        );
    }

    public function test_evaluate_remote_response()
    {
        $this->assertEqual($this->PrototypeHelper->evaluate_remote_response(), 'eval(request.responseText)');
    }

    public function test_remote_function()
    {
        $this->assertEqual(
            $this->PrototypeHelper->remote_function(array('url' => 'http://rails.org', 'update' => 'div_update')),
            'new Ajax.Updater(\'div_update\', \'http://rails.org\', {})'
        );
    }

    public function test_observe_field()
    {
        $this->assertEqual(
            $this->PrototypeHelper->observe_field('form_id', array('url' => 'http://rails.org', 'frequency' => 2, 'update' => 'div_update')),
            "<script type=\"text/javascript\">\n//<![CDATA[\nnew Form.Element.Observer('form_id', 2, function(element, value) {new Ajax.Updater('div_update', 'http://rails.org', {parameters:value})})\n//]]>\n</script>"
        );
        $this->assertEqual(
            $this->PrototypeHelper->observe_field('form_id', array('url' => 'http://rails.org', 'update' => 'div_update')),
            "<script type=\"text/javascript\">\n//<![CDATA[\nnew Form.Element.EventObserver('form_id', function(element, value) {new Ajax.Updater('div_update', 'http://rails.org', {parameters:value})})\n//]]>\n</script>"
        );
    }

    public function test_observe_form()
    {
        $this->assertEqual(
            $this->PrototypeHelper->observe_form('form_id', array('url' => 'http://rails.org', 'frequency' => 2, 'update' => 'div_update')),
            "<script type=\"text/javascript\">\n//<![CDATA[\nnew Form.Observer('form_id', 2, function(element, value) {new Ajax.Updater('div_update', 'http://rails.org', {parameters:value})})\n//]]>\n</script>"
        );
        $this->assertEqual(
            $this->PrototypeHelper->observe_form('form_id', array('url' => 'http://rails.org', 'update' => 'div_update')),
            "<script type=\"text/javascript\">\n//<![CDATA[\nnew Form.EventObserver('form_id', function(element, value) {new Ajax.Updater('div_update', 'http://rails.org', {parameters:value})})\n//]]>\n</script>"
        );
    }
}

ak_test_case('PrototypeHelper_TestCase');
