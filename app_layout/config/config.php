<?php
// 项目配置及启动文�?//时区设置


// 当前项目环境设置: development, testing, 
// 会加载�?本environment目录下的�?环境}.php
defined('ENVIRONMENT') || define('ENVIRONMENT', 'production');
//设置项目可用的语�?�?逗号�?���?define('AVAILABLE_LOCALES', 'en,cn');
// Use this in order to allow only these locales on web requests
//~ define('ACTIVE_RECORD_DEFAULT_LOCALES',  AVAILABLE_LOCALES);
//~ define('APP_LOCALES',                    AVAILABLE_LOCALES);
//~ define('PUBLIC_LOCALES',                 AVAILABLE_LOCALES);
defined('URL_REWRITE_ENABLED') || define('URL_REWRITE_ENABLED', true);

defined('DS')                   || define('DS',                     DIRECTORY_SEPARATOR);
include dirname(__FILE__).DS.'environment.php';

