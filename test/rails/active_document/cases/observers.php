<?php

require_once(dirname(__FILE__).'/../config.php');

class DocumentObservers_TestCase extends ActiveDocumentUnitTest
{
    public function setup() {
        $this->db = new AkOdbAdapter();
        $this->db->connect(array('type' => 'mongo_db', 'database' => 'rails_testing'));
        $this->WebPage = new WebPage();
        $this->WebPage->setAdapter($this->db);
        $this->WebPage->setAdapter($this->db);

        $Auditor = new WebPageAuditor();
        $Auditor->observe($this->WebPage);
    }

    public function tearDown() {
        $this->db->dropDatabase();
        $this->db->disconnect();
    }
    
    public function test_should_issue_observer_callbacks_in_the_right_order() {
        // Creation callbacks
        $Rails = $this->WebPage->create(array('title' => 'Rails PHP framework'));

        $this->assertEqual($Rails->callbacks, array (
        'beforeValidation',
        'WebPageAuditor::beforeValidation',
        'beforeValidationOnCreate',
        'WebPageAuditor::beforeValidationOnCreate',
        'afterValidation',
        'WebPageAuditor::afterValidation',
        'afterValidationOnCreate',
        'WebPageAuditor::afterValidationOnCreate',
        'beforeSave',
        'WebPageAuditor::beforeSave',
        'beforeCreate',
        'WebPageAuditor::beforeCreate',
        'afterCreate',
        'WebPageAuditor::afterCreate',
        'afterSave',
        'WebPageAuditor::afterSave',
        ));

        $this->assertFalse($Rails->isNewRecord());

        // Instantiating callbacks
        $Rails->callbacks = array();
        $Rails->reload();

        $this->assertEqual($Rails->callbacks, array('afterInstantiate', 'WebPageAuditor::afterInstantiate'));

        // Update callbacks
        $Rails->callbacks = array();
        $Rails->save();


        $this->assertEqual($Rails->callbacks, array (
        'beforeValidation',
        'WebPageAuditor::beforeValidation',
        'beforeValidationOnUpdate',
        'WebPageAuditor::beforeValidationOnUpdate',
        'afterValidation',
        'WebPageAuditor::afterValidation',
        'afterValidationOnUpdate',
        'WebPageAuditor::afterValidationOnUpdate',
        'beforeSave',
        'WebPageAuditor::beforeSave',
        'beforeUpdate',
        'WebPageAuditor::beforeUpdate',
        'afterUpdate',
        'WebPageAuditor::afterUpdate',
        'afterSave',
        'WebPageAuditor::afterSave',
        ));

        // Destroy callbacks
        $Rails->callbacks = array();
        $Rails->destroy();

        $this->assertEqual($Rails->callbacks, array (
        'beforeDestroy',
        'WebPageAuditor::beforeDestroy',
        'afterDestroy',
        'WebPageAuditor::afterDestroy'
        ));
    }

    public function test_should_halt_operation_if_observer_callback_returns_false() {
        $Rails = $this->WebPage->create(array('title' => 'Rails PHP framework'));
        $Rails->callbacks = array();
        $Rails->halt_on_callback = 'WebPageAuditor::afterValidation';
        $this->assertFalse($Rails->save());
        $this->assertEqual($Rails->callbacks, array (
        'beforeValidation',
        'WebPageAuditor::beforeValidation',
        'beforeValidationOnUpdate',
        'WebPageAuditor::beforeValidationOnUpdate',
        'afterValidation',
        'WebPageAuditor::afterValidation',
        ));

        // Destroy callbacks
        $Rails->callbacks = array();
        $Rails->halt_on_callback = 'WebPageAuditor::beforeDestroy';
        $this->assertFalse($Rails->destroy());
        $this->assertEqual($Rails->callbacks, array (
        'beforeDestroy',
        'WebPageAuditor::beforeDestroy',
        ));

        $this->assertTrue($Rails->reload());
        $this->assertFalse($Rails->isNewRecord());

    }
}

ak_test_case('DocumentObservers_TestCase');
