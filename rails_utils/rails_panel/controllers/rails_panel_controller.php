<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class RailsPanelController extends AkActionController {
    public $application_name = APP_NAME;
    
    public function __construct(){
       // if(!(DEV_MODE && AkRequest::isLocal())){
            //throw new ForbiddenActionException('You can only access the Rails Panel from localhost and when running development environment.');
        //}
    }

}

