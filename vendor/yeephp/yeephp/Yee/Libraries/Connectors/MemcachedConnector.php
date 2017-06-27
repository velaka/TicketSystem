<?php 

namespace Yee\Libraries\Connectors;

class MemcachedConnector {
    
    /**
     * Static instance of self
     *
     * @var MemcachedConnector
     */
    protected static $_instance;

    /**
     * Memcache instance
     *
     * @var memcached
     */
    protected $_memcached;
     
    /**
     * Connection data
     *
     * @var host array
     */
    protected $host;
  
    protected $isConnected = false;
    
    /**
     * @param string $host
     */
    public function __construct( $host = [ ['127.0.0.1', 11211, 10] ] )
    {
        $this->host      = $host;
        self::$_instance = $this;
    }
    
    /**
     * A method to connect to memcached service
     *
     */
    public function connect()
    {
  
            $this->_memcached = new \Memcached(); 
            
            $this->_memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 10);
            $this->_memcached->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
            $this->_memcached->setOption(\Memcached::OPT_SERVER_FAILURE_LIMIT, 2);
            $this->_memcached->setOption(\Memcached::OPT_REMOVE_FAILED_SERVERS, true);
            $this->_memcached->setOption(\Memcached::OPT_RETRY_TIMEOUT, 1);
            
            $this->_memcached->addServers( $this->host );
          
            $this->isConnected = true;
    }
    
    /**
     * A method of returning the static instance to allow access to the
     * instantiated object from within another class.
     * Inheriting this class would require reloading connection info.
     *
     * @uses $db = MemcachedConnctor::getInstance();
     *
     * @return object Returns the current instance.
     */
    public static function getInstance()
    {
        return self::$_instance;
    }
    
    /**
     * A convenient Get Function.
     *
     * @param string $key 
     *
     * @return string
     */
    public function get( $key )
    {
        if( !$this->isConnected )
            $this->connect();
        
        return $this->_memcached->get( $key );
    }
  
    /**
     * A convenient Set Function.
     *
     * @param string $key
     * @param string $value
     *
     * @return 
     */
    public function set( $key, $value=NULL )
    {
        if( !$this->isConnected )
            $this->connect();
    
        return $this->_memcached->set( $key, $value );
    }

}