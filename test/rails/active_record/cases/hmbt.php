<?php
require_once(dirname(__FILE__).'/../config.php');

class HasAndBelongsToMany_TestCase1 extends ActiveRecordUnitTest
{
    public function test_start() {
        $this->_cleanUpAutomaticGeneratedFiles();

        $Installer = new AkInstaller();
        $this->installAndIncludeModels(array('Post','Picture', 'Thumbnail','Panorama', 'Property', 'PropertyType', 'User', 'Tagging', 'Tag'));
    }
 
    public function test_getAssociatedModelInstance_should_return_a_single_instance()  // bug-fix
    {
        $this->assertReference($this->Post->tag->getAssociatedModelInstance(),$this->Post->tag->getAssociatedModelInstance());
    }
    public function test_for_has_and_belongs_to_many() {
        $Property = new Property(array('description'=>'属性１')); //创建一个属性
        $this->assertEqual($Property->property_type->getType(), 'hasAndBelongsToMany');
        $this->assertTrue(is_array($Property->property_types) && count($Property->property_types) === 0);

        $Property->property_type->load();
        $this->assertEqual($Property->property_type->count(), 0);

        $Chalet = new PropertyType(array('description'=>'属性1->1'));

        $Property->property_type->add($Chalet);
        $this->assertEqual($Property->property_type->count(), 1);

        $this->assertReference($Property->property_types[0], $Chalet);
	$Property->property_type->add($Chalet);
        $this->assertEqual($Property->property_type->count(), 1);

        $Condo = new PropertyType(array('description'=>'Condominium'));
        $Property->property_type->add($Condo);

        $this->assertEqual($Property->property_type->count(), 2);


    }
    public function test_clean() {
        $this->_cleanUpAutomaticGeneratedFiles();
    }

    public function _cleanUpAutomaticGeneratedFiles() {
        foreach (explode(',', 'post_tag,post_user,friend_friend,aa_ee,bb_cc,dd_ee,group_user') as $file){
            @AkFileSystem::file_delete(AkConfig::getDir('models').DS.$file.'.php');
        }
    }

}
ak_test_case('HasAndBelongsToMany_TestCase1');