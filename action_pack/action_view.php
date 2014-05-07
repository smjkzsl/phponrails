<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

/**
* Action View templates can be written in two ways. If the template file has a +.tpl+ extension then it uses PHP.
*
* = PHP
*
* You trigger PHP by using embeddings such as <? ?>, <?php ?> and <?= ?>. The difference is whether you want output or not. Consider the
* following loop for names:
*
*   <b>Names of all the people</b>
*   <?php foreach($people as $person) : ?>
*   Name: <?=$person->name ?><br/>
*   <?php endforeach ?>
*
* == Using sub templates
*
* Using sub templates allows you to sidestep tedious replication and extract common display structures in shared templates. The
* classic example is the use of a header and footer (even though the Action Pack-way would be to use Layouts):
*
*   <?= $controller->render("shared/header") ?>
*   Something really specific and terrific
*   <?= $controller->render("shared/footer") ?>
*
* As you see, we use the output embeddings for the render methods. The render call itself will just return a string holding the
* result of the rendering. The output embedding writes it to the current template.
*
* But you don't have to restrict yourself to static includes. Templates can share variables amongst themselves by using instance
* variables defined using the regular embedding tags. Like this:
*
*   <?php $shared->page_title = "A Wonderful Hello" ?>
*   <?= $controller->render("shared/header") ?>
*
* Now the header can pick up on the $page_title variable and use it for outputting a title tag:
*
*   <title><?= $page_title ?></title>
*
* == Passing local variables to sub templates
*
* You can pass local variables to sub templates by using an array with the variable names as keys and the objects as values:
*
*   <?= $controller->render("shared/header", array('headline'=>'Welcome','person'=> $person )) ?>
*
* These can now be accessed in shared/header with:
*
*   Headline: <?= $headline ?>
*   First name: <?= $person->first_name ?>
*
*
* == JavaScriptGenerator ==
*
* @todo Fully implement Javascript Generators
*
* JavaScriptGenerator templates end in +.js.tpl+. Unlike conventional templates which are used to
* render the results of an action, these templates generate instructions on how to modify an already rendered page. This makes it easy to
* modify multiple elements on your page in one declarative Ajax response. Actions with these templates are called in the background with Ajax
* and make updates to the page where the request originated from.
*
* An instance of the JavaScriptGenerator object named +page+ is automatically made available to your template,
* which is implicitly wrapped in an AkActionView/Helpers/AkPrototypeHelper::update_page method.
*
* When an .js.tpl action is called with +linkToRemote+, the generated JavaScript is automatically evaluated.  Example:
*
*   linkToRemote(array('url' => array('action' => 'delete')));
*
* The subsequently rendered +delete.js.tpl+ might look like:
*
*   <% replace_html  'sidebar', :partial => 'sidebar' %>
*   <% remove "person-#{person.id}" %>
*   <% visual_effect :highlight, 'user-list' %>
*
* This refreshes the sidebar, removes a person element and highlights the user list.
*
* See the AkActionView/Helpers/PrototypeHelper/JavaScriptGenerator documentation for more details.
*/
class AkActionView
{
    public
    $first_render,
    $app_views_dir,
    $base_path,
    $assigns = array(),
    $template_extension,
    $controller,
    $logger,
    $params,
    $request,
    $response,
    $session,
    $headers,
    $HelperLoader,
    $format,
    $flash;

    public $_template_handlers = array();
    public $template_args = array();
    private $_local_assigns = array();

    public function __construct($base_path = null, $assigns_for_first_render = array(), $controller = null) {
        $this->app_views_dir = AkConfig::getDir('views');
        $this->base_path = empty($base_path) ? $this->app_views_dir : $base_path;
        $this->assigns = $assigns_for_first_render;
        $this->format = !empty($assigns_for_first_render['format']) ? $assigns_for_first_render['format'] : 'html';
        if(!empty($controller)){
            $this->controller = $controller;
            $this->logger = !empty($controller) && !empty($controller->Logger);
        }
    }

    /**
    * Register a class that knows how to handle template files with the given
    * extension. This can be used to implement new template types.
    * The constructor for the class must take the AkActionView instance
    * as a parameter, and the class must implement a "render" method that
    * takes the contents of the template to render as well as the array of
    * local assigns available to the template. The "render" method ought to
    * return the rendered template as a string.
    */
    public function registerTemplateHandler($extension, $className) {
        $this->_template_handlers[$extension] = $className;
    }


    /**
    * Renders the template present at <tt>template_path</tt>. If <tt>use_full_path</tt> is set to true,
    * it's relative to the template_root, otherwise it's absolute. The array in <tt>local_assigns</tt>
    * is made available as local variables.
    */
    public function renderFile($template_path, $use_full_path = true, $local_assigns = array()) {
        if(empty($this->first_render)){
            $this->first_render = $template_path;
        }

        list($template_extension, $template_file_name) = $this->_getTemplateExtenssionAndFileName($template_path, $use_full_path);

        $format = '';
        if(isset($local_assigns['params']['format']) && $local_assigns['params']['format'] != 'html'){
            $format = Ak::sanitize_include($local_assigns['params']['format'],'paranoid');
            $template_extension = $format.'.'.$template_extension;
        }

        if(DEBUG && CALLED_FROM_LOCALHOST && ENCLOSE_RENDERS_WITH_DEBUG_SPANS && empty($format)){
            $files_name = trim((str_replace(AkConfig::getDir('base'), '', realpath($template_file_name))), '/');
            return "\n\n<span title='file: $files_name'>".$this->renderTemplate($template_extension, null, $template_file_name, $local_assigns)."\n\n</span>";
        }

        return $this->renderTemplate($template_extension, null, $template_file_name, $local_assigns);
    }
    

    /**
    * Renders the template present at <tt>template_path</tt> (relative to the template_root).
    * The array in <tt>local_assigns</tt> is made available as local variables.
    */
    public function render($options = array()) {
        if(is_string($options)){
            $result = $this->renderFile($options, true);
        }elseif(is_array($options)){
            $options['locals'] = empty($options['locals']) ? array() : $options['locals'];
            $options['use_full_path'] = empty($options['use_full_path']) ? true : false;

            if(!empty($options['file'])){
                $result = $this->renderFile($options['file'], $options['use_full_path'], $options['locals']);
            }elseif (!empty($options['partial']) && (isset($options['collection']) && is_array($options['collection']))){
                $result = $this->renderPartialCollection($options['partial'], $options['collection'], @$options['spacer_template'], @$options['locals']);
            }elseif (!empty($options['partial'])){
                $result = $this->renderPartial($options['partial'], @$options['object'], @$options['locals']);
            }elseif ($options['inline']){
                $result = $this->renderTemplate(empty($options['type']) ? 'tpl' : $options['type'], $options['inline'], null, empty($options['locals']) ? array() : $options['locals']);
            }
        }
        if(!empty($options['layout'])){
            $layout = Ak::deleteAndGetValue($options, 'layout');
            list($template_extension, $template_file_name) = $this->_getTemplateExtenssionAndFileName($layout);
            return $this->renderTemplate('tpl', null, $template_file_name, array_merge($options['locals'], array('content_for_layout'=>$result)));
        }
        return $result;
    }


    /*
    * Renders the +template+ which is given as a string as tpl.php or js.tpl depending on <tt>template_extension</tt>.
    * The array in <tt>local_assigns</tt> is made available as local variables.
    */
    public function renderTemplate($____template_extension, $____template, $____file_path = null, $____local_assigns = array(), $____save_content_in_attribute_as = 'layout') {
        $____result = '';

        $____local_assigns = $this->getLocalAssigns($____local_assigns);

        if(strstr($____template_extension,'.')){
            $____format = substr($____template_extension, 0, strpos($____template_extension,'.'));
            $____template_extension = substr($____template_extension, strpos($____template_extension,'.')+1);
            $____file_path_for_format = preg_replace("/\.$____template_extension$/", ".$____format.$____template_extension", $____file_path);
            if(is_file($____file_path_for_format)){
                $____file_path = $____file_path_for_format;
            }
        }

        if(!empty($this->_template_handlers[$____template_extension])){
            $____handler = $this->_template_handlers[$____template_extension];
            $____template = empty($____template) ? $this->_readTemplateFile($____file_path) : $____template;
            $____result = $this->_delegateRender($____handler, $____template, $____local_assigns, $____file_path);
            if(is_array($____result)){
                $____save_content_in_attribute_as = $____result[0];
                $____result = $____result[1];
            }
        }else{
            trigger_error(Ak::t('Could not find a template engine for delegating templates with the extension %extension',array('%extension'=>$____template_extension)), E_USER_ERROR);
        }
        $this->{'content_for_'.$____save_content_in_attribute_as} = $____result;

        return $____result;
    }

    public function addSharedAttributes(&$local_assigns) {
        $this->_local_assigns = $local_assigns;
    }

    public function pickTemplateExtension($template_path) {
        if($match = $this->delegateTemplateExists($template_path)){
            return $match;
        }elseif($this->_templateExists($template_path,'tpl.php')){
            return 'tpl.php';
        }elseif($this->_templateExists($template_path, 'js.tpl')){
            return 'js.tpl';
        }else{
            trigger_error(Ak::t('No tpl.php, js.tpl or delegate template found for %template_path',array('%template_path'=>$template_path)), E_USER_ERROR);
            return false;
        }
    }

    public function delegateTemplateExists($template_path) {
        //~ $template_path=strtolower($template_path);
        foreach (array_keys($this->_template_handlers) as $k){
            if($this->_templateExists($template_path, $k)){
                return $k;
            }
        }
        return false;
    }

    /**
    * Returns true is the file may be rendered implicitly.
    */
    public function fileIsPublic($template_path) {
        return strpos(strrchr($template_path,DS),'_') !== 1;
    }

    public function getFullTemplatePath($template_path, $extension) {
        //the '.html'-extension is handled by a special ExtensionHandler, so we remove this here
        //that is of course a hack, basically to allow that you can use either index.tpl or index.html.tpl
        //as your template-filename
        if(substr($template_path,-5)=='.html'){
            $template_path = substr($template_path,0,-5);
        }

        $template_path_with_format = $this->_getTemplatePathWithFormat($template_path, $extension);
        $template_path = $this->_getTemplatePathWithoutFormat($template_path,$extension);
        if($template_path_with_format != $template_path){
            $template_path_with_format = $this->_getFullTemplatePath($template_path_with_format, true);
            if(file_exists($template_path_with_format)){
                return $template_path_with_format;
            }
        }
        return $this->_getFullTemplatePath($template_path, true);
    }

    protected function _getTemplatePathWithFormat($template_path, $extension){
        return substr($template_path,-1*strlen($extension)) == $extension ? $template_path :  $template_path.(strstr($extension,'.') ? '' : '.'.$this->format).'.'.$extension;
    }

    protected function _getTemplatePathWithoutFormat($template_path, $extension){
        return substr($template_path,-1*strlen($extension)) == $extension ? $template_path : $template_path.'.'.$extension;
    }

    private function _getFullTemplatePath($template_path, $recheck = false){
        $result = substr($template_path, 0, strlen($this->app_views_dir)) == $this->app_views_dir ? $template_path : $this->base_path.DS.$template_path;
        return $recheck ? $this->_getFullTemplatePath($result, false) : $result;
    }

    protected function _templateExists($template_path, $extension) {
       
        $file_path = $this->getFullTemplatePath($template_path, $extension);
        
        return !empty($this->_method_names[$file_path]) || file_exists($file_path);
    }

    protected function _readTemplateFile($template_path) {
        return file_get_contents($template_path);
    }

    protected function _delegateRender($handler, $template, $local_assigns, $file_path) {
        static $Handlers = array();
        if(empty($Handlers[$handler])){
            $HandlerInstance = new $handler($this);
            $Handlers[$handler] = $HandlerInstance;
        }else{
            $HandlerInstance = $Handlers[$handler];
            $HandlerInstance->init($this);
        }
        return $HandlerInstance->render($template, $local_assigns, $file_path);
    }

    public function javascriptTemplateExists($template_path) {
        return $this->_templateExists($template_path,'js.tpl');
    }

    /**
    * Partial Views
    *
    *  There's also a convenience method for rendering sub templates within the current controller that depends on a single object
    *  (we call this kind of sub templates for partials). It relies on the fact that partials should follow the naming convention of being
    *  prefixed with an underscore -- as to separate them from regular templates that could be rendered on their own.
    *
    *  In a template for AdvertiserController::account:
    *
    *   <?= $controller->render(array('partial' => 'account')); ?>
    *
    *  This would render "advertiser/_account.tpl" and pass the instance variable $controller->account in as a local variable $account to
    *  the template for display.
    *
    *  In another template for Advertiser::buy, we could have:
    *
    *    <?= $controller->render(array('partial' =>'account','locals'=>array('account'=>$buyer)));  ?>
    *
    *    <?php foreach($advertisements as $ad) : ?>
    *      <?= $controller->render(array('partial'=>'ad','locals'=>array('ad'=>$ad))); ?>
    *    <?php endforeach; ?>
    *
    *  This would first render "advertiser/_account.tpl" with $buyer passed in as the local variable $account, then render
    *  "advertiser/_ad.tpl" and pass the local variable $ad to the template for display.
    *
    *  == Rendering a collection of partials
    *
    *  The example of partial use describes a familiar pattern where a template needs to iterate over an array and render a sub
    *  template for each of the elements. This pattern has been implemented as a single method that accepts an array and renders
    *  a partial by the same name as the elements contained within. So the three-lined example in "Using partials" can be rewritten
    *  with a single line:
    *
    *    <?= $controller->render(array('partial'=>'ad','collection'=>(array)$advertisements)); ?>
    *
    *  This will render "advertiser/_ad.tpl" and pass the local variable +ad+ to the template for display. An iteration counter
    *  will automatically be made available to the template with a name of the form +partial_name_counter+. In the case of the
    *  example above, the template would be fed +ad_counter+.
    *
    *  == Rendering shared partials
    *
    *  Two controllers can share a set of partials and render them like this:
    *
    *    <?= $controller->render(array('partial'=>'advertiser/ad', 'locals' => array('ad' => $advertisement ))); ?>
    *
    *  This will render the partial "advertiser/_ad.tpl" regardless of which controller this is being called from.
    */
    public function renderPartial($partial_path, $object, $local_assigns = array()) {
        $path = $this->partialPathPiece($partial_path);
        $partial_name = $this->partialPathName($partial_path);
        $local_assigns = array_merge((array)@$this->controller->_assigns, (array)$local_assigns);
        $this->_addObjectToLocalAssigns_($partial_name, $local_assigns, $object);
        return $this->renderFile((empty($path) ? '' : $path.DS).'_'.$partial_name, true, $local_assigns);
    }

    public function renderPartialCollection($partial_name, $collection, $partial_spacer_template = null, $local_assigns = array()) {
        $collection_of_partials = array();
        $counter_name = $this->_partialCounterName($partial_name);
        if(empty($local_assigns[$counter_name])){
            $local_assigns[$counter_name] = 1;
        }

        foreach ($collection as $counter=>$element){
            $local_assigns[$counter_name] = $counter+1;
            $collection_of_partials[] = $this->renderPartial($partial_name, $element, $local_assigns);
        }

        if (empty($collection_of_partials)) {
            return ' ';
        }

        if (!empty($partial_spacer_template)){
            $spacer_path = $this->partialPathPiece($partial_spacer_template);
            $spacer_name = $this->partialPathName($partial_spacer_template);
            return join((empty($spacer_path) ? '' : $spacer_path.DS).'_'.$spacer_name,$collection_of_partials);
        }else{
            return join('',$collection_of_partials);
        }
    }

    public function setHelperLoader(&$HelperLoader){
        $this->HelperLoader = $HelperLoader;
    }

    public function &getHelperLoader(){
        return $this->HelperLoader;
    }

    public function renderCollectionOfPartials($partial_name, $collection, $partial_spacer_template = null, $local_assigns = array()) {
        return $this->renderPartialCollection($partial_name, $collection, $partial_spacer_template, $local_assigns);
    }

    public function partialPathPiece($partial_path) {
        if(strstr($partial_path, '/')){
            $dir_name = dirname($partial_path);
            if(strstr($dir_name,'/')){
                return $dir_name;
            }else{
                return $this->app_views_dir.DS.$dir_name;
            }
        }else{
            return '';
        }
    }

    public function partialPathName($partial_path) {
        return strstr($partial_path, '/') ? basename($partial_path) : $partial_path;
    }

    protected function getLocalAssigns($extra_assigns = array()){
        $controller_extras = isset($this->controller) ? array('controller_name' => $this->controller->getControllerName(), 'controller' => $this->controller) : array();
        $result = array_merge(
        $this->getGlobals(),
        $this->assigns,
        $this->_local_assigns,
        $extra_assigns,
        $controller_extras
        );

        return $result;
    }

    protected function _partialCounterName($partial_name) {
        return Ak::last(explode('/',$partial_name)).'_counter';
    }

    protected function _addObjectToLocalAssigns($partial_name, $local_assigns, &$object) {
        $local_assigns[$partial_name] = empty($object) ? $this->controller->$partial_name : $object;
    }

    protected function _addObjectToLocalAssigns_($partial_name, &$local_assigns, &$object) {
        if (is_null($object) && isset($this->controller->$partial_name)){
            $local_assigns[$partial_name] =& $this->controller->$partial_name;
        } elseif (!is_null($object)) {
            $local_assigns[$partial_name] = $object;
        }
    }
    

    protected function _getTemplateExtenssionAndFileName($template_path, $use_full_path = true){
        $template_path = substr($template_path,0,7) === 'layouts' ? AkConfig::getDir('views').DS.$template_path.'.tpl' : $template_path;
        if(!$use_full_path && strstr($template_path,'.')){
            $template_file_name = $template_path;
            $template_extension = substr($template_path,strpos($template_path,'.')+1);
        }else{
            $template_extension = $this->pickTemplateExtension($template_path);
            $template_file_name = $this->getFullTemplatePath($template_path, $template_extension);
        }
        return array($template_extension, $template_file_name);
    }

    /**
     * Variables assigned using this method will act on any controller or action. Use this in conjunction
     * with your application helpers in order to allow variable passing from inside your views.
     * This is used for example on the capture helper.
     */
    static function addGlobalVar($var_name, $value, $_retrieve = false) {
        static $_global_vars = array();
        if($_retrieve){
            return $_global_vars;
        }
        if($var_name[0] != '_'){
            if(isset($_global_vars[$var_name]) && is_string($_global_vars[$var_name])){
                $_global_vars[$var_name] .= $value;
            }else{
                $_global_vars[$var_name] = $value;
            }
        }
    }

    static function getGlobals() {
        return AkActionView::addGlobalVar(null,null,true);
    }

}

