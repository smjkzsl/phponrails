<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

$total_args = count($GLOBALS['argv']);

if($total_args > 5){
    return;
}

$Generator = new RailsGenerator();
echo "--help\n";
if($total_args <= 4){
    echo join("\n", $Generator->getAvailableGenerators());
}
