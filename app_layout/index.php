<?php
//项目入口文件

//var_dump($_REQUEST);
include "config/config.php";
$d=new AkDispatcher();
$d->dispatch();
