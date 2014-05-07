<?php

require_once(dirname(__FILE__).'/../config.php');

class DocumentCallbacks_TestCase extends ActiveDocumentUnitTest
{
    public function setup() {
        $this->db = new AkOdbAdapter();
        $this->db->connect(array('type' => 'mongo_db', 'database' => 'rails_testing'));
        $this->WebPage = new WebPage();
        $this->WebPage->setAdapter($this->db);
    }

    public function tearDown() {
        $this->db->dropDatabase();
        $this->db->disconnect();
    }

    public function test_should_issue_callbacks_in_the_right_order() {
        // Creation callbacks
        $Rails = $this->WebPage->create(array('title' => 'Rails PHP framework'));
        $this->assertEqual($Rails->callbacks, array (
        'beforeValidation',
        'beforeValidationOnCreate',
        'afterValidation',
        'afterValidationOnCreate',
        'beforeSave',
        'beforeCreate',
        'afterCreate',
        'afterSave'
        ));

        $this->assertFalse($Rails->isNewRecord());

        // Instantiating callbacks
        $Rails->callbacks = array();
        $Rails->reload();
        $this->assertEqual($Rails->callbacks, array('afterInstantiate'));

        // Update callbacks
        $Rails->callbacks = array();
        $Rails->save();
        $this->assertEqual($Rails->callbacks, array (
        'beforeValidation',
        'beforeValidationOnUpdate',
        'afterValidation',
        'afterValidationOnUpdate',
        'beforeSave',
        'beforeUpdate',
        'afterUpdate',
        'afterSave'
        ));

        // Destroy callbacks
        $Rails->callbacks = array();
        $Rails->destroy();
        $this->assertEqual($Rails->callbacks, array (
        'beforeDestroy',
        'afterDestroy'
        ));
    }

    public function test_should_halt_operation_if_callback_returns_false() {
        $Rails = $this->WebPage->create(array('title' => 'Rails PHP framework'));
        $Rails->callbacks = array();
        $Rails->halt_on_callback = 'afterValidation';
        $this->assertFalse($Rails->save());
        $this->assertEqual($Rails->callbacks, array (
        'beforeValidation',
        'beforeValidationOnUpdate',
        'afterValidation',
        ));

        // Destroy callbacks
        $Rails->callbacks = array();
        $Rails->halt_on_callback = 'beforeDestroy';
        $this->assertFalse($Rails->destroy());
        $this->assertEqual($Rails->callbacks, array (
        'beforeDestroy',
        ));

        $this->assertTrue($Rails->reload());
        $this->assertFalse($Rails->isNewRecord());

    }
}

ak_test_case('DocumentCallbacks_TestCase');
