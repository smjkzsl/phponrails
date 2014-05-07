<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

$doc_db_file = DOCS_DIR.DS.'rails'.DS.'doc.sqlite';
$installed = file_exists($doc_db_file);
//~ $db = Ak::db('sqlite://'.urlencode($doc_db_file).'/?persist');
$db =&AkDbAdapter::getInstance();
AkConfig::rebaseApp(PLUGINS_DIR.DS.'doc_builder');

if(!$installed){
    $DocInstaller = new DocInstaller($db);
    $DocInstaller->install();
}


$SourceAnalyzer = new SourceAnalyzer();
$SourceAnalyzer->db = $db;
$SourceAnalyzer->storeFilesForIndexing(ACTION_MAILER_DIR);
$SourceAnalyzer->indexFiles();
//unlink($doc_db_file);
return;

$dir_iterator = new RecursiveDirectoryIterator(ACTION_MAILER_DIR);
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);


foreach ($iterator as $file) {
    if($file->isFile()){
        echo $file, "\n";
        $original_file = file_get_contents($file);
        $Reflection = new AkReflectionFile($file);
        foreach ($Reflection->getClasses() as $Class) {
            echo  'class: '.$Class->getName()."\n";

            $defined_methods = array(
            'public' => array(),
            'protected' => array(),
            'private' => array(),
            );

            foreach ($Class->getMethods() as $Method){
                $name = $Method->getName();
                $visibility = $Method->getVisibility();
                if($visibility == 'public' && $name[0] == '_' && !in_array($name, array('__construct', '__destruct', '__call', '__get', '__set'))){
                    $visibility = 'protected';
                    $content =  $Method->toString(4, null, array('visibility' => 'protected'));
                }else{
                    if($name[0] != '_' && $visibility != 'public'){
                        $name = ltrim($name, '_');
                        $content = $Method->toString(4, $name);
                    }else{
                        $content = $Method->toString(4);
                    }
                }
                $defined_methods[$visibility][$name] = $content;
                /*
                foreach ($Method->getParams() as $Param){
                echo  '       param: '.$Param."\n";
                }
                */
            }

            $sorted_methods = '';

            foreach ($defined_methods as $visibility => $methods){
                foreach ($methods as $method){
                    echo "\n".$method."\n";
                }
            }
        }
    }
}


