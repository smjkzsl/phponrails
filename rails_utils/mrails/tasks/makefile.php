<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

mrails_task('T,tasks', array(
'description' => 'Shows available tasks',
'run' => array(
'php' => <<<PHP
    \$Mrails->displayAvailableTasks();
PHP
)
));

mrails_task('test:case', array(
    'description' => 'Runs a single test case file',
    //'autocompletion' => 'ENVIRONMENT=production'
));

mrails_task('test:units', array(
    'description' => 'Run all unit tests'
    //'autocompletion' => 'ENVIRONMENT=production'
));

mrails_task('test:functionals', array(
    'description' => 'Run all functional tests'
    //'autocompletion' => 'ENVIRONMENT=production'
));

mrails_task('release:generate', array(
    'description' => 'Generates a new release'
));


mrails_task('db:sessions:create', array(
    'description' => 'Creates the database table for storing sessions'
));


