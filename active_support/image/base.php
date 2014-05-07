<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

require_once(CONTRIB_DIR.DS.'pear'.DS.'Image'.DS.'Transform.php');

/**
 * AkImage provides a simple interface for image manipulation within Rails
 *
 *
 * Native Image filters based on http://php.net/imagefilter
 *
 *  h2. negate
 *
 *  Reverses all colors of the image.
 *
 *  Example:
 *
 *     $Image = new AkImage('/photo.jpg');
 *     $Image->transform('negate');
 *     $Image->save('/negative.jpg');
 *
 *  h2. grayscale
 *
 *  Converts the image into grayscale.
 *
 *     $Image = new AkImage('/photo.jpg');
 *     $Image->transform('grayscale');
 *     $Image->save('/grayscale.jpg');
 *
 * h2. brightness
 *
 *  Changes the brightness of the image. Use arg1 to set the level of brightness.
 *
 *     $Image = new AkImage('/photo.jpg');
 *     $Image->transform('brightness', 50);
 *     $Image->save('/bright_photo.jpg');
 *
 * h2. contrast
 *
 *  Changes the contrast of the image. Use arg1 to set the level of contrast.
 *
 *     $Image = new AkImage('/photo.jpg');
 *     $Image->transform('contrast', 50);
 *     $Image->save('/contrast_photo.jpg');
 *
 * h2. colorize
 *
 *  Like grayscale, except you can specify the color. Use arg1 , arg2 and arg3 in the form of red , blue , green and arg4 for the alpha channel. The range for each color is 0 to 255.
 *
 *     $Image = new AkImage('/photo.jpg');
 *     $Image->transform('colorize', array(100,25,30));
 *     $Image->save('/colorized_photo.jpg');
 *
 * h2. detect_edges
 *
 *  Uses edge detection to highlight the edges in the image.
 *
 * h2. emboss
 *
 *  Embosses the image.
 *
 * h2. gaussion_blur
 *
 *  Blurs the image using the Gaussian method
 *
 * h2. selective_blur
 *
 *  Blurs the image.
 *
 *  h2. sketch
 *
 *  Uses mean removal to achieve a "sketchy" effect.
 *
 * h2. smooth
 *
 *  Makes the image smoother. Use arg1 to set the level of smoothness.
 *
 *  h2. pixelate
 *
 *  Applies pixelation effect to the image, use arg1 to set the block size and arg2 to set the pixelation effect mode.
 */

class AkImage extends Image_Transform
{
    public $image_path;
    public $Transform;
    public $filters = array();

    public function __construct($image_path = null, $tranform_using = IMAGE_DRIVER) {
        $this->Transform = Image_Transform::factory($tranform_using);

        if(PEAR::isError($this->Transform)){
            trigger_error($this->Transform->getMessage(), E_USER_ERROR);
        }
        if(!empty($image_path)){
            $this->load($image_path);
        }
    }

    public function load($image_path) {
        $this->image_path = $image_path;
        $this->Transform->load($image_path);
    }

    public function save($path = null, $quality = 100, $options = array()) {
        if(!$tmp_image_name = tempnam(TMP_DIR,'ak_image_')){
            trigger_error(Ak::t('Could not create the temporary file %tmp_image_name for apliying changes and saving', array('%tmp_image_name'=>$tmp_image_name)), E_USER_ERROR);
        }
        $options['skip_restricting_origin'] = true;
        $path = empty($path) ? $this->image_path : $path;
        $this->Transform->save($tmp_image_name, $this->getExtension($path), $quality);
        AkFileSystem::move($tmp_image_name, $path, $options);
    }

    public function transform($transformation, $options = array()) {
        if(!is_array($options)){
            $args = func_get_args();
            array_shift($args);
            $options = $args;
        }

        $this->filters = array();
        $this->addFilter($transformation, $options);
        $this->applyFilters();
    }

    public function getWidth() {
        return $this->Transform->getImageWidth();
    }

    public function getHeight() {
        return $this->Transform->getImageHeight();
    }

    public function getExtension($path = null) {
        return substr(strrchr(empty($path) ? $this->image_path : $path, '.'), 1);
    }

    public function addFilter($filter_name, $options = array()) {
        if($this->isNativeFiler($filter_name)){
            $this->addNativeFilter($filter_name, $options);
        }elseif($this->_filterExists($filter_name)){
            $class_name = $this->_getFilterClassName($filter_name);
            $filter = new $class_name();
            if(method_exists($filter,'init')){
                $filter->init();
            }
            $filter->setImage($this);
            $filter->setOptions($options);
            $this->filters[] = $filter;
            return true;
        }
        return false;
    }

    public function _filterExists($filter_name) {
        if(class_exists($filter_name)){
            return true;
        }

        $file_name = $this->_getFilterFileName($filter_name);
        $class_name = $this->_getFilterClassName($filter_name);
        $success = true;
        if(!file_exists($file_name)){
            $success = false;
        }else{
            require_once($file_name);
        }
        $success = class_exists($class_name) ? $success : false;

        if(!$success){
            trigger_error(Ak::t('Could not find image filter %class_name at %file_name', array('%class_name'=>$class_name, '%file_name'=>$file_name)), E_USER_ERROR);
        }
        return $success;
    }

    public function _getFilterClassName($filter_name) {
        // We might allow other classes to be created as filters in order to create image filter plugins from outside the framework
        if(!class_exists($filter_name)){
            return 'AkImage'.AkInflector::classify($filter_name).'Filter';
        }else{
            return $filter_name;
        }
    }

    public function _getFilterFileName($filter_name) {
        return ACTIVE_SUPPORT_DIR.DS.'image'.DS.'filters'.DS.AkInflector::underscore($filter_name).'.php';
    }

    public function _getFilterChainPath($name, $path) {
        return (empty($path) ? AkConfig::getDir('app').DS.'image_filters' : rtrim($path,DS.'/')).DS.$name.'_filter.php';
    }

    public function applyFilters() {
        foreach (array_keys($this->filters) as $k){
            if(!empty($this->filters[$k]->native_filter_constant)){
                call_user_func_array('imagefilter', $this->filters[$k]->params);
            }else{
                $this->filters[$k]->apply();
            }
        }
    }

    public function saveFilterChain($name, $filter_chain = null, $filters_directory = null, $options = array()) {
        $path = $this->_getFilterChainPath($name, $filters_directory);
        $filter_chain = empty($filter_chain) ? $this->getFilterChain() : $filter_chain;
        return AkFileSystem::file_put_contents($path, '<?php $filter_chain = '.var_export($filter_chain, true).'; ?>', $options);
    }

    public function getFilterChain() {
        $filter_chain = array();
        foreach (array_keys($this->filters) as $k){
            $filter_chain[] = array('name'=>$this->filters[$k]->getName(),'options'=>$this->filters[$k]->getOptions());
        }
        return $filter_chain;
    }

    public function setFilterChain($filter_chain) {
        $this->filters = array();
        $this->appendFilterChain($filter_chain);
    }

    public function appendFilterChain($filter_chain) {
        foreach ($filter_chain as $filter){
            $this->addFilter($filter['name'],$filter['options']);
        }
    }

    public function loadFilterChain($name, $filters_directory = null) {
        $path = $this->_getFilterChainPath($name, $filters_directory);
        $success = file_exists($path);
        @include($path);
        if(!$success || empty($filter_chain)){
            trigger_error(Ak::t('Could not find a valid %name filer chain at %path.', array('%name'=>$name,'%path'=>$path)), E_USER_ERROR);
        }
        $this->setFilterChain($filter_chain);
    }

    public function applyFilterChain($name, $filters_directory = null) {
        $this->loadFilterChain($name, $filters_directory);
        $this->applyFilters();
    }

    public function isNativeFiler($filter_name) {
        return $this->getNativeFilerConstant($filter_name) != false;
    }

    public function addNativeFilter($filter_name, $params = array()) {
        $filter = new stdClass();
        if($filter->native_filter_constant = $this->getNativeFilerConstant($filter_name)){
            array_unshift($params, constant($filter->native_filter_constant));
            array_unshift($params, $this->Transform->imageHandle);
            $filter->params = array_diff($params, array(''));
            $this->filters[] = $filter;
        }
    }

    public function getNativeFilerConstant($filter_name) {
        $filter_name = AkInflector::underscore($filter_name);
        $native_filters = array(
        'negate' =>'IMG_FILTER_NEGATE',
        'grayscale' =>'IMG_FILTER_GRAYSCALE',
        'brightness' =>'IMG_FILTER_BRIGHTNESS',
        'contrast' =>'IMG_FILTER_CONTRAST',
        'colorize' =>'IMG_FILTER_COLORIZE',
        'detect_edges' =>'IMG_FILTER_EDGEDETECT',
        'emboss' =>'IMG_FILTER_EMBOSS',
        'gaussian_blur' =>'IMG_FILTER_GAUSSIAN_BLUR',
        'selective_blur' =>'IMG_FILTER_SELECTIVE_BLUR',
        'sketch' =>'IMG_FILTER_MEAN_REMOVAL',
        'smooth' =>'IMG_FILTER_SMOOTH',
        'pixelate' =>'IMG_FILTER_PIXELATE'
        );
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            unset($native_filters['pixelate']);
        }
        if (version_compare(PHP_VERSION, '5.2.5', '<')) {
            unset($native_filters['colorize']);
        }
        return isset($native_filters[$filter_name]) ? (defined($native_filters[$filter_name]) ? $native_filters[$filter_name] : false) : false;
    }
}
