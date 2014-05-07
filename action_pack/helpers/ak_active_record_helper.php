<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details


/**
* The Active Record Helper makes it easier to create forms for records kept in instance variables. The most far-reaching is the form
* method that creates a complete form for all the basic content types of the record (not associations or aggregations, though). This
* is a great of making the record quickly available for editing, but likely to prove lackluster for a complicated real-world form.
* In that case, it's better to use the input method and the specialized form methods from the FormHelper
*/
class AkActiveRecordHelper extends AkBaseHelper
{

    /**
    * Returns a default input tag for the type of object returned by the method. Example
    * (title is a VARCHAR column and holds "Hello World"):
    *   $active_record_helper->input('post', 'title'); =>
    *     <input id="post_title" name="post[title]" size="30" type="text" value="Hello World" />
    */
    public function input($record_name, $method, $options = array()) {
        $InstanceTag = new AkActiveRecordInstanceTag($record_name, $method, $this);
        return $InstanceTag->to_tag($options);
    }

    /**
    * Returns an entire form with input tags and everything for a specified Active Record object. Example
    * (post is a new record that has a title using VARCHAR and a body using TEXT):
    *   $active_record_helper->form('post'); =>
    *     <form action='/post/create' method='post'>
    *       <p>
    *         <label for="post_title">Title</label><br />
    *         <input id="post_title" name="post[title]" size="30" type="text" value="Hello World" />
    *       </p>
    *       <p>
    *         <label for="post_body">Body</label><br />
    *         <textarea cols="40" id="post_body" name="post[body]" rows="20">
    *           Back to the hill and over it again!
    *         </textarea>
    *       </p>
    *       <input type='submit' value='Create' />
    *     </form>
    *
    * It's possible to specialize the form builder by using a different action name and by supplying another
    * block renderer that will be evaled by PHP.
    * Example (entry is a new record that has a message attribute using VARCHAR):
    *
    *   $active_record_helper->form('entry', array('action'=>'sign','input_block' =>
    *  '<p><?=AkInflector::humanize($column)?>: <?=$this->input($record_name, $column)?></p><br />'
    *   );
    *
    *     <form action='/post/sign' method='post'>
    *       Message:
    *       <input id="post_title" name="post[title]" size="30" type="text" value="Hello World" /><br />
    *       <input type='submit' value='Sign' />
    *     </form>
    */
    public function form($record_name, $options = array()) {
        $record = $this->_controller->$record_name;

        $options['action'] = !empty($options['action']) ? $options['action'] : ($record->isNewRecord() ? 'create' : 'update');

        $action = $this->_controller->urlFor(array('action'=>$options['action'], 'id' => $record->getId()));

        $submit_value = !empty($options['submit_value']) ? $options['submit_value'] : strtoupper(preg_replace('/[^\w]/','',$options['action']));

        $contents = '';
        $contents .= $record->isNewRecord() ? '' : $this->_controller->ak_form_helper->hidden_field($record_name, 'id');
        $contents .= $this->all_input_tags($record, $record_name, $options);
        $contents .= AkFormTagHelper::submit_tag($this->t($submit_value));
        return AkTagHelper::content_tag('form', $contents, array('action'=>$action, 'method'=>'post',
        'enctype'=> !empty($options['multipart']) ? 'multipart/form-data': null ));
    }

    /**
    * Returns a string containing the error message attached to the +method+ on the +object+, if one exists.
    * This error message is wrapped in a DIV tag, which can be specialized to include both a +prepend_text+ and +append_text+
    * to properly introduce the error and a +css_class+ to style it accordingly. Examples (post has an error message
    * "can't be empty" on the title attribute):
    *
    *   <?= $active_record_helper->error_message_on('post', 'title'); ?>
    *     <div class="formError">can't be empty</div>
    *
    *   <?=$active_record_helper->error_message_on('post','title','Title simply ', " (or it won't work)", 'inputError') ?> =>
    *     <div class="inputError">Title simply can't be empty (or it won't work)</div>
    */
    public function error_message_on($object_name, $method, $prepend_text = '', $append_text = '', $css_class = 'formError') {
        if($errors = $this->_controller->$object_name->getErrorsOn($method)){
            $text = $prepend_text.(is_array($errors) ? array_shift($errors) : $errors).$append_text;
            return AkTagHelper::content_tag('div', $this->t($text), array('class'=>$css_class));
        }
        return '';
    }

    /**
    * Returns a string with a div containing all the error messages for the object located as an instance variable by the name
    * of <tt>object_name</tt>. This div can be tailored by the following options:
    *
    * * <tt>header_tag</tt> - Used for the header of the error div (default: h2)
    * * <tt>id</tt> - The id of the error div (default: errorExplanation)
    * * <tt>class</tt> - The class of the error div (default: errorExplanation)
    *
    * NOTE: This is a pre-packaged presentation of the errors with embedded strings and a certain HTML structure. If what
    * you need is significantly different from the default presentation, it makes plenty of sense to access the $object->getErrors()
    * instance yourself and set it up. View the source of this method to see how easy it is.
    */
    public function error_messages_for($object_name, $options = array()) {
        $object = $this->_controller->$object_name;
        if($object->hasErrors()){
            $error_list = '<ul>';
            foreach ($object->getFullErrorMessages() as $field=>$errors){
                foreach ($errors as $error){
                    $error_list .= AkTagHelper::content_tag('li',$error);
                }
            }
            $error_list .= '</ul>';
            return
            AkTagHelper::content_tag('div',
            AkTagHelper::content_tag(
                        (!empty($options['header_tag']) ? $options['header_tag'] :'h2'),
                        
                        $this->t('%number_of_errors %errors prohibited this %object_name from being saved' ,array(
                            '%number_of_errors'=>$object->countErrors(),
                            '%errors'=>$this->t(AkInflector::conditionalPlural($object->countErrors(),'error')),
                            '%object_name'=>$this->t(AkInflector::humanize($object->getModelName()))
                            ))
                    ).
                    AkTagHelper::content_tag('p', $this->t('There were problems with the following fields:')).
                    $error_list,
                    
                    array(
                        'id'=> !empty($options['id']) ? $options['id'] : 'errorExplanation', 
                        'class' => !empty($options['class']) ? $options['class'] : 'errorExplanation'
                        )
            );
        }
    }


    public function all_input_tags(&$record, $record_name, $options = array()) {
        $input_block = !empty($options['input_block']) ? $options['input_block'] : $this->default_input_block();
        $columns = empty($options['columns']) ? array_keys($record->getContentColumns()) : $options['columns'];
        $result = '';
        foreach ($columns as $column){
            ob_start();
            eval("?>$input_block<?php ");
            $result .= ob_get_clean()."\n";
        }
        return $result;
    }

    public function default_input_block() {
        return '<p><label for="<?php echo $record_name; ?>_<?php echo $column; ?>"><?php echo AkInflector::humanize($column); ?></label><br /><?php echo $this->input($record_name, $column); ?></p>';
    }
}

class AkActiveRecordInstanceTag extends AkFormHelperInstanceTag
{
    public $method_name;

    public function __construct($object_name, $column_name, &$template_object) {
        $column_name = $this->method_name = $this->_getColumnName($column_name, $object_name,  $template_object);
        parent::__construct($object_name, $column_name, $template_object);
    }

    public function to_tag($options = array()) {
        $options = array_merge($this->object->getErrorsOn($this->method_name)==false?array():array("class"=>"fieldError"), $options);

        switch ($this->get_column_type()) {

            case 'string':
            $field_type = strstr($this->method_name,'password') ? 'password' : 'text';
            return $this->to_input_field_tag($field_type, $options);
            break;

            case 'text':
            return $this->to_text_area_tag($options);
            break;

            case 'integer':
            case 'float':
            case 'decimal':
            return $this->to_input_field_tag('text', $options);
            break;

            case 'date':
            return $this->to_date_select_tag($options);
            break;

            case 'datetime':
            case 'timestamp':
            return $this->to_datetime_select_tag($options);
            break;

            case 'boolean':
            return $this->to_check_box_tag($options);
            break;

            default:
            return '';
            break;
        }
    }

    public function tag($name, $options = null, $open = false) {
        if($this->object->hasErrors()){
            return $this->error_wrapping($this->tag_without_error_wrapping($name, $options, $open), $this->object->getErrorsOn($this->method_name));
        }else{
            return $this->tag_without_error_wrapping($name, $options);
        }
    }

    public function tag_without_error_wrapping($name, $options, $open = false) {
        return parent::tag($name, $options, $open);
    }


    public function content_tag($name, $content, $options = null) {
        if($this->object->hasErrors()){
            return $this->error_wrapping($this->content_tag_without_error_wrapping($name, $value, $options), $this->object->getErrorsOn($this->method_name));
        }else{
            return $this->content_tag_without_error_wrapping($name, $value, $options);
        }
    }

    public function content_tag_without_error_wrapping($name, $value, $options) {
        return parent::content_tag($name, $value, $options);
    }

    public function to_date_select_tag($options = array()) {
        if($this->object->hasErrors()){
            return $this->error_wrapping($this->to_date_select_tag_without_error_wrapping($options), $this->object->getErrorsOn($this->method_name));
        }else{
            return $this->to_date_select_tag_without_error_wrapping($options);
        }
    }

    public function to_date_select_tag_without_error_wrapping($options = array()) {
        return parent::to_date_select_tag($options);
    }

    public function to_datetime_select_tag($options = array()) {
        if($this->object->hasErrors()){
            return $this->error_wrapping($this->to_datetime_select_tag_without_error_wrapping($options), $this->object->getErrorsOn($this->method_name));
        }else{
            return $this->to_datetime_select_tag_without_error_wrapping($options);
        }
    }

    public function to_datetime_select_tag_without_error_wrapping($options = array()) {
        return parent::to_datetime_select_tag($options);
    }

    public function to_check_box_tag($options = array(), $checked_value = '1', $unchecked_value = '0') {
        if($this->object->hasErrors()){
            return $this->error_wrapping($this->to_check_box_tag_without_error_wrapping($options), $this->object->getErrorsOn($this->method_name));
        }else{
            return $this->to_check_box_tag_without_error_wrapping($options);
        }
    }

    public function to_check_box_tag_without_error_wrapping($options = array()) {
        return parent::to_check_box_tag($options);
    }

    public function error_wrapping($html_tag, $has_error) {
        return $has_error ? "<div class=\"fieldWithErrors\">$html_tag</div>" : $html_tag;
    }

    public function error_message() {
        return $this->object->getErrorsOn($this->method_name);
    }

    public function get_column_type() {
        return $this->object->getColumnType($this->method_name);
    }

    protected function _getColumnName($column_name, $object_name, &$template_object) {

        $object = $template_object->getController()->{$object_name};
        $internationalized_columns = $object->getInternationalizedColumns();
        if(!empty($internationalized_columns[$column_name]))  {
            $current_locale = $object->getCurrentLocale();
            if(in_array($current_locale, $internationalized_columns[$column_name]))  {
                $column_name = $current_locale.'_'.$column_name;
            }
        }
        return $column_name;
    }
}

