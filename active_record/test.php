<?php

require_once __DIR__.'/../autoload.php';
class Post extends AkActiveRecord{

}

$a=new Post();
$a->name='����';
$a->save();
var_dump($a->findAll()[2]->getId());
