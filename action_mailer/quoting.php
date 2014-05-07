<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

class AkActionMailerQuoting
{
    /**
     * Convert the given text into quoted printable format, with an instruction
     * that the text be eventually interpreted in the given charset.
     */
    static function quotedPrintable($text, $charset = ACTION_MAILER_DEFAULT_CHARSET) {
        $pre="=?$charset?Q?";
        $start=0;
        $length=10;
        /**
         * Splitting string into characters with /u modifier,
         * to handle multibyte strings in utf-8
         */
        $chars = preg_split('//u',$text);
        $parts=array();
        /**
         * slicing them into chunks of 10 characters and encoding them with qp
         */
        while(count($subchars=array_slice($chars,$start,$length))>0) {
            $text = str_replace(' ','_', preg_replace('/[^a-z ]/ie', 'AkActionMailerQuoting::quotedPrintableEncode("$0")', join('',$subchars)));
            $parts[]=$pre.$text.'?=';
            $start+=$length;
        }
        $return=array_shift($parts);
        foreach($parts as $part) {
            $return.="\r\n ".$part;
        }

        return $return;
    }

    static function base64encode($text, $charset = ACTION_MAILER_DEFAULT_CHARSET) {
        $pre="=?$charset?B?";
        $start=0;
        $length=10;
        $chars=preg_split('//u',$text);
        $parts=array();
        while(count($subchars=array_slice($chars,$start,$length))>0) {
            $text = base64_encode(join('',$subchars));
            $parts[]=$pre.$text.'?=';
            $start+=$length;
        }
        $return=array_shift($parts);
        foreach($parts as $part) {
            $return.="\r\n ".$part;
        }

        return $return;
    }

    /**
     * Convert the given character to quoted printable format, taking into
     * account multi-byte characters
     */
    static function quotedPrintableEncode($character, $emulate_imap_8bit = ACTION_MAILER_EMULATE_IMAP_8_BIT) {
        $lines = preg_split("/(?:\r\n|\r|\n)/", $character);
        $search_pattern = $emulate_imap_8bit ? '/[^\x20\x21-\x3C\x3E-\x7E]/e' : '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';
        foreach ((array)$lines as $k=>$line){
            if (empty($line)){
                continue;
            }

            $line = preg_replace($search_pattern, 'sprintf( "=%02X", ord ( "$0" ) ) ;', $line );
            $length = strlen($line);

            $last_char = ord($line[$length-1]);
            if (!($emulate_imap_8bit && ($k==count($lines)-1)) && ($last_char==0x09) || ($last_char==0x20)) {
                $line[$length-1] = '=';
                $line .= ($last_char==0x09) ? '09' : '20';
            }
            if ($emulate_imap_8bit) {
                $line = str_replace(' =0D', '=20=0D', $line);
            }
            $lines[$k] = $line;
        }
        return implode(ACTION_MAILER_EOL,$lines);
    }

    /**
     * Quote the given text if it contains any "illegal" characters
     */
    static function quoteIfNecessary($text, $charset = ACTION_MAILER_DEFAULT_CHARSET) {
        return preg_match(ACTION_MAILER_CHARS_NEEDING_QUOTING_REGEX,$text) ? AkActionMailerQuoting::quotedPrintable($text,$charset) : $text;
    }

    /**
     * Quote any of the given strings if they contain any "illegal" characters
     */
    static function quoteAnyIfNecessary($strings = array(), $charset = ACTION_MAILER_DEFAULT_CHARSET) {
        foreach ($strings as $k=>$v){
            $strings[$k] = AkActionMailerQuoting::quoteIfNecessary($charset, $v);
        }
        return $strings;
    }

    /**
     *  Quote the given address if it needs to be. The address may be a
     * regular email address, or it can be a phrase followed by an address in
     * brackets. The phrase is the only part that will be quoted, and only if
     * it needs to be. This allows extended characters to be used in the
     * "to", "from", "cc", and "bcc" headers.
     */
    static function quoteAddressIfNecessary($address, $charset = ACTION_MAILER_DEFAULT_CHARSET) {
        if(is_array($address)){
            return join(", ".ACTION_MAILER_EOL."     ",AkActionMailerQuoting::quoteAnyAddressIfNecessary($address, $charset));
        }elseif (preg_match('/^(\S.*)\s+(<?('.ACTION_MAILER_EMAIL_REGULAR_EXPRESSION.')>?)$/i', $address, $match)){
            $address = $match[3];
            $quoted = AkActionMailerQuoting::quoteIfNecessary(trim($match[1],'\'"'), $charset);
            $phrase = str_replace('"','\"', $quoted);
            if($phrase[0] != '='){
                return "\"$phrase\" <$address>";
            }else{
                return "$phrase <$address>";
            }
        }else{
            return $address;
        }
    }

    /**
     *  Quote any of the given addresses, if they need to be.
     */
    static function quoteAnyAddressIfNecessary($address = array(), $charset = ACTION_MAILER_DEFAULT_CHARSET) {
        foreach ($address as $k=>$v){
            $address[$k] = is_string($k) ?
            AkActionMailerQuoting::quoteAddressIfNecessary('"'.$k.'" <'.$v.'>', $charset) :
            AkActionMailerQuoting::quoteAddressIfNecessary($v, $charset);
        }
        return $address;
    }

    static function chunkQuoted($quoted_string, $max_length = 74) {
        if(empty($max_length) || !is_string($quoted_string)){
            return $quoted_string;
        }

        $lines= preg_split("/(?:\r\n|\r|\n)/", $quoted_string);
        foreach ((array)$lines as $k=>$line){
            if (empty($line)){
                continue;
            }
            preg_match_all( '/.{1,'.($max_length - 2).'}([^=]{0,2})?/', $line, $match );
            $line = implode('='.ACTION_MAILER_EOL, $match[0] );

            $lines[$k] = $line;
        }
        return implode(ACTION_MAILER_EOL,$lines);
    }
}

