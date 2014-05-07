<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkPhpMailDelivery
{
    public function deliver(&$Mailer, $settings = array()) {
        $Message = $Mailer->Message;
        $to = $Message->getTo();
        $subject = $Message->getSubject();

        list($header, $body) = $Message->getRawHeadersAndBody();

        $header = preg_replace('/(To|Subject): [^\r]+\r\n/', '', $header);
        return mail($to, $subject, $body, $header);
    }
}

