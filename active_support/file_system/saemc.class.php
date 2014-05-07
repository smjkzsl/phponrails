<?php
/* usage：
<?php
include_once 'SaeMC.class.php';// load file
SaeMCFS::set('test.php','<?php echo "hello world"?>');//save file content
SaeMCFS::include_file('test.php');//execute file
var_dump(SaeMCFS::filemtime('test.php'));//get file create time
var_dump(SaeMCFS::file_exists('test.php'));//
 ?>
 */

    class SaeMCFS {

        static public $handler;
        static public $current_file = null;
        static private $contents = array();
        static private $filemtimes = array();
	static private $filePath;
	static public $IsInited=false;
	static public function init(){
		if(self::$IsInited)return true;
		
		
		self::$filePath=$_SERVER['SERVER_NAME'] . '/' ;
		if (!(self::$handler = @(memcache_init()))) {
			//~ header("Content-Type:text/html; charset=utf-8");
			trigger_error('您的Memcache还没有初始化，请登录SAE平台进行初始化~', E_USER_ERROR);
			return false;
		}
		self::$IsInited=true;

	}
        //设置文件内容
    static public function set($filename, $content) {
	    if(!self::$IsInited)self::init();
	    if(self::file_exists($filename))
		self::unlink($filename);
            self::$handler->set(self::$filePath . $filename, time() . $content, MEMCACHE_COMPRESSED, 0);
        }
	static public function content($_filename){
		self::$current_file  =   'saemc://' . self::$filePath . $_filename;
		$_content = isset(self::$contents[$_filename]) ? self::$contents[$_filename] : self::getValue($_filename, 'content');
		return $_content;
	}
        static public function saemcFileName($_filename){
            return  'saemc://' . self::$filePath . $_filename;
        }
        //载入文件
        static public function include_file($_filename,$_vars=null) {
            self::$current_file = 'saemc://' . self::$filePath . $_filename;
            $_content = isset(self::$contents[$_filename]) ? self::$contents[$_filename] : self::getValue($_filename, 'content');
            if(!is_null($_vars))
                extract($_vars, EXTR_OVERWRITE);
  
            if (!$_content)
                trigger_error('<br /><b>SAE_Parse_error</b>: failed to open stream: No such file ' . self::$current_file, E_USER_ERROR);
            if (@(eval(' ?>' . $_content)) === false)
                self::error();
            self::$current_file = null;
            unset(self::$contents[$_filename]); //释放内存
        }
	
        static private function getValue($filename, $type='mtime') {
		if(!self::$IsInited)self::init();
            $content = self::$handler->get(self::$filePath. $filename);
            if (!$content)
                return false;
            $ret = array(
                'mtime' => substr($content, 0, 10),
                'content' => substr($content, 10)
            );
            self::$contents[$filename] = $ret['content'];
            self::$filemtimes[$filename] = $ret['mtime'];
            return $ret[$type];
        }

        //获得文件修改时间
        static public function filemtime($filename) {
            if (!isset(self::$filemtimes[$filename]))
                return self::getValue($filename, 'mtime');
            return self::$filemtimes[$filename];
        }
        //清空读取的缓存
        static public function clearCache($filename){
            if(isset(self::$contents[$filename])) unset(self::$contents[$filename]);
            if(isset(self::$filemtimes[$filename])) unset(self::$filemtimes[$filename]);
        }

        //删除文件
        static public function unlink($filename) {
            if (isset(self::$contents[$filename]))
                unset(self::$contents[$filename]);
            if (isset(self::$filemtimes[$filename]))
                unset(self::$filemtimes[$filename]);
            return self::$handler->delete(self::$filePath . $filename);
        }

        static public function file_exists($filename) {
            return self::filemtime($filename) === false ? false : true;
        }

        static function error() {
            $error = error_get_last();
            if (!is_null($error)) {
                $file = strpos($error['file'], 'eval()') !== false ? self::$current_file : $error['file'];
                trigger_error("<br /><b>SAE_error</b>:  {$error['message']} in <b>" . $file . "</b> on line <b>{$error['line']}</b><br />");
            }
        }

    }
 
?>