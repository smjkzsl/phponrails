<?php

 // Current environment. Options are: development, testing and production
defined('ENVIRONMENT') || define('ENVIRONMENT', 'development');

// Other default settings like database can be found in ./config/**.yml
// these yaml files will be cached as php for improving performance.

// Change if Rails core files are at another location
// defined('RAILS_DIR') || define('RAILS_DIR', '/path/to/the/framework');

// Rails only shows debug messages if accessed from the localhost IP, you can manually tell
// Rails which IP's you consider to be local by editing config/environment.php

// Rails bootstrapping. Don't delete this comment as it will be used by ./mrails app:define_constants
include dirname(__FILE__).DIRECTORY_SEPARATOR.'environment.php';

