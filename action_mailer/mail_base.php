<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

include_once(CONTRIB_DIR.DS.'pear'.DS.'Mail.php');

class AkMailBase extends Mail
{

    public $raw_message = '';
    public $charset = ACTION_MAILER_DEFAULT_CHARSET;
    public $content_type;
    public $body;

    public $parts = array();
    public $attachments = array();

    public $_attach_html_images = true;


    public function __construct() {
        $args = func_get_args();
        if(isset($args[0])){
            if(count($args) == 1 && is_string($args[0])){
                $this->raw_message = $args[0];
            }elseif(is_array($args[0])){
                AkMailParser::importStructure($this, $args[0]);
            }
        }
    }

    static function &parse($raw_email = '') {
        if(empty($raw_email)){
            trigger_error(Ak::t('Cannot parse an empty message'), E_USER_ERROR);
        }
        $Mail = new AkMailMessage((array)AkMailParser::parse($raw_email));
        return $Mail;
    }

    public function &load($email_file) {
        if(!file_exists($email_file)){
            trigger_error(Ak::t('Cannot find mail file at %path',array('%path'=>$email_file)), E_USER_ERROR);
        }
        $Mail = new AkMail((array)AkMailParser::parse(file_get_contents($email_file)));
        return $Mail;
    }

    public function setBody($body) {
        if(is_string($body)){
            $content_type = @$this->content_type;
            $this->body = stristr($content_type,'text/') ? str_replace(array("\r\n","\r"),"\n", $body) : $body;

            if($content_type == 'text/html'){
                $Parser = new AkMailParser();
                $Parser->applyCssStylesToTags($this);
                $Parser->addBlankTargetToLinks($this);
                if($this->_attach_html_images) {
                    $Parser->extractImagesIntoInlineParts($this);
                }
            }
        }else{
            $this->body = $body;
        }
    }

    public function getBody() {
        if(!is_array($this->body)){
            $encoding = $this->getContentTransferEncoding();
            $charset = $this->getCharset();
            switch ($encoding) {
                case 'quoted-printable':
                    return trim(AkActionMailerQuoting::chunkQuoted(AkActionMailerQuoting::quotedPrintableEncode($this->body, $charset)));
                case 'base64':
                    return $this->_base64Body($this->body);
                default:
                    return trim($this->body);
            }
        }
    }

    /**
     * Specify the CC addresses for the message.
     */
    public function setCc($cc) {
        $this->cc = $cc;
    }

    /**
     * Specify the BCC addresses for the message.
     */
    public function setBcc($bcc) {
        $this->bcc = $bcc;
    }

    /**
     * Specify the charset to use for the message.
     */
    public function setCharset($charset, $append_to_content_type_as_attribute = true) {
        $this->charset = $charset;
        if($append_to_content_type_as_attribute){
            $this->setContenttypeAttributes(array('charset'=>$charset));
        }
    }

    public function getCharset($default_to = null) {
        return empty($this->charset) ? ACTION_MAILER_DEFAULT_CHARSET : $this->charset;
    }

    /**
     * Specify the content type for the message. This defaults to <tt>text/plain</tt>
     * in most cases, but can be automatically set in some situations.
     */
    public function setContentType($content_type) {
        list($this->content_type, $ctype_attrs) = $this->_getContentTypeAndAttributes($content_type);
        $this->setContenttypeAttributes($ctype_attrs);
    }

    public function getContentType() {
        return empty($this->content_type) ? ($this->isMultipart()?'multipart/alternative':null) : $this->content_type.$this->getContenttypeAttributes();
    }

    public function hasContentType() {
        return !empty($this->content_type);
    }

    public function setContenttypeAttributes($attributes = array()) {
        foreach ($attributes as $key=>$value){
            if(strtolower($key) == 'charset'){
                $this->setCharset($value, false);
            }
            $this->content_type_attributes[$key] = $value;
        }
    }

    public function getContentTypeAttributes() {
        return $this->_getAttributesForHeader('content_type');
    }

    public function bodyToString($Mail = null, $only_first_text_part = false) {
        $Mail = empty($Mail) ? $this : $Mail;
        $result = '';
        foreach ((array)$Mail as $field => $value){
            if(!empty($value) && is_string($value)){
                if($Mail->isMainMessage() && $field=='body'){
                    $result .= $value."\n";
                }elseif(empty($Mail->data) && $field=='body'){
                    $result .= $value."\n";
                }elseif(!empty($Mail->data) && $field=='original_filename'){
                    $result .= $value;
                }
            }
            if($only_first_text_part && !empty($result)){
                return $result;
            }
            if($field == 'parts' && !empty($value) && is_array($value)){
                foreach ($value as $part){
                    if(!empty($part->data) && !empty($part->original_filename)){
                        $result .= "Attachment: ";
                        $result .= $Mail->bodyToString($part)."\n";
                    }else{
                        $result .= $Mail->bodyToString($part)."\n";
                    }
                    if($only_first_text_part && !empty($result)){
                        return $result;
                    }
                }
            }
        }

        return $result;
    }

    public function getTextPlainPart($Mail = null) {
        $Mail = empty($Mail) ? $this : $Mail;
        return $Mail->bodyToString($Mail, true);
    }

    public function isMainMessage() {
        return strtolower(get_class($this)) == 'akmailmessage';
    }

    public function isPart() {
        return strtolower(get_class($this)) == 'akmailpart';
    }

    /**
     * Specify the content disposition for the message.
     */
    public function setContentDisposition($content_disposition) {
        $this->content_disposition = $content_disposition;
    }

    /**
     * Specify the content transfer encoding for the message.
     */
    public function setContentTransferEncoding($content_transfer_encoding) {
        $this->content_transfer_encoding = $content_transfer_encoding;
    }

    /**
     * Alias for  setContentTransferEncoding
     */
    public function setTransferEncoding($content_transfer_encoding) {
        $this->setContentTransferEncoding($content_transfer_encoding);
    }

    public function getContentTransferEncoding() {
        if(empty($this->content_transfer_encoding)){
            return null;
        }
        return $this->content_transfer_encoding;
    }

    public function getTransferEncoding() {
        return $this->getTransferEncoding();
    }

    public function getDefault($field) {
        $field = AkInflector::underscore($field);
        $defaults = array(
        'charset' => $this->getCharset(),
        'content_type' => 'text/plain',
        );
        return isset($defaults[$field]) ? $defaults[$field] : null;
    }

    public function getRawHeaders($options = array()) {
        if(empty($this->raw_headers)){

            $this->headers = $this->getHeaders(true);

            if($this->isPart()){
                $this->prepareHeadersForRendering(array(
                'skip' => (array)@$options['skip'],
                'only' => (array)@$options['only']
                ));
            }
            unset($this->headers['Charset']);
            $headers = $this->prepareHeaders($this->headers);
            if(!is_array($headers)){
                trigger_error($headers->message, E_USER_NOTICE);
                return false;
            }else{
                $this->raw_headers = array_pop($headers);
            }
        }
        return $this->raw_headers;
    }

    public function getHeaders($force_reload = false) {
        if(empty($this->headers) || $force_reload){
            $this->loadHeaders();
            $this->_addHeaderAttributes();

        }
        return $this->headers;
    }

    public function getHeader($header_name) {
        $headers = $this->getHeaders();
        return isset($headers[$header_name]) ? $headers[$header_name] : null;
    }

    public function loadHeaders() {
        if(empty($this->date) && $this->isMainMessage()){
            $this->setDate();
        }
        $new_headers = array();
        $this->_moveMailInstanceAttributesToHeaders();
        foreach (array_map(array('AkActionMailerQuoting','chunkQuoted'), $this->headers) as $header=>$value){
            if(!is_numeric($header)){
                $new_headers[$this->_castHeaderKey($header)] = $value;
            }
        }
        $this->headers = $new_headers;
        $this->_sanitizeHeaders($this->headers);
    }

    /**
     * Specify additional headers to be added to the message.
     */
    public function setHeaders($headers, $options = array()) {
        foreach ((array)$headers as $name=>$value){
            $this->setHeader($name, $value, $options);
        }
    }

    public function setHeader($name, $value = null, $options = array()) {
        if(is_array($value)){
            $this->setHeaders($value, $options);
        }elseif($this->headerIsAllowed($name)){
            $this->headers[$name] = $value;
        }
    }

    /**
     * Generic setter
     *
     * Calling $this->set(array('body'=>'Hello World', 'subject' => 'First subject'));
     * is the same as calling $this->setBody('Hello World'); and $this->setSubject('First Subject');
     *
     * This simplifies creating mail objects from datasources.
     *
     * If the method does not exists the parameter will be added to the header.
     */
    public function set($attributes = array()) {
        foreach ((array)$attributes as $key=>$value){
            if($key[0] != '_' && $this->headerIsAllowed($key)){
                $attribute_setter = 'set'.AkInflector::camelize($key);
                if(method_exists($this, $attribute_setter)){
                    $this->$attribute_setter($value);
                }else{
                    $this->setHeader($key, $value);
                }
            }
        }
    }

    public function getSortedParts($parts, $order = array()) {
        $this->_parts_order = array_map('strtolower', empty($order) ? $this->implicit_parts_order : $order);
        usort($parts, array($this,'_contentTypeComparison'));
        return array_reverse($parts);
    }

    public function sortParts() {
        if(!empty($this->parts)){
            $this->parts = $this->getSortedParts($this->parts);
        }
    }

    public function setParts($parts, $position = 'append', $propagate_multipart_parts = false) {
        foreach ((array)$parts as $k=>$part){
            if(is_numeric($k)){
                $this->setPart((array)$part, $position, $propagate_multipart_parts);
            }else{
                $this->setPart($parts, $position, $propagate_multipart_parts);
                break;
            }
        }
    }

    /**
     * Add a part to a multipart message, with an array of options like
     * (content-type, charset, body, headers, etc.).
     *
     *   function my_mail_message()
     *   {
     *     $this->setPart(array(
     *       'content-type' => 'text/plain',
     *       'body' => "hello, world",
     *       'transfer_encoding' => "base64"
     *     ));
     *   }
     */
    public function setPart($options = array(), $position = 'append', $propagate_multipart_parts = false) {
        $default_options = array('content_disposition' => 'inline', 'content_transfer_encoding' => 'quoted-printable');
        $options = array_merge($default_options, $options);
        $Part = new AkMailPart($options);
        $position == 'append' ? array_push($this->parts, $Part) : array_unshift($this->parts, $Part);
        empty($propagate_multipart_parts) ? $this->_propagateMultipartParts() : null;
    }

    public function setAsMultipart() {
        $this->_multipart_message = true;
    }

    public function isMultipart() {
        return !empty($this->_multipart_message);
    }

    public function isAttachment() {
        return $this->content_disposition == 'attachment';
    }

    public function hasAttachments() {
        return !empty($this->attachments);
    }

    public function hasParts() {
        return !empty($this->parts);
    }

    public function hasNonAttachmentParts() {
        return (count($this->parts) - count($this->attachments)) > 0;
    }

    /**
     * Add an attachment to a multipart message. This is simply a part with the
     * content-disposition set to "attachment".
     *
     *     $this->setAttachment("image/jpg", array(
     *       'body' => AkFileSystem::file_get_contents('hello.jpg'),
     *       'filename' => "hello.jpg"
     *     ));
     */
    public function setAttachment() {
        $args = func_get_args();
        $options = array();
        if(count($args) == 2){
            $options['content_type'] = array_shift($args);
        }

        $arg_options = @array_shift($args);
        $options = array_merge($options, is_string($arg_options) ? array('body'=>$arg_options) : (array)$arg_options);
        $options = array_merge(array('content_disposition' => 'attachment', 'content_transfer_encoding' => 'base64'), $options);

        $this->setPart($options);
    }

    public function setAttachments($attachments = array()) {
        foreach ($attachments as $attachment){
            $this->setAttachment($attachment);
        }
    }

    public function setMessageId($id) {
        $this->messageId = $id;
    }

    /**
     * Specify the order in which parts should be sorted, based on content-type.
     * This defaults to the value for the +default_implicit_parts_order+.
     */
    public function setImplicitPartsOrder($implicit_parts_order) {
        $this->implicit_parts_order = $implicit_parts_order;
    }

    public function getEncoded() {
        $header = $this->getRawHeaders();
        return $header ? $header.ACTION_MAILER_EOL.ACTION_MAILER_EOL.$this->getBody() : false;
    }

    public function headerIsAllowed($header_name) {
        return preg_match('/default.?|template.?|.?deliver.?|server_settings|base_url|mailerName/', $header_name) != true;
    }

    public function moveBodyToInlinePart() {
        $options = array(
        'content_type' => @$this->content_type,
        'body' => @$this->body,
        'charset' => @$this->charset,
        'content_disposition' => 'inline'
        );
        foreach (array_keys($options) as $k){
            unset($this->$k);
        }

        $this->setAsMultipart();
        $this->setPart($options, 'preppend');
    }

    protected function _base64Body($content) {
        $Cache = Ak::cache();
        $cache_id = md5($content);
        $Cache->init(3600);
        if (!$encoded_content = $Cache->get($cache_id)) {
            $encoded_content = trim(chunk_split(base64_encode($content)));
            unset($content);
            $Cache->save($encoded_content);
        }
        return $encoded_content;
    }

    protected function _getAttributesForHeader($header_index, $force_reload = false) {
        if(empty($this->_header_attributes_set_for[$header_index]) || $force_reload){
            $header_index = strtolower(AkInflector::underscore($header_index)).'_attributes';
            if(!empty($this->$header_index)){
                $attributes = '';
                if(!empty($this->$header_index)){
                    foreach ((array)$this->$header_index as $key=>$value){
                        $attributes .= ";$key=$value";
                    }
                }
                $this->_header_attributes_set_for[$header_index] = $attributes;
            }
        }
        if (!empty($this->_header_attributes_set_for[$header_index])){
            return $this->_header_attributes_set_for[$header_index];
        }
    }

    protected function _getContentTypeAndAttributes($content_type = null) {
        if(empty($content_type)){
            return array($this->getDefault('content_type'), array());
        }
        $attributes = array();
        if(strstr($content_type,';')){
            list($content_type, $attrs) = preg_split("/;\\s*/",$content_type);
            if(!empty($attrs)){
                foreach ((array)$attrs as $s){
                    if(strstr($s,'=')){
                        list($k,$v) = array_map('trim', explode("=", $s, 2));
                        if(!empty($v)){
                            $attributes[$k] = $v;
                        }
                    }
                }
            }
        }

        $attributes = array_diff(array_merge(array('charset'=> (empty($this->_charset)?$this->getDefault('charset'):$this->_charset)),$attributes), array(''));
        return array(trim($content_type), $attributes);
    }

    protected function _addHeaderAttributes() {
        foreach($this->getHeaders() as $k=>$v){
            $this->headers[$k] .= $this->_getAttributesForHeader($k);
        }
    }

    protected function _moveMailInstanceAttributesToHeaders() {
        foreach ((array)$this as $k=>$v){
            if($k[0] != '_' && $this->_belongsToHeaders($k)){
                $attribute_getter = 'get'.ucfirst($k);
                $attribute_name = AkInflector::underscore($k);
                $header_value = method_exists($this,$attribute_getter) ? $this->$attribute_getter() : $v;
                is_array($header_value) ? null : $this->setHeader($attribute_name, $header_value);
            }
        }
    }

    protected function _belongsToHeaders($attribute) {
        return !in_array(strtolower($attribute),array('body','recipients','part','parts','raw_message','sep','implicit_parts_order','header','headers'));
    }

    protected function _castHeaderKey($key) {
        return str_replace(' ','-',ucwords(str_replace('_',' ',AkInflector::underscore($key))));
    }

    protected function _contentTypeComparison($a, $b) {
        if(!isset($a->content_type) || !isset($b->content_type)){
            if (!isset($a->content_type) && !isset($b->content_type)) {
                return 0;
            } else if (!isset($a->content_type)) {
                return -1;
            } else {
                return 1;
            }
        }

        $a_ct = strtolower($a->content_type);
        $b_ct = strtolower($b->content_type);
        $a_in = in_array($a_ct, $this->_parts_order);
        $b_in = in_array($b_ct, $this->_parts_order);
        if($a_in && $b_in){
            $a_pos = array_search($a_ct, $this->_parts_order);
            $b_pos = array_search($b_ct, $this->_parts_order);
            return (($a_pos == $b_pos) ? 0 : (($a_pos < $b_pos) ? -1 : 1));
        }
        return $a_in ? -1 : ($b_in ? 1 : (($a_ct == $b_ct) ? 0 : (($a_ct < $b_ct) ? -1 : 1)));
    }

    protected function _propagateMultipartParts() {
        if(!empty($this->parts)){
            foreach (array_keys($this->parts) as $k){
                $Part =& $this->parts[$k];
                if(empty($Part->_propagated)){
                    $Part->_propagated = true;
                    if(!empty($Part->content_disposition)){
                        // Inline bodies
                        if(isset($Part->content_type) && stristr($Part->content_type,'text/') && $Part->content_disposition == 'inline'){
                            if((!empty($this->body) && is_string($this->body))
                            ||  (!empty($this->body) && is_array($this->body) && ($this->isMultipart() || $this->content_type == 'text/plain'))
                            ){
                                $this->moveBodyToInlinePart();
                            }
                            $type = strstr($Part->content_type, '/') ? substr($Part->content_type,strpos($Part->content_type,"/")+1) : $Part->content_type;
                            $Part->_on_body_as = $type;
                            $this->body[$type] = $Part->body;

                        }

                        // Attachments
                        elseif ($Part->content_disposition == 'attachment' || ($Part->content_disposition == 'inline' && !preg_match('/^(text|multipart)\//i',$Part->content_type)) || !empty($Part->content_location)){
                            $this->_addAttachment($Part);
                        }
                    }
                }
            }
        }
    }

    protected function _addAttachment(&$Part) {
        $Part->original_filename = !empty($Part->content_type_attributes['name']) ? $Part->content_type_attributes['name'] :
        (!empty($Part->content_disposition_attributes['filename']) ? $Part->content_disposition_attributes['filename'] :
        (empty($Part->filename) ? @$Part->content_location : $Part->filename));

        $Part->original_filename = preg_replace('/[^A-Z^a-z^0-9^\-^_^\.]*/','',$Part->original_filename);

        if(!empty($Part->body)){
            $Part->data =& $Part->body;
        }
        if(empty($Part->content_disposition_attributes['filename'])){
            $Part->content_disposition_attributes['filename'] = $Part->original_filename;
        }
        if(empty($Part->content_type_attributes['name'])){
            $Part->content_type_attributes['name'] = $Part->original_filename;
        }
        unset($Part->content_type_attributes['charset']);
        $this->attachments[] =& $Part;
    }
}
