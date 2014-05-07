<?php

require_once(dirname(__FILE__).'/../config.php');

class TableInheritance_TestCase extends ActiveRecordUnitTest
{
    public function test_start() {
        eval("class Schedule extends ActiveRecord { public \$belongs_to = 'event'; }");
        $this->installAndIncludeModels(array('Event', 'Concert','OpenHouseMeeting'));
    }


    public function test_for_table_inheritance() {
        $Event = new Event(array('description'=>'Uncategorized Event'));
        $this->assertTrue($Event->save());

        $Concert = new Concert(array('description' => 'Madonna at Barcelona'));
        $this->assertTrue($Concert->save());
        $OpenHouseMeeting = new OpenHouseMeeting(array('description' => 'Networking event at Rails'));
        $this->assertTrue($OpenHouseMeeting->save());
        $this->assertEqual($OpenHouseMeeting->get('type'), 'Open house meeting');

        $this->assertTrue($OpenHouseMeeting = $Event->findFirstBy('description','Networking event at Rails'));
        $this->assertEqual($OpenHouseMeeting->get('description'), 'Networking event at Rails');
        $this->assertEqual($OpenHouseMeeting->getType(), 'OpenHouseMeeting');

    }

    public function test_find_should_return_appropriate_models() {
        $Events = $this->Event->find('all');
        $expected = array(1 => 'Event', 2 => 'Concert', 3 => 'OpenHouseMeeting');
        foreach ($Events as $event){
            $this->assertEqual($event->getType(),$expected[$event->getId()]);
        }
    }

    public function test_inheritance_should_lazy_load_right_model() {
        $this->installAndIncludeModels(array('Schedule'=>'id,name,event_id'));
        $this->Schedule->create(array('name'=>'to OpenHouseMeeting','event_id'=>3));
        $this->Schedule->create(array('name'=>'to Event','event_id'=>1));
        $this->Schedule->create(array('name'=>'to Concert','event_id'=>2));

        $scheds = $this->Schedule->find('all');
        foreach ($scheds as $schedule){
            $schedule->event->load();
        }

        $expected = array(1=>'OpenHouseMeeting',2=>'Event',3=>'Concert');
        foreach ($scheds as $schedule){
            $this->assertEqual($schedule->event->getType(),$expected[$schedule->getId()]);
        }
    }
}

ak_test_case('TableInheritance_TestCase');
