<?php

namespace Yee\Managers;

use \Yee\Yee;

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


class RoutingCacheManager
{
	const DEFAULT_REFRESH_TIME = 5 * 60; // 5 minutes
	
    protected $cacheDir;
    protected $rootDir;
   
    protected $content = "";

    function __construct()
    {
        $app = \Yee\Yee::getInstance();
        
		$doReload = true;
        $options = $app->config( 'routing' );
		if (isset($options['cacheFile']) && file_exists($options['cacheFile'])) {
			if (!isset($options['refreshTime'])) {
				$option['refreshTime'] = self::DEFAULT_REFRESH_TIME;
			}
			$now = time();
			$fileModTime = filemtime($options['cacheFile']);
			$timePassed = $now - $fileModTime;
			if ($timePassed < $options['refreshTime']) {
				$doReload = false;
			}
		}
		
		if ($doReload) {
			
	        $date = date("Y-m-d h:i:s");
	        $this->content = <<<EOD
<?php


/**
 * Generated with RoutingCacheManager
 *
 * on {$date}
 */

\$app = Yee\Yee::getInstance();


EOD;

	        $tmp = preg_split('|/vendor/|', __DIR__);
	        $this->rootDir = $tmp[0] . "/"; 
	        if(!isset($options['cache'])){
	            $options['cache'] = $tmp[0] . '/cache/routing';
	        }
	        @mkdir($options['cache'], 0777, true);
	        $this->cacheDir = $options['cache'];
	        if(isset($options['controller'])){
	            if(is_array($options['controller'])){
	                $controllers = $options['controller'];
	            }else{
	                $controllers = array($options['controller']);
	            }
	        }else{
	            $controllers = array($tmp[0] . '/app/controller');
	        }
	        if(count($controllers)){
	            foreach($controllers as $controllerPath){
	                $this->loadPath(realpath($controllerPath));
	            }
	        }
        
			file_put_contents($options['cacheFile'], $this->content);
		}
		
		require_once($options['cacheFile']);
    }


    /*******************
     *                 *
     * CACHE FUNCTIONS *
     *                 *
     *******************/

    /**
     * gets the full path and the name of cache file
     *
     * @param $class
     *
     * @return string
     */
    protected function cacheFile($class)
    {
         return $this->cacheDir . '/' . md5($class) . '.php';
    }

    /**
     * This method writes the cache content into cache file
     *
     * @param $class
     * @param $content
     *
     * @return string
     */
    protected function writeCache($class, $content)
    {
        $fileName = $this->cacheFile($class);
        file_put_contents($fileName, $content);

        return $fileName;
    }

    /**
     * Generates new file and return cachefile name
     * @param $classFile
     *
     * @return string
     */
    protected function updateAndGetCacheFileName($classFile)
    {
        $className = $this->className($classFile);
		
		$this->content .= "/* File: " . $classFile . "*/\n";
		$this->content .= $this->processClass($classFile);
		$this->content .= "\n";

        //echo $className;
        
        /*
        if( $this->hasChanged($classFile) )
        {
            $content = $this->processClass($classFile);
            
            $this->content .= '' . $content;
            //$this->writeCache($className, $content);
            //$this->setDateFromClassFile($classFile);
        }
        */
        return $this->cacheFile($className);
    }

    /**
     * Process the cachefile, in PHP require is enough
     *
     * @param $classFile
     *
     * @throws \Exception
     */
    protected function processCache($classFile)
    {
        $this->updateAndGetCacheFileName($classFile);
    }

    /**
     * Indicates if the classfile has a diferent modify time that cache file
     *
     * @param $classFile
     *
     * @return bool
     */
    protected function hasChanged($classFile)
    {
        $className = $this->className($classFile);
        $cacheFile = $this->cacheFile($className);
        $cacheDate = file_exists($cacheFile) ? filemtime($cacheFile) : 0;
        $fileDate  = filemtime($classFile);

        return ($fileDate != $cacheDate); 
    }


    /*******************
     *                 *
     * CLASS FUNCTIONS *
     *                 *
     *******************/

    /**
     * Sets the modify time of cache file according to classfile
     *
     * @param $classFile
     */
    protected function setDateFromClassFile($classFile)
    {
        $className = $this->className($classFile);
        $cacheFile = $this->cacheFile($className);
        $fileDate  = filemtime($classFile);
        touch($cacheFile, $fileDate);
    }

    /**
     * Extracts the className through the classfile name
     *
     * @param $classFile
     *
     * @return mixed
     * @throws \Exception
     */
    protected function className($classFile)
    {
        $classFile = str_replace($this->rootDir, "", $classFile);
        $className = str_replace(array(".php", "/"), array("", "_"), $classFile);

        return $className;
    }

    /**
     * @param $classFile
     *
     * @return string
     * @throws \Exception
     */
    protected function processClass($classFile)
    {
        $this->routes[$classFile] = array();
        
		$namespace = '';
        $className = '';
		$result = '';
        $content   = file_get_contents($classFile);
		
        preg_match_all('/namespace\s+(([\w]*(\\\))*[\w]*)\s*/s', $content, $mnamespace, PREG_SET_ORDER);
        $namespace = $mnamespace[0][1];
        if (!$namespace){
            throw new \Exception(sprintf('namespace not found in %s', $classFile));
        }
		$namespace = str_replace('\\', '\\\\', $namespace);

        preg_match_all('/class\s+(\w*)\s*(extends\s+)?([^{])*/s', $content, $mclass, PREG_SET_ORDER);
        $className = $mclass[0][1];
        if (!$className){
            throw new \Exception(sprintf('class not found in %s', $classFile));
        }

        preg_match_all('|(/\*\*[^{]*?{)|', $content, $match, PREG_PATTERN_ORDER);

        foreach ($match[0] as $k => $m) {
            $function = '?';
            $comments = '';
            if (!substr_count($m, 'class')) {
                $function = substr_count($m, 'function') ? 'yes' : 'no';
                if ($function == 'yes') {
                    preg_match_all('/(\/\*\*.*\*\/)/s', $m, $mc, PREG_PATTERN_ORDER);
                    $comments = nl2br($mc[0][0]);
                    preg_match_all('/\*\/\s+(public\s+)?(static\s+)?function\s+([^\(]*)\(/s', $m, $mf, PREG_SET_ORDER);
                    $functionName = $mf[0][3];
                    
                    preg_match_all("/\*\s+@Route\s*\('([^']*)'\)/s", $comments, $params, PREG_SET_ORDER);
                    $route = $params[0][1];
                    
                    $routes = $params;
                    
                    preg_match_all("/\*\s+@Method\s*\('([^']*)'\)/s", $comments, $params, PREG_SET_ORDER);
                    $method = isset($params[0][1]) ? strtoupper($params[0][1]) : 'GET';
                    preg_match_all("/\*\s+@Name\s*\('([^']*)'\)/s", $comments, $params, PREG_SET_ORDER);
                    $name = strtolower($params[0][1]);
                    if ($className != "" && $functionName != "" )
                    {	
                        foreach( $routes as $aRoute )
                        {
                            $result .= sprintf(
    	                        '$app->map("%s", "%s::___%s")->via("%s")->name("%s");' . PHP_EOL,
    	                        $aRoute[1], '\\\\' . $namespace. '\\\\' . $className, $functionName, str_replace(',','","',$method), $name 
    	                    );
                        }
                    }
                }
            }
        }
		
		return $result;
    }

    /******************
     *                *
     * PATH FUNCTIONS *
     *                *
     ******************/

    /**
     * Main method to invoke the routing system
     *
     * @param $phpFile
     */
    protected function loadRoute($phpFile)
    {
        $this->processCache($phpFile);
    }

    /**
     * Reads the contents of this dir and returns only dirs
     * that have first letter capitalized
     *
     * @return array
     */
    protected static function readdir($dir)
    {
        $entries = array();
        foreach (scandir($dir) as $entry) {
            if(($entry != '.') && ($entry != '..')){
                $current = "$dir/$entry";
                if($current != $dir){
                    if(is_dir($current)){
                        $aux = self::readdir($current);
                        $entries = array_merge($entries, $aux);
                    }else{
                        if(preg_match("/\w*?Controller.php/", $entry)){
                            $entries[] = $current;
                        }
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * Load all Controller classes in an entire path
     *
     * @param $path
     */
    protected function loadPath($path)
    {
        $controllers = self::readdir($path);
        if(count($controllers)){
            foreach($controllers as $controller){
                $this->loadRoute($controller);
            }
        }
    }


}