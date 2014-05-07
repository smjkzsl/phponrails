<?php

require_once(dirname(__FILE__).'/../config.php');

class DocumentCrud_TestCase extends ActiveDocumentUnitTest
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

    public function test_should_get_collection() {
        $this->assertEqual($this->WebPage->getCollectionName(), 'web_pages');
        $this->assertEqual($this->WebPage->getTableName(),      'web_pages');
    }

    public function test_should_create_document() {
        $attributes = array(
        'title' => 'Rails.org',
        'body'  =>  'Rails PHP framework...',
        'keywords' => array('one', 'two')
        );
        $Rails = $this->WebPage->create($attributes);
        $this->assertFalse($Rails->isNewRecord());
        $this->assertEqual($Rails->title, 'Rails.org');
        $this->assertEqual(Ak::pick('title,body,keywords', $Rails->getAttributes()), $attributes);
        $this->assertNotNull($Rails->getId());
    }

    public function test_should_not_duplicate_documents() {
        $attributes = array(
        'title' => 'Doc 1',
        );
        $Rails             = $this->WebPage->create($attributes);
        $RailsDuplicated   = $this->WebPage->create($attributes);
        
        $this->assertNotEqual($RailsDuplicated->getId(), $Rails->getId());
        
        $attributes['body'] = 'Rails PHP framework...';
        $RailsDuplicated   = $this->WebPage->create($attributes);
        $this->assertNotEqual($RailsDuplicated->getId(), $Rails->getId());
    }

    public function test_should_set_and_get_attributes() {
        $this->WebPage->title = 'Rails.org';
        $this->WebPage->body  =  'Rails PHP framework...';
        $this->WebPage->keywords = array('one', 'two');
        $this->assertNull($this->WebPage->getId());
        $this->assertTrue($this->WebPage->isNewRecord());
        $this->WebPage->save();
        $this->assertFalse($this->WebPage->isNewRecord());
        $this->assertEqual($this->WebPage->title, 'Rails.org');
        $this->assertEqual(Ak::pick('body', $this->WebPage->getAttributes()), array('body' => 'Rails PHP framework...'));
        $this->assertEqual($this->WebPage->getAttribute('body'), 'Rails PHP framework...');
        $this->assertNotNull($this->WebPage->getId());
    }


    public function test_should_update_records() {
        $this->WebPage->body  =  'Rails PHP framework...';
        $this->WebPage->save();
        $id = $this->WebPage->getId();
        $this->assertEqual($this->WebPage->body, 'Rails PHP framework...');
        $this->WebPage->body  =  'Rails';
        $this->WebPage->save();
        $this->assertEqual($this->WebPage->get('body'), 'Rails');
        $this->assertEqual($id, $this->WebPage->getId());
    }

    public function test_should_record_timestamps() {
        $this->WebPage->body  =  'Rails PHP framework...';
        $this->WebPage->save();
        $created_at = Ak::getDate();
        $this->assertEqual($this->WebPage->created_at, $created_at);
        sleep(1);
        $this->WebPage->save();
        $this->assertEqual($this->WebPage->created_at, $created_at);
        $this->assertEqual($this->WebPage->updated_at, Ak::getDate());
    }

    public function test_should_instantiate_record_by_primary_key() {
        $this->WebPage->body  =  'Rails PHP framework...';
        $this->WebPage->save();
        $WebPage = new WebPage($this->WebPage->getId());
        $this->assertFalse($WebPage->isNewRecord());
        $this->assertEqual($WebPage->body, $this->WebPage->body);
        $this->assertEqual($WebPage->getId(), $this->WebPage->getId());
    }

    public function test_should_set_default_attributes_on_constructor() {
        $WebPage = new WebPage(array('body' => 'Rails PHP framework'));
        $WebPage->save();
        $WebPage2 = new WebPage($WebPage->getId());
        $this->assertEqual($WebPage->body, $WebPage2->body);
    }

    public function test_should_destroy_record() {
        $WebPage = new WebPage(array('body' => 'Rails PHP framework'));
        $WebPage->save();
        $WebPage2 = new WebPage($WebPage->getId());
        $this->assertEqual($WebPage->body, $WebPage2->body);
        $WebPage2->destroy();
        $WebPage2 = new WebPage($WebPage->getId());
        $this->assertTrue($WebPage2->isNewRecord());
        $this->assertFalse($WebPage->find($WebPage->getId()));
    }

}

ak_test_case('DocumentCrud_TestCase');
