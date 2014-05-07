<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkMailMessage extends AkMailBase
{
    /**
     * Specify the from address for the message.
     */
    public function setFrom($from) {
        $this->from = $from;
    }

    public function getFrom() {
        return $this->_getMessageHeaderFieldFormated(!empty($this->from) ? $this->from : @$this->sender);
    }

    public function getTo() {
        return $this->getRecipients();
    }

    public function getRecipients() {
        return $this->_getMessageHeaderFieldFormated($this->recipients);
    }

    public function getBcc() {
        return $this->_getMessageHeaderFieldFormated($this->bcc);
    }

    public function getCc() {
        return $this->_getMessageHeaderFieldFormated($this->cc);
    }

    public function setTo($to) {
        $this->setRecipients($to);
    }

    public function setDate($date = null, $validate = true) {
        $date = trim($date);
        $is_valid =  preg_match("/^".ACTION_MAILER_RFC_2822_DATE_REGULAR_EXPRESSION."$/",$date);
        $date = !$is_valid ? date('r', (empty($date) ? Ak::time() : (!is_numeric($date) ? strtotime($date) : $date))) : $date;

        if($validate && !$is_valid  && !preg_match("/^".ACTION_MAILER_RFC_2822_DATE_REGULAR_EXPRESSION."$/",$date)){
            trigger_error(Ak::t('You need to supply a valid RFC 2822 date. You can just leave the date field blank or pass a timestamp and Rails will automatically format the date for you'), E_USER_ERROR);
        }

        $this->date = $date;
    }

    public function setSentOn($date) {
        $this->setDate($date);
    }

    public function setReturnPath($return_path) {
        $this->returnPath = $return_path;
    }

    /**
     * Defaults to "1.0", but may be explicitly given if needed.
     */
    public function setMimeVersion($mime_version = null) {
        $this->mime_version = empty($mime_version) ? ((empty($this->mime_version) && !empty($this->parts)) ? '1.0' : $this->mime_version) : $mime_version;
    }

    /**
     * The recipient addresses for the message, either as a string (for a single
     * address) or an array (for multiple addresses).
     */
    public function setRecipients($recipients) {
        $this->recipients = $this->_getMessageHeaderFieldFormated($recipients);
        $this->setHeader('To',$this->getTo());
    }

    /**
     * Specify the subject of the message.
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    public function getSubject($charset = null) {
        $charset = empty($charset) ? $this->getCharset() : $charset;
        return AkActionMailerQuoting::quoteIfNecessary($this->subject, $charset);
    }

    public function getRawMessage() {
        return AkMailComposer::getRawMessage($this);
    }

    public function getRawHeadersAndBody() {
        $Composer = new AkMailComposer();
        return $Composer->getRawHeadersAndBody($this);
    }

    protected function _getMessageHeaderFieldFormated($address_header_field) {
        $charset = empty($this->charset) ? ACTION_MAILER_DEFAULT_CHARSET : $this->charset;
        return AkActionMailerQuoting::quoteAddressIfNecessary($address_header_field, $charset);
    }
}

