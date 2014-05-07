<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

define('RAILS_VERSION', '2.0.1');

/**
 * This file will bootstrap the Rails framework by:
 * 
 *  - Registering autoloaders
 *  - Defining default constants if they were not set yet.
 * 
 * If you need to customize the framework default settings or specify internationalization options,
 * edit the files config/testing.php, config/development.php, config/production.php
 * 
 * To register an autoloader before Rails autoloader, you can do it so by calling:
 * 
 *      Ak::registerAutoloader('my_better_loader');
 */

defined('DS')               || define('DS',             DIRECTORY_SEPARATOR);
defined('ENVIRONMENT')   || define('ENVIRONMENT', 'development');

// You should declare BASE_DIR before including the autoloader.
if(!defined('BASE_DIR')){
    $__ak_base_dir = array_slice(get_included_files(),-2,1);
    define('BASE_DIR', dirname($__ak_base_dir[0]));
    unset($__ak_base_dir);
    define('SKIP_ENV_CONFIG', true);
}

defined('SKIP_ENV_CONFIG')   || define('SKIP_ENV_CONFIG', false);
defined('RAILS_DIR')     || define('RAILS_DIR', str_replace(DS.'autoload.php','',__FILE__));
defined('TESTING_NAMESPACE') || define('TESTING_NAMESPACE', 'rails');

/**
 * Paths for the frameworks in Rails
 */
defined('ACTION_MAILER_DIR')     || define('ACTION_MAILER_DIR',   RAILS_DIR.DS.'action_mailer');
defined('ACTION_PACK_DIR')       || define('ACTION_PACK_DIR',     RAILS_DIR.DS.'action_pack');
defined('ACTIVE_RECORD_DIR')     || define('ACTIVE_RECORD_DIR',   RAILS_DIR.DS.'active_record');
defined('ACTIVE_RESOURCE_DIR')   || define('ACTIVE_RESOURCE_DIR', RAILS_DIR.DS.'active_resource');
defined('ACTIVE_SUPPORT_DIR')    || define('ACTIVE_SUPPORT_DIR',  RAILS_DIR.DS.'active_support');
defined('ACTIVE_DOCUMENT_DIR')   || define('ACTIVE_DOCUMENT_DIR', RAILS_DIR.DS.'active_document');
defined('RAILS_UTILS_DIR')      || define('RAILS_UTILS_DIR',    RAILS_DIR.DS.'rails_utils');
defined('GENERATORS_DIR')        || define('GENERATORS_DIR',      RAILS_UTILS_DIR.DS.'generators');

function rails_autoload($name, $path = null) {
    static $paths = array(), $lib_paths = array(), $app_paths = array();

    if (!empty($path)){
        $paths[$name] = $path;
        return ;
    }

    if(empty($app_paths)){
        $app_paths = array(
        'BaseActionController'      =>  'controllers/base_action_controller.php',
        'BaseActiveRecord'          =>  'models/base_active_record.php',
        'ApplicationController'     =>  'controllers/application_controller.php',
        'ActiveRecord'              =>  'models/active_record.php',
        );
    }

    if(empty($lib_paths)){
        $lib_paths = array(
        // Action Mailer
        'AkActionMailer'        => 'action_mailer/base.php',
        'AkMailComposer'        => 'action_mailer/composer.php',
        'AkMailEncoding'        => 'action_mailer/encoding.php',
        'AkMailBase'            => 'action_mailer/mail_base.php',
        'AkMailMessage'         => 'action_mailer/message.php',
        'AkMailParser'          => 'action_mailer/parser.php',
        'AkMailPart'            => 'action_mailer/part.php',
        'AkActionMailerQuoting' => 'action_mailer/quoting.php',

        // Action Pack
        'AkActionController'            => 'action_pack/action_controller.php',
        'AkExceptionDispatcher'         => 'action_pack/exception_dispatcher.php',
        'AkActionView'                  => 'action_pack/action_view.php',
        'AkBaseHelper'                  => 'action_pack/base_helper.php',
        'AkActionViewHelper'            => 'action_pack/base_helper.php',
        'AkCacheHandler'                => 'action_pack/cache_handler.php',
        'AkCacheSweeper'                => 'action_pack/cache_sweeper.php',
        'AkActionControllerTest'        => 'action_pack/testing.php',
        'AkHelperTest'                  => 'action_pack/testing.php',
        'AkDbSession'                   => 'action_pack/db_session.php',
        'AkCookieStore'                 => 'action_pack/cookie_store.php',
        'AkDispatcher'                  => 'action_pack/dispatcher.php',
        'AkActiveRecordHelper'          => 'action_pack/helpers/ak_active_record_helper.php',
        'AkAssetTagHelper'              => 'action_pack/helpers/ak_asset_tag_helper.php',
        'AkFormHelperBuilder'           => 'action_pack/helpers/ak_form_helper.php',
        'AkFormHelperInstanceTag'       => 'action_pack/helpers/ak_form_helper.php',
        'AkFormHelper'                  => 'action_pack/helpers/ak_form_helper.php',
        'AkFormHelperOptionsInstanceTag'=> 'action_pack/helpers/ak_form_options_helper.php',
        'AkFormTagHelper'               => 'action_pack/helpers/ak_form_tag_helper.php',
        'AkJavascriptHelper'            => 'action_pack/helpers/ak_javascript_helper.php',
        'AkJavascriptMacrosHelper'      => 'action_pack/helpers/ak_javascript_macros_helper.php',
        'AkMailHelper'                  => 'action_pack/helpers/ak_mail_helper.php',
        'AkMenuHelper'                  => 'action_pack/helpers/ak_menu_helper.php',
        'AkNumberHelper'                => 'action_pack/helpers/ak_number_helper.php',
        'AkPaginationHelper'            => 'action_pack/helpers/ak_pagination_helper.php',
        'AkPrototypeHelper'             => 'action_pack/helpers/ak_prototype_helper.php',
        'AkScriptaculousHelper'         => 'action_pack/helpers/ak_scriptaculous_helper.php',
        'AkTextHelper'                  => 'action_pack/helpers/ak_text_helper.php',
        'AkUrlHelper'                   => 'action_pack/helpers/ak_url_helper.php',
        'AkXmlHelper'                   => 'action_pack/helpers/ak_xml_helper.php',
        'AkHelperLoader'                => 'action_pack/helper_loader.php',
        'AkPaginator'                   => 'action_pack/pagination.php',
        'AkPhpCodeSanitizer'            => 'action_pack/php_code_sanitizer.php',
        'AkPhpTemplateHandler'          => 'action_pack/php_template_handler.php',
        'AkRequest'                     => 'action_pack/request.php',
        'AkResponse'                    => 'action_pack/response.php',

        'AkRouter'                      => 'action_pack/router/base.php',
        'AkDynamicSegment'              => 'action_pack/router/dynamic_segment.php',
        'AkLangSegment'                 => 'action_pack/router/lang_segment.php',
        'AkRoute'                       => 'action_pack/router/route.php',
        'AkRouterConfig'                => 'action_pack/router/router_config.php',
        'AkRouterHelper'                => 'action_pack/router/router_helper.php',
        'AkSegment'                     => 'action_pack/router/segment.php',
        'AkStaticSegment'               => 'action_pack/router/static_segment.php',
        'AkUrl'                         => 'action_pack/router/url.php',
        'AkUrlWriter'                   => 'action_pack/router/url_writer.php',
        'AkVariableSegment'             => 'action_pack/router/variable_segment.php',
        'AkWildcardSegment'             => 'action_pack/router/wildcard_segment.php',
        'AkResource'                    => 'action_pack/router/resources.php',
        'AkResources'                   => 'action_pack/router/resources.php',
        'AkSingletonResource'           => 'action_pack/router/resources.php',

        'AkSession'                     => 'action_pack/session.php',
        'AkStream'                      => 'action_pack/stream.php',
        'AkSintags'                     => 'action_pack/template_engines/sintags/base.php',
        'AkSintagsLexer'                => 'action_pack/template_engines/sintags/lexer.php',
        'AkSintagsParser'               => 'action_pack/template_engines/sintags/parser.php',
        'AkXhtmlValidator'              => 'action_pack/xhtml_validator.php',


        'AkActionWebService'        => 'action_pack/action_web_service.php',
        'AkActionWebserviceApi'     => 'action_pack/action_web_service/api.php',
        'AkActionWebServiceClient'  => 'action_pack/action_web_service/client.php',
        'AkActionWebServiceServer'  => 'action_pack/action_web_service/server.php',

        // Active Record
        'AkActiveRecord'            => 'active_record/base.php',
        'AkDbAdapter'               => 'active_record/adapters/base.php',
        'AkAssociatedActiveRecord'  => 'active_record/associated_active_record.php',
        'AkAssociation'             => 'active_record/associations/base.php',
        'AkBelongsTo'               => 'active_record/associations/belongs_to.php',
        'AkHasAndBelongsToMany'     => 'active_record/associations/has_and_belongs_to_many.php',
        'AkHasMany'                 => 'active_record/associations/has_many.php',
        'AkHasOne'                  => 'active_record/associations/has_one.php',
        'AkDbSchemaCache'           => 'active_record/database_schema_cache.php',
        'AkActiveRecordMock'        => 'active_record/mock.php',
        'AkObserver'                => 'active_record/observer.php',

        // Active Resource
        'AkHttpClient' => 'active_resource/http_client.php',

        // Active Support
        'AkAdodbCache'              => 'active_support/cache/adodb.php',
        'AkCache'                   => 'active_support/cache/base.php',
        'AkMemcache'                => 'active_support/cache/memcache.php',
        'AkColor'                   => 'active_support/color/base.php',
        'AkConsole'                 => 'active_support/console/base.php',
        'AkAnsiColor'               => 'active_support/console/ansi.php',
        'AkConfig'                  => 'active_support/config/base.php',
        'AkClassExtender'           => 'active_support/core/class_extender.php',
        'AkDebug'                   => 'active_support/core/debug.php',
        'AkLazyObject'              => 'active_support/core/lazy_object.php',
        'AkArray'                   => 'active_support/core/types/array.php',
        'AkType'                    => 'active_support/core/types/base.php',
        'AkDate'                    => 'active_support/core/types/date.php',
        'AkMimeType'                => 'active_support/core/types/mime.php',
        'AkNumber'                  => 'active_support/core/types/number.php',
        'AkString'                  => 'active_support/core/types/string.php',
        'AkTime'                    => 'active_support/core/types/time.php',
        'AkFileSystem'              => 'active_support/file_system/base.php',
        'SaeMCFS'              => 'active_support/file_system/saemc.class.php',//2014-04-26s
        'RailsGenerator'           => 'active_support/generator.php',
        'AkCharset'                 => 'active_support/i18n/charset/base.php',
        'AkCountries'               => 'active_support/i18n/countries.php',
        'AkLocaleManager'           => 'active_support/i18n/locale_manager.php',
        'AkTimeZone'                => 'active_support/i18n/time_zone.php',
        'AkImage'                   => 'active_support/image/base.php',
        'AkImageColorScheme'        => 'active_support/image/color_scheme.php',
        'AkImageFilter'             => 'active_support/image/filters/base.php',
        'AkLogger'                  => 'active_support/logger.php',
        'AkInstaller'               => 'active_support/migrations/installer.php',
        'AkBaseModel'               => 'active_support/models/base.php',
        'AkModelExtenssion'         => 'active_support/models/base.php',
        'AkFtp'                     => 'active_support/network/ftp.php',
        'AkPlugin'                  => 'active_support/plugin/base.php',
        'AkPluginLoader'            => 'active_support/plugin/base.php',
        'AkPluginInstaller'         => 'active_support/plugin/installer.php',
        'AkPluginManager'           => 'active_support/plugin/manager.php',
        'AkProfiler'                => 'active_support/profiler.php',
        'AkReflection'              => 'active_support/reflection/base.php',
        'AkReflectionClass'         => 'active_support/reflection/class.php',
        'AkReflectionDocBlock'      => 'active_support/reflection/doc_block.php',
        'AkReflectionFile'          => 'active_support/reflection/file.php',
        'AkReflectionFunction'      => 'active_support/reflection/function.php',
        'AkReflectionMethod'        => 'active_support/reflection/method.php',
        'AkTestApplication'         => 'active_support/testing/application.php',
        'RailsTextReporter'        => 'active_support/testing/base.php',
        'RailsVerboseTextReporter' => 'active_support/testing/base.php',
        'AkXUnitXmlReporter'        => 'active_support/testing/base.php',
        'AkUnitTest'                => 'active_support/testing/base.php',
        'AkWebTestCase'             => 'active_support/testing/base.php',
        'AkTestDispatcher'          => 'active_support/testing/dispatcher.php',
        'AkTestRequest'             => 'active_support/testing/request.php',
        'AkTestResponse'            => 'active_support/testing/response.php',
        'AkUnitTestSuite'           => 'active_support/testing/suite.php',
        'AkRouterUnitTest'          => 'active_support/testing/router.php',
        'AkRouteUnitTest'           => 'active_support/testing/route.php',
        'AkControllerUnitTest'      => 'active_support/testing/controller.php',
        'AkInflector'               => 'active_support/text/inflector.php',
        'AkLexer'                   => 'active_support/text/lexer.php',
        'AkError'                   => 'active_support/error_handlers/base.php',

        // Active Document
        'AkActiveDocument'          => 'active_document/base.php',
        'DocInstaller'          => 'rails_utils'.DS.'doc_builder'.DS.'installers'.DS.'doc_installer.php',
        'SourceAnalyzer'          => 'rails_utils'.DS.'doc_builder'.DS.'models'.DS.'source_analyzer.php',
        'File'          => 'rails_utils'.DS.'doc_builder'.DS.'models'.DS.'file.php',
        'Component'          => 'rails_utils'.DS.'doc_builder'.DS.'models'.DS.'component.php',
        'Klass'          => 'rails_utils'.DS.'doc_builder'.DS.'models'.DS.'klass.php',
        'SourceParser'          => 'rails_utils'.DS.'doc_builder'.DS.'models'.DS.'source_parser.php',
        'AkOdbAdapter'              => 'active_document/adapters/base.php',

        );
    }

    if(isset($lib_paths[$name])){
        include RAILS_DIR.DS.$lib_paths[$name];
        return ;
    }

    if(isset($app_paths[$name])){
        $file_path = AkConfig::getDir('app').DS.$app_paths[$name];
        if(file_exists($file_path)){
            include $file_path;
            return ;
        }
    }

    if(isset($paths[$name])){
        include $paths[$name];
    }elseif(file_exists(DS.$name.'.php')){
        include DS.$name.'.php';
    }else{
        $underscored_name = AkInflector::underscore($name);
        if(!Ak::import($name)){
            if(strstr($name, 'Helper')){
                $file_path = AkConfig::getDir('helpers').DS.$underscored_name.'.php';
                if(!file_exists($file_path)){
                    $file_path = ACTION_PACK_DIR.DS.'helpers'.DS.$underscored_name.'.php';
                    if(!file_exists($file_path)){
                        $file_path = ACTION_PACK_DIR.DS.'helpers'.DS.'ak_'.$underscored_name.'.php';
                        if(include_once($file_path)){
                            eval('class '.$name.' extends Ak'.$name.'{}');
                            return;
                        }
                    }
                }
            }elseif(strstr($name, 'Installer')){
                $file_path = AkConfig::getDir('app_installers').DS.$underscored_name.'.php';
            }elseif(strstr($name, 'Controller')){
                $file_path = AkInflector::toControllerFilename($name);
            }
        }
    }
    if(isset($file_path) && file_exists($file_path)){
        include $file_path;
    }
}


// Including Rails static functions under the Ak:: scope
include_once ACTIVE_SUPPORT_DIR.DS.'base.php';

// Registering rails autoloader
spl_autoload_register('rails_autoload');



/**
 * Rails environment guessing.
 *
 * Rails will set environment constants which have not been defined at this point.
 * 
 * You can retrieve a list of current settings by running AkDebug::get_constants();
 *
 * If you're running a high load site you might want to fine tune this options
 * according to your environment. If you set the options implicitly you might
 * gain in performance but loose in flexibility when moving to a different
 * environment.
 *
 * If you need to customize the framework default settings or specify
 * internationalization options, edit the files at config/environments/*
 */


defined('PHP5')                      || define('PHP5',  version_compare(PHP_VERSION, '5',  '>=') == 1 ? true : false);
defined('PHP53')                     || define('PHP53', version_compare(PHP_VERSION, '5.3','>=') == 1 ? true : false);
defined('PHP6')                      || define('PHP6',  version_compare(PHP_VERSION, '6',  '>=') == 1 ? true : false);

defined('CONFIG_DIR')                || define('CONFIG_DIR', BASE_DIR.DS.'config');

defined('CACHE_HANDLER_PEAR')        || define('CACHE_HANDLER_PEAR',    1);
defined('CACHE_HANDLER_ADODB')       || define('CACHE_HANDLER_ADODB',   2);
defined('CACHE_HANDLER_MEMCACHE')    || define('CACHE_HANDLER_MEMCACHE',3);

defined('ACTION_MAILER_DIR')     || define('ACTION_MAILER_DIR',   RAILS_DIR.DS.'action_mailer');
defined('ACTION_PACK_DIR')       || define('ACTION_PACK_DIR',     RAILS_DIR.DS.'action_pack');
defined('ACTIVE_RECORD_DIR')     || define('ACTIVE_RECORD_DIR',   RAILS_DIR.DS.'active_record');
defined('ACTIVE_RESOURCE_DIR')   || define('ACTIVE_RESOURCE_DIR', RAILS_DIR.DS.'active_resource');
defined('ACTIVE_SUPPORT_DIR')    || define('ACTIVE_SUPPORT_DIR',  RAILS_DIR.DS.'active_support');
defined('ACTIVE_DOCUMENT_DIR')   || define('ACTIVE_DOCUMENT_DIR', RAILS_DIR.DS.'active_document');
defined('RAILS_UTILS_DIR')      || define('RAILS_UTILS_DIR',    RAILS_DIR.DS.'rails_utils');

defined('WIN')                                       || define('WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
defined('OS')                                        || define('OS', WIN ? 'WINDOWS' : 'UNIX');
defined('CAN_FORK')                                  || define('CAN_FORK', function_exists('pcntl_fork'));

defined('CLI')                       || define('CLI', php_sapi_name() == 'cli');
defined('WEB_REQUEST')               || define('WEB_REQUEST', !empty($_SERVER['REQUEST_URI']));

if(ENVIRONMENT != 'setup' && !SKIP_ENV_CONFIG){
    include_once CONFIG_DIR.DS.'environments'.DS.ENVIRONMENT.'.php';
}

defined('CACHE_HANDLER')                 || define('CACHE_HANDLER', CACHE_HANDLER_PEAR);

if (!defined('TEST_DATABASE_ON')) {
    defined('DEFAULT_DATABASE_PROFILE')  || define('DEFAULT_DATABASE_PROFILE', ENVIRONMENT);
}

// Locale settings ( you must create a file at /config/locales/ using en.php as departure point)
// Please be aware that your charset needs to be UTF-8 in order to edit the locales files
// auto will enable all the locales at config/locales/ dir
defined('AVAILABLE_LOCALES')         || define('AVAILABLE_LOCALES', 'auto');
defined('AVAILABLE_ENVIRONMENTS')    || define('AVAILABLE_ENVIRONMENTS','setup,testing,development,production,staging');
// Set these constants in order to allow only these locales on web requests
// defined('ACTIVE_RECORD_DEFAULT_LOCALES') || define('ACTIVE_RECORD_DEFAULT_LOCALES','en,es');
// defined('APP_LOCALES') || define('APP_LOCALES','en,es');
// defined('PUBLIC_LOCALES') || define('PUBLIC_LOCALES','en,es');
// defined('URL_REWRITE_ENABLED') || define('URL_REWRITE_ENABLED', true);

defined('TIME_DIFFERENCE')           || define('TIME_DIFFERENCE', 0); // Time difference from the webserver

defined('PROTOCOL')                  || define('PROTOCOL',isset($_SERVER['HTTPS']) ? 'https://' : 'http://');
defined('HOST')                      || define('HOST', !isset($_SERVER['SERVER_NAME']) ? 'localhost' :
($_SERVER['SERVER_NAME'] == 'localhost' ?
// Will force to IP4 for localhost until IP6 is supported by helpers
($_SERVER['SERVER_ADDR'] == '::1' ? '127.0.0.1' : $_SERVER['SERVER_ADDR']) :
$_SERVER['SERVER_NAME']));

// Under some circumstances like proxied requests, REQUEST_URI might include the
// host and protocol, so we need to get rid of it.
if(!defined('REQUEST_URI')){
    $__request_uri =
    (isset($_SERVER['REQUEST_URI']) ?  $_SERVER['REQUEST_URI'] :
    (isset($_SERVER['argv']) ?  $_SERVER['SCRIPT_NAME'].'?'. $_SERVER['argv'][0] :
    (isset($_SERVER['QUERY_STRING']) ? $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'] :
    $_SERVER['SCRIPT_NAME']
    )));
    if(strstr($__request_uri, PROTOCOL)){
        $__request_uri = str_replace(PROTOCOL.HOST, '', $__request_uri);
    }
    define('REQUEST_URI', $__request_uri);
    unset($__request_uri);
}

defined('DEBUG')                 || define('DEBUG', ENVIRONMENT == 'production' ? 0 : 1);

defined('APP_DIR')               || define('APP_DIR',             BASE_DIR.DS.'app');
defined('PUBLIC_DIR')            || define('PUBLIC_DIR',          BASE_DIR.DS.'public');
defined('TEST_DIR')              || define('TEST_DIR',            BASE_DIR.DS.'test');
defined('SCRIPT_DIR')            || define('SCRIPT_DIR',          BASE_DIR.DS.'script');
defined('APP_VENDOR_DIR')        || define('APP_VENDOR_DIR',      BASE_DIR.DS.'vendor');
defined('APP_LIB_DIR')           || define('APP_LIB_DIR',         BASE_DIR.DS.'lib');
defined('TASKS_DIR')             || define('TASKS_DIR',           APP_LIB_DIR.DS.'tasks');

defined('APIS_DIR')              || define('APIS_DIR',            APP_DIR.DS.'apis');
defined('MODELS_DIR')            || define('MODELS_DIR',          APP_DIR.DS.'models');
defined('CONTROLLERS_DIR')       || define('CONTROLLERS_DIR',     APP_DIR.DS.'controllers');
defined('VIEWS_DIR')             || define('VIEWS_DIR',           APP_DIR.DS.'views');
defined('HELPERS_DIR')           || define('HELPERS_DIR',         APP_DIR.DS.'helpers');

defined('APP_PLUGINS_DIR')       || define('APP_PLUGINS_DIR',     APP_VENDOR_DIR.DS.'plugins');
defined('APP_INSTALLERS_DIR')    || define('APP_INSTALLERS_DIR',  APP_DIR.DS.'installers');

defined('PLUGINS_DIR')           || define('PLUGINS_DIR', APP_VENDOR_DIR.DS.'plugins');
defined('PLUGINS')               || define('PLUGINS', 'auto');

defined('TMP_DIR')               || define('TMP_DIR', Ak::get_tmp_dir_name());
defined('COMPILED_VIEWS_DIR')    || define('COMPILED_VIEWS_DIR', TMP_DIR.DS.'views');
defined('CACHE_DIR')             || define('CACHE_DIR', TMP_DIR.DS.'cache');

defined('DEFAULT_LAYOUT')        || define('DEFAULT_LAYOUT', 'application');

defined('CONTRIB_DIR')           || define('CONTRIB_DIR', RAILS_UTILS_DIR.DS.'contrib');
defined('LIB_DIR')               || define('LIB_DIR',     RAILS_DIR);

defined('VENDOR_DIR')            || define('VENDOR_DIR',  CONTRIB_DIR);
defined('DOCS_DIR')              || define('DOCS_DIR',    BASE_DIR.DS.'docs');

defined('CONFIG_INCLUDED')       || define('CONFIG_INCLUDED',true);
defined('FW')                    || define('FW',true);


if(ENVIRONMENT != 'setup'){
    defined('UPLOAD_FILES_USING_FTP')    || define('UPLOAD_FILES_USING_FTP', !empty($ftp_settings));
    defined('READ_FILES_USING_FTP')      || define('READ_FILES_USING_FTP', false);
    defined('DELETE_FILES_USING_FTP')    || define('DELETE_FILES_USING_FTP', !empty($ftp_settings));
    defined('FTP_AUTO_DISCONNECT')       || define('FTP_AUTO_DISCONNECT', !empty($ftp_settings));

    if(!empty($ftp_settings)){
        defined('FTP_PATH')              || define('FTP_PATH', $ftp_settings);
        unset($ftp_settings);
    }
}


if(!CLI && WEB_REQUEST){

    defined('SITE_URL_SUFFIX')   || define('SITE_URL_SUFFIX', '/');

    defined('AUTOMATIC_SSL_DETECTION')   || define('AUTOMATIC_SSL_DETECTION', 1);
    defined('REMOTE_IP')                 || define('REMOTE_IP',preg_replace('/,.*/','',((!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : (!empty($_ENV['HTTP_X_FORWARDED_FOR']) ? $_ENV['HTTP_X_FORWARDED_FOR'] : (empty($_ENV['REMOTE_ADDR']) ? false : $_ENV['REMOTE_ADDR']))))));
    defined('SERVER_STANDARD_PORT')      || define('SERVER_STANDARD_PORT', PROTOCOL == 'https://' ? '443' : '80');

    $_ak_port = ($_SERVER['SERVER_PORT'] != SERVER_STANDARD_PORT)
    ? (empty($_SERVER['SERVER_PORT']) ? '' : ':'.$_SERVER['SERVER_PORT']) : '';

    if(isset($_SERVER['HTTP_HOST']) && strstr($_SERVER['HTTP_HOST'],':')){
        $_ak_port = substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'],':'));
    }

    $_ak_suffix = '';
    if(defined('SITE_HTPSS_URL_SUFFIX') && isset($_SERVER['HTTPS'])){
        $_ak_suffix = SITE_HTPSS_URL_SUFFIX;
        $_ak_port = strstr(SITE_HTPSS_URL_SUFFIX,':') ? '' : $_ak_port;
    }elseif(defined('SITE_URL_SUFFIX') && SITE_URL_SUFFIX != ''){
        $_ak_suffix = SITE_URL_SUFFIX;
        $_ak_port = strstr(SITE_URL_SUFFIX,':') ? '' : $_ak_port;
    }

    if(!defined('SITE_URL')){
        defined('SITE_URL') || define('SITE_URL', trim(PROTOCOL.HOST, '/').$_ak_port.$_ak_suffix);
        defined('URL')       || define('URL', SITE_URL);
    }else{
        if(AUTOMATIC_SSL_DETECTION){
            defined('URL')   || define('URL', str_replace(array('https://','http://'),PROTOCOL, SITE_URL).$_ak_port.$_ak_suffix);
        }else{
            defined('URL')   || define('URL', SITE_URL.$_ak_port.$_ak_suffix);
        }
    }

    defined('CURRENT_URL')           || define('CURRENT_URL', substr(SITE_URL,0,strlen($_ak_suffix)*-1).REQUEST_URI);
    defined('SERVER_PORT')           || define('SERVER_PORT', empty($_ak_port) ? SERVER_STANDARD_PORT : trim($_ak_port,':'));

    unset($_ak_suffix, $_ak_port);
    defined('COOKIE_DOMAIN')                 || define('COOKIE_DOMAIN', HOST);
    defined('INSECURE_APP_DIRECTORY_LAYOUT') || define('INSECURE_APP_DIRECTORY_LAYOUT', false);

    if(!defined('ASSET_URL_PREFIX')){
        defined('ASSET_URL_PREFIX')  || define('ASSET_URL_PREFIX', INSECURE_APP_DIRECTORY_LAYOUT ? SITE_URL_SUFFIX.str_replace(array(BASE_DIR,'\\','//'),array('','/','/'), PUBLIC_DIR) : SITE_URL_SUFFIX);
    }

}else{
    defined('PROTOCOL')          || define('PROTOCOL',        'http://');
    defined('REMOTE_IP')         || define('REMOTE_IP',       '127.0.0.1');
    defined('SITE_URL')          || define('SITE_URL',        'http://localhost');
    defined('URL')               || define('URL',             'http://localhost/');
    defined('CURRENT_URL')       || define('CURRENT_URL',     'http://localhost/');
    defined('COOKIE_DOMAIN')     || define('COOKIE_DOMAIN',   HOST);
    defined('ASSET_URL_PREFIX')  || define('ASSET_URL_PREFIX','');
    defined('SITE_URL_SUFFIX')   || define('SITE_URL_SUFFIX', '/');
}

defined('CALLED_FROM_LOCALHOST')                     || define('CALLED_FROM_LOCALHOST', REMOTE_IP == '127.0.0.1');
defined('SESSION_HANDLER')                           || define('SESSION_HANDLER', 4);
defined('SESSION_EXPIRE')                            || define('SESSION_EXPIRE', 600);
defined('SESSION_NAME')                              || define('SESSION_NAME', '_sess_id');
defined('ASSET_HOST')                                || define('ASSET_HOST','');
defined('DEV_MODE')                                  || define('DEV_MODE',        ENVIRONMENT == 'development');
defined('TEST_MODE')                                 || define('TEST_MODE',       ENVIRONMENT == 'testing');
defined('STAGING_MODE')                              || define('STAGING_MODE',    ENVIRONMENT == 'staging');
defined('PRODUCTION_MODE')                           || define('PRODUCTION_MODE', ENVIRONMENT == 'production');
defined('AUTOMATICALLY_UPDATE_LANGUAGE_FILES')       || define('AUTOMATICALLY_UPDATE_LANGUAGE_FILES', DEV_MODE);
defined('ENABLE_PROFILER')                           || define('ENABLE_PROFILER', false);
defined('PROFILER_GET_MEMORY')                       || define('PROFILER_GET_MEMORY',false);

// ERROR LOGGING
defined('LOG_DIR')                                   || define('LOG_DIR', BASE_DIR.DS.'log');
defined('LOG_EVENTS')                                || define('LOG_EVENTS', DEV_MODE && is_writable(LOG_DIR));

defined('ROUTES_MAPPING_FILE')                       || define('ROUTES_MAPPING_FILE', CONFIG_DIR.DS.'routes.php');
defined('CHARSET')                                   || define('CHARSET', 'UTF-8');
defined('ACTION_CONTROLLER_DEFAULT_REQUEST_TYPE')    || define('ACTION_CONTROLLER_DEFAULT_REQUEST_TYPE', 'web_request');
defined('ACTION_CONTROLLER_DEFAULT_ACTION')          || define('ACTION_CONTROLLER_DEFAULT_ACTION', 'index');
defined('ACTION_CONTROLLER_PERFORM_CACHING')         || define('ACTION_CONTROLLER_PERFORM_CACHING', PRODUCTION_MODE);
defined('ACTION_CONTROLLER_PAGE_CACHE_DIR')          || define('ACTION_CONTROLLER_PAGE_CACHE_DIR', PUBLIC_DIR.DS.'cache');

defined('FRAMEWORK_LANGUAGE')                        || define('FRAMEWORK_LANGUAGE', 'cn');
defined('AUTOMATIC_CONFIG_VARS_ENCRYPTION')          || define('AUTOMATIC_CONFIG_VARS_ENCRYPTION', false);
defined('VERBOSE_INSTALLER')                         || define('VERBOSE_INSTALLER', DEV_MODE);
defined('HIGH_LOAD_MODE')                            || define('HIGH_LOAD_MODE', false);
defined('AUTOMATIC_SESSION_START')                   || define('AUTOMATIC_SESSION_START', !HIGH_LOAD_MODE);
defined('APP_NAME')                                  || define('APP_NAME', 'Application');
defined('JAVASCRIPT_DEFAULT_SOURCES')                || define('JAVASCRIPT_DEFAULT_SOURCES','prototype,event_selectors,scriptaculous');
defined('DATE_HELPER_DEFAULT_PREFIX')                || define('DATE_HELPER_DEFAULT_PREFIX', 'date');
defined('JAVASCRIPT_PATH')                           || define('JAVASCRIPT_PATH', PUBLIC_DIR.DS.'javascripts');
defined('DEFAULT_LOCALE_NAMESPACE')                  || define('DEFAULT_LOCALE_NAMESPACE', null);

// Use setColumnName if available when using set('column_name', $value);
defined('ACTIVE_RECORD_INTERNATIONALIZE_MODELS_BY_DEFAULT')  || define('ACTIVE_RECORD_INTERNATIONALIZE_MODELS_BY_DEFAULT',    false);
defined('ACTIVE_RECORD_ENABLE_AUTOMATIC_SETTERS_AND_GETTERS')|| define('ACTIVE_RECORD_ENABLE_AUTOMATIC_SETTERS_AND_GETTERS',  false);
defined('ACTIVE_RECORD_ENABLE_CALLBACK_SETTERS')             || define('ACTIVE_RECORD_ENABLE_CALLBACK_SETTERS', ACTIVE_RECORD_ENABLE_AUTOMATIC_SETTERS_AND_GETTERS);
defined('ACTIVE_RECORD_ENABLE_CALLBACK_GETTERS')             || define('ACTIVE_RECORD_ENABLE_CALLBACK_GETTERS', ACTIVE_RECORD_ENABLE_AUTOMATIC_SETTERS_AND_GETTERS);

defined('ACTIVE_RECORD_ENABLE_PERSISTENCE')                  || define('ACTIVE_RECORD_ENABLE_PERSISTENCE', ENVIRONMENT != 'testing');
defined('ACTIVE_RECORD_CACHE_DATABASE_SCHEMA')               || define('ACTIVE_RECORD_CACHE_DATABASE_SCHEMA', ACTIVE_RECORD_ENABLE_PERSISTENCE && ENVIRONMENT != 'development');
defined('ACTIVE_RECORD_CACHE_DATABASE_SCHEMA_LIFE')          || define('ACTIVE_RECORD_CACHE_DATABASE_SCHEMA_LIFE', 300);
defined('ACTIVE_RECORD_VALIDATE_TABLE_NAMES')                || define('ACTIVE_RECORD_VALIDATE_TABLE_NAMES', true);
defined('ACTIVE_RECORD_SKIP_SETTING_ACTIVE_RECORD_DEFAULTS') || define('ACTIVE_RECORD_SKIP_SETTING_ACTIVE_RECORD_DEFAULTS', false);
defined('NOT_EMPTY_REGULAR_EXPRESSION')                      || define('NOT_EMPTY_REGULAR_EXPRESSION','/.+/');
defined('EMAIL_REGULAR_EXPRESSION')                          || define('EMAIL_REGULAR_EXPRESSION',"/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i");
defined('NUMBER_REGULAR_EXPRESSION')                         || define('NUMBER_REGULAR_EXPRESSION',"/^[0-9]+$/");
defined('PHONE_REGULAR_EXPRESSION')                          || define('PHONE_REGULAR_EXPRESSION',"/^([\+]?[(]?[\+]?[ ]?[0-9]{2,3}[)]?[ ]?)?[0-9 ()\-]{4,25}$/");
defined('DATE_REGULAR_EXPRESSION')                           || define('DATE_REGULAR_EXPRESSION',"/^(([0-9]{1,2}(\-|\/|\.| )[0-9]{1,2}(\-|\/|\.| )[0-9]{2,4})|([0-9]{2,4}(\-|\/|\.| )[0-9]{1,2}(\-|\/|\.| )[0-9]{1,2})){1}$/");
defined('IP4_REGULAR_EXPRESSION')                            || define('IP4_REGULAR_EXPRESSION',"/^((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])$/");
defined('POST_CODE_REGULAR_EXPRESSION')                      || define('POST_CODE_REGULAR_EXPRESSION',"/^[0-9A-Za-z  -]{2,9}$/");

defined('HAS_AND_BELONGS_TO_MANY_CREATE_JOIN_MODEL_CLASSES') || define('HAS_AND_BELONGS_TO_MANY_CREATE_JOIN_MODEL_CLASSES' ,true);
defined('HAS_AND_BELONGS_TO_MANY_JOIN_CLASS_EXTENDS')        || define('HAS_AND_BELONGS_TO_MANY_JOIN_CLASS_EXTENDS' , 'ActiveRecord');

defined('DEFAULT_TEMPLATE_ENGINE')                           || define('DEFAULT_TEMPLATE_ENGINE', 'sintags');
defined('TEMPLATE_SECURITY_CHECK')                           || define('TEMPLATE_SECURITY_CHECK', false);
defined('PHP_CODE_SANITIZER_FOR_TEMPLATE_HANDLER')           || define('PHP_CODE_SANITIZER_FOR_TEMPLATE_HANDLER', 'AkPhpCodeSanitizer');


defined('URL_DEBUG_REQUEST')                 || define('URL_DEBUG_REQUEST', !empty($_GET['debug']));
defined('ENCLOSE_RENDERS_WITH_DEBUG_SPANS')  || define('ENCLOSE_RENDERS_WITH_DEBUG_SPANS', DEBUG && URL_DEBUG_REQUEST);
defined('FORCE_TEMPLATE_COMPILATION')        || define('FORCE_TEMPLATE_COMPILATION', DEBUG && !empty($_GET['recompile']));

defined('DEFAULT_LOCALE_NAMESPACE')          || define('DEFAULT_LOCALE_NAMESPACE', null);

defined('PLUGINS_MAIN_REPOSITORY')           || define('PLUGINS_MAIN_REPOSITORY', 'http://svn.rails.org/plugins');
defined('PLUGINS_REPOSITORY_DISCOVERY_PAGE') || define('PLUGINS_REPOSITORY_DISCOVERY_PAGE', 'http://www.rails.org/wiki/plugins');
defined('TESTING_NAMESPACE')                 || define('TESTING_NAMESPACE', APP_NAME);

defined('ACTION_MAILER_DELIVERY_METHOD')                 || define('ACTION_MAILER_DELIVERY_METHOD', TEST_MODE ? 'test' : 'php');
defined('ACTION_MAILER_DEFAULT_CHARSET')                 || define('ACTION_MAILER_DEFAULT_CHARSET', CHARSET);
defined('ACTION_MAILER_EOL')                             || define('ACTION_MAILER_EOL', "\r\n");
defined('ACTION_MAILER_EMAIL_REGULAR_EXPRESSION')        || define('ACTION_MAILER_EMAIL_REGULAR_EXPRESSION', trim(EMAIL_REGULAR_EXPRESSION, '/^$i'));
defined('ACTION_MAILER_RFC_2822_DATE_REGULAR_EXPRESSION')|| define('ACTION_MAILER_RFC_2822_DATE_REGULAR_EXPRESSION', "(?:(Mon|Tue|Wed|Thu|Fri|Sat|Sun), *)?(\d\d?) (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) (\d\d\d\d) (\d{2}:\d{2}(?::\d\d)) (UT|GMT|EST|EDT|CST|CDT|MST|MDT|PST|PDT|[A-Z]|(?:\+|\-)\d{4})");
defined('ACTION_MAILER_CHARS_NEEDING_QUOTING_REGEX')     || define('ACTION_MAILER_CHARS_NEEDING_QUOTING_REGEX', "/[\\000-\\011\\013\\014\\016-\\037\\177-\\377]/");
defined('ACTION_MAILER_EMULATE_IMAP_8_BIT')              || define('ACTION_MAILER_EMULATE_IMAP_8_BIT', true);
defined('CLASS_EXTENDER_ENABLE_CACHE')                   || define('CLASS_EXTENDER_ENABLE_CACHE', !DEV_MODE);

defined('GENERATE_HELPER_FUNCTIONS_FOR_NAMED_ROUTES')    || define('GENERATE_HELPER_FUNCTIONS_FOR_NAMED_ROUTES',true);
defined('AUTOMATICALLY_ACCEPT_KNOW_FORMATS')             || define('AUTOMATICALLY_ACCEPT_KNOW_FORMATS', true);

defined('OPTIONAL')                     || define('OPTIONAL',   'OPTIONAL');
defined('COMPULSORY')                   || define('COMPULSORY', 'COMPULSORY');
defined('ANY')                          || define('ANY',        'ANY');

defined('ENABLE_URL_REWRITE')        || define('ENABLE_URL_REWRITE',     true);
defined('URL_REWRITE_ENABLED')       || define('URL_REWRITE_ENABLED',    true);
defined('DEFAULT_CONTROLLER')        || define('DEFAULT_CONTROLLER', 'page');
defined('DEFAULT_ACTION')            || define('DEFAULT_ACTION', 'index');

defined('IMAGE_DRIVER')              || define('IMAGE_DRIVER', 'GD');


defined('ACTION_WEBSERVICE_CACHE_REMOTE_METHODS') || define('ACTION_WEBSERVICE_CACHE_REMOTE_METHODS', PRODUCTION_MODE);

defined('SET_UTF8_ON_MYSQL_CONNECT') || define('SET_UTF8_ON_MYSQL_CONNECT', true);
defined('IN_SAE') || define('IN_SAE',false);


/**
 * Other settings
 */

// IIS does not provide a valid REQUEST_URI so we need to guess it from the script name + query string
$_SERVER['REQUEST_URI'] = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'].(( isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')));

$ADODB_CACHE_DIR = CACHE_DIR;

if(!IN_SAE)ini_set('arg_separator.output', '&');
ini_set('include_path', (CONTRIB_DIR.DS.'pear'.PATH_SEPARATOR.ini_get('include_path')));
if(!IN_SAE)ini_set('session.name', SESSION_NAME);


class ArgumentException         extends Exception {}
class ControllerException       extends Exception{}
class UnknownActionException    extends ControllerException{}
class ForbiddenActionException  extends ControllerException{}
class DispatchException         extends ControllerException{}
class NotAcceptableException    extends ControllerException{}
class BadRequestException       extends ControllerException{}
class MissingTemplateException  extends ControllerException{}
class RouteException extends ControllerException{}
class RouteDoesNotMatchRequestException extends RouteException{}
class RouteDoesNotMatchParametersException extends RouteException{}
class AkDatabaseConnectionException extends Exception{}
class RecordNotFoundException extends Exception{
    public $status = 404;
}