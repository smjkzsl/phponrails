<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkError{
    static function handle(Exception $e){
        if(WEB_REQUEST){
            echo "<pre>";
        }
        throw $e;
        if(WEB_REQUEST){
            echo "</pre>";
        }
    }
}