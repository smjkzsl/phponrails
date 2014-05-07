<?php

# This file is part of the PhpOnRails Framework
# (Copyright) 2010-2014 Bruce chou from bermi's  project
# See LICENSE and CREDITS for details

if(!empty($options['h']) || !empty($options['help'])){
    die(<<<HELP
Description:
    The 'rails:install' command install the PhpOnRails Framework in your system.

Example:
    rails:install

    This will isntall a copy of Rails in your system and will add the
    rails and mrails commands to the binary path.

Usage: rails:install [-db]

    -d --directory=<value>    Destination directory for installing 
                              the framework. (/var/src)
    -b --binary_path=<value>  Binary path for the commands rails and
                              mrails. (/urs/local/bin)
    -q                        Do not display verbose output

HELP
);
}


class RailsFrameworkInstaller
{
    public $options = array();
    
    public function __construct($options) {
        $this->options = $options;
    }
    
    public function install() {
        $core_dir = CORE_DIR;
        $src_path = $this->getSrcPath().'/rails';
        $bin_path = $this->getBinaryPath();

        if(is_dir($src_path)){
            if(AkConsole::promptUserVar("The directory $src_path is not empty. Do you want to override its contents? (y/n)", 'n') != 'y'){
                die("Aborted.\n");
            }
        }
        
        
        $this->ensureCanWriteOnDirectory($src_path);
        $this->ensureCanWriteOnDirectory($bin_path.'/rails');
        $this->log("Copying souce files from $core_dir to $src_path.");
        $this->run("cp -R $core_dir/ $src_path/");
        $this->log("Linking binaries");
        $this->run(array('rm '.$bin_path.'/rails',"ln -s $src_path/rails $bin_path/rails"));
        $this->run(array('rm '.$bin_path.'/mrails',"ln -s $src_path/mrails $bin_path/mrails"));
        $this->log("Done.");
    }
    
    public function getBinaryPath() {
        return (empty($this->options['b']) ? (empty($this->options['binary']) ? ('/usr/local/bin') : $this->options['binary']) : $this->options['b']);
    }
    
    public function getSrcPath() {
        return (empty($this->options['d']) ? (empty($this->options['directory']) ? ('/var/src') : $this->options['directory']) : $this->options['d']);
    }
    
    static public function ensureCanWriteOnDirectory($dir) {
        if(!is_writable(dirname($dir))){
            echo "$dir: Permission denied.\n";
            die();
        }
    }
    
    public function run($cmds) {
        $cmds = is_array($cmds) ? $cmds : array($cmds);
        foreach($cmds as $cmd){
            $this->log($cmd);
            $this->log(`$cmd`);
        }
    }
    
    public function log($message = null) {
        if(empty($this->options['q'])){
            echo $message ? $message."\n" : '';
        }
    }
}

$Installer = new RailsFrameworkInstaller($options);
$Installer->install();

