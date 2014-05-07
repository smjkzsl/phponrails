<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

$Installer = new AkInstaller();
$Installer->copyFilesIntoApp(dirname(__FILE__).DS.'website_files', array('relative_url' => AkConsole::promptUserVar("Relative url path for serving images on CSS files\n    (ie. /public /rails/public or /)\n hit enter if your application is served from the base of hostname\n", '/')));

echo "\nYou can now browse your documentation from localhost at: \n\n    ".SITE_URL."/docs\n\nPlease configure access settings by editing docs_controller.php\n";