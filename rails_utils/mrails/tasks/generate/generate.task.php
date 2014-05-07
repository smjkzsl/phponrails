<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

$argv = $GLOBALS['argv'];
array_shift($argv);
array_shift($argv);
$command = join(' ',$argv);

$Generator = new RailsGenerator();
$Generator->runCommand($command);

echo "\n";


