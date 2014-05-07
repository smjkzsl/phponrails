<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

/**
* Use these methods to generate HTML tags programmatically when you can't use a Builder.
* By default, they output XHTML compliant tags.
*/
class AkTagHelper
{
    /**
    * Returns an empty HTML tag of type *name* which by default is XHTML
    * compliant. Setting *open* to true will create an open tag compatible
    * with HTML 4.0 and below. Add HTML attributes by passing an attributes
    * array to *options*. For attributes with no value like (disabled and
    * readonly), give it a value of true in the *options* array.
    *
    * Examples:
    *
    *   <%= tag 'br' %>
    *    # => <br />
    *   <%= tag 'br', null, true %>
    *    # => <br>
    *   <%= tag 'input', { :type => 'text', :disabled => true } %>
    *    # => <input type="text" disabled="disabled" />
    */
    static function tag($name, $options = null, $open = false) {
        return '<'.$name.(!empty($options) ? AkTagHelper::tag_options($options) : '').($open ? '>' : ' />');
    }

    /**
    *  Returns an HTML block tag of type *name* surrounding the *content*. Add
    * HTML attributes by passing an attributes array to *options*. For attributes
    * with no value like (disabled and readonly), give it a value of true in
    * the *options* array. You can use symbols or strings for the attribute names.
    *
    *   <%= content_tag 'p', 'Hello world!' %>
    *    # => <p>Hello world!</p>
    *   <%= content_tag('div', content_tag('p', "Hello world!"), :class => "strong") %>
    *    # => <div class="strong"><p>Hello world!</p></div>
    *   <%= content_tag("select", options, :multiple => true) %>
    *    # => <select multiple="multiple">...options...</select>
    */
    static function content_tag($name, $content, $options = null) {
        return '<'.$name.(!empty($options) ? AkTagHelper::tag_options($options) : '').'>'.$content.'</'.$name.'>';
    }

    /**
    * Returns a CDATA section for the given +content+.  CDATA sections
    * are used to escape blocks of text containing characters which would
    * otherwise be recognized as markup. CDATA sections begin with the string
    * <tt>&lt;![CDATA[</tt> and } with (and may not contain) the string
    * <tt>]]></tt>.
    */
    static function cdata_section($content) {
        return '<![CDATA['.$content.']]>';
    }


    /**
    * Returns the escaped +html+ without affecting existing escaped entities.
    *
    *  <%= escape_once "1 > 2 &amp; 3" %>
    *    # => "1 &gt; 2 &amp; 3"
    */
    static function escape_once($html) {
        return AkTagHelper::fix_double_escape(AkTextHelper::html_escape($html));
    }

    /**
    * Fix double-escaped entities, such as &amp;amp;, &amp;#123;, etc.
    */
    static function fix_double_escape($escaped) {
        return preg_replace('/&amp;([a-z]+|(#\d+));/i', '&$1;', $escaped);
    }

    static function tag_options($options) {
        $formated_options = array();
        foreach ($options as $key=>$value){
            if(empty($value) && !is_string($value)){
                continue;
            }
            if(!is_numeric($key) && !is_array($value) && !is_object($value)){
                $formated_options[$key] =  $key.'="'.AkTagHelper::escape_once($value).'"';
            }
        }
        ksort($formated_options);
        return empty($formated_options) ? '' : ' '.join(' ',$formated_options);
    }
}

