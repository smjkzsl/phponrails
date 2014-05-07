<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkTestDelivery
{
    public function deliver(&$Mailer, $settings = array()) {
        $encoded_message = $Mailer->getRawMessage();
        $settings['ActionMailer']->deliveries[] = $encoded_message;
        if(!PRODUCTION_MODE){
            $Logger = Ak::getLogger('mail');
            $Logger->message($encoded_message);
        }
        if(TEST_MODE){
            Ak::setStaticVar('last_mail_delivered', $encoded_message);
        }
    }
}


