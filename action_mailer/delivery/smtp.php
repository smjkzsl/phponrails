<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkSmtpDelivery
{
    public function deliver(&$Mailer, $settings = array()) {
        $Message = $Mailer->Message;

        $SmtpClient = Mail::factory('smtp', $settings);

        include_once 'Net/SMTP.php';

        if (!($smtp = new Net_SMTP($SmtpClient->host, $SmtpClient->port, $SmtpClient->localhost))) {
            return PEAR::raiseError('unable to instantiate Net_SMTP object');
        }

        if ($SmtpClient->debug) {
            $smtp->setDebug(true);
        }
        if(isset($settings['auth']) && $settings['auth'] == 1 && !function_exists('openssl_verify')){
            trigger_error('Can\'t authenticate on '.$SmtpClient->host.':'.$SmtpClient->port.'. PHP does not have OpenSSL support enabled', E_USER_ERROR);
            return;
        }

        if (PEAR::isError($smtp->connect($SmtpClient->timeout))) {
            trigger_error('unable to connect to smtp server '.$SmtpClient->host.':'.$SmtpClient->port.'. SMTP settings can be fount at config/mailer.yml.', E_USER_NOTICE);
            return false;
        }

        if ($SmtpClient->auth) {
            $method = is_string($SmtpClient->auth) ? $SmtpClient->auth : '';

            if (PEAR::isError($smtp->auth($SmtpClient->username, $SmtpClient->password, $method))) {
                trigger_error('unable to authenticate to smtp server. SMTP settings can be fount at config/mailer.yml.', E_USER_ERROR);
            }
        }

        $from = is_array($Message->from) ? array_shift(array_values($Message->from)) : $Message->from;

        if (PEAR::isError($smtp->mailFrom($from))) {
            trigger_error('unable to set sender to [' . $from . ']', E_USER_ERROR);
        }

        $recipients = $SmtpClient->parseRecipients($Message->getRecipients());

        if (PEAR::isError($recipients)) {
            return $recipients;
        }

        foreach ($recipients as $recipient) {
            if (PEAR::isError($res = $smtp->rcptTo($recipient))) {
                return PEAR::raiseError('unable to add recipient [' .
                $recipient . ']: ' . $res->getMessage());
            }
        }

        if (PEAR::isError($smtp->data($Mailer->getRawMessage()))) {
            return PEAR::raiseError('unable to send data');
        }

        $smtp->disconnect();
    }
}

