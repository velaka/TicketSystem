<?php 

namespace Yee\Libraries\Connectors;

use Redis;

class RedisConnector {

    /**
     * Indicator for which redis server will execute the command
     */
    const REDIS_MASTER = 0;
    const REDIS_SLAVE = 1;

    /**
     * Static instance of self
     *
     * @var RedisConnector
     */
    protected static $_instance;
    
    /**
     * Redis cluster instances
     *
     * @var redis
     */
    protected $_redis;
     
    /**
     * Connection data
     *
     * @var host array|string
     */
    protected $hosts;
    
    /**
     * Connection indicator
     *
     * @var boolean
     */
    protected $isConnected = false;

    /**
     * Available PHP Redis build-in functions which can be used to retrieve values
     *
     * @var array
     */
    protected static $redisFuncGet = array(
        'mGet', 'getMultiple', 'getSet', 'dbSize', 'lastSave', 'getRange', 'strLen', 'ttl', 'keys', 'getKeys', 'exists'
    );

    /**
     * Available PHP Redis build-in functions which can be used to set values
     *
     * @var array
     */
    protected static $redisFuncSet = array(
        'append', 'getSet', 'incr', 'incrBy', 'incrByFloat', 'decr', 'decrBy'
    );
    
    /**
     * @param array $host
     */
    public function __construct( $hosts )
    {
        $this->hosts        = $hosts;
        self::$_instance    = $this;
    }

    /**
     * Method which connects to redis service or cluster.
     *
     * @return boolean
     */
    public function connect()
    {
        try {

            if ( $this->isConnected ) {
                return $this->isConnected;
            }

            if ( !isset( $this->hosts ) ) {
                return false;
            }

            foreach ( $this->hosts as $node ) {

                $redis      = new \Redis();

                $host       = $node['host'];
                $port       = $node['port'] ?? null;
                $timeout    = $node['timeout'] ?? null;

                $connected = $redis->pconnect( $host, $port, $timeout );

                if ( $connected == true ){
                    
                    if ( isset( $node['password'] ) && !empty( $node['password'] ) ) {
                        $hasAuthenticated = $redis->auth( $node['password'] );
                    }

                    if ( isset( $node['database'] )) {
                        $hasSelected = $redis->select( $node['database'] );
                    }

                    $replicationInfo = $redis->info( 'REPLICATION' );

                    $role = $replicationInfo['role'];

                    if ( $role == 'master' ) {
                    
                        $this->_redis['master'] = $redis;

                    } else if ( $role == 'slave' ) {

                        $this->_redis['slave'] = $redis;

                    }
                }
            }

            return $this->isConnected = true;

        } catch ( \RedisException $e ) {
            $e->getMessage();
            return false;
        }
    }

    /**
     * Method which returns the RedisConnector itself.
     *
     * @return RedisConnector
     */
    public static function getInstance()
    {
        return self::$_instance;
    }

    /**
     * Method which returns the value of the key.
     * In case of failure this method will return false.
     *
     * @param string $key
     * @return mixed
     */
    public function get( $key )
    {
        if ( !$this->connect() ){
            return false;
        }

        $value = $this->executeCommand( self::REDIS_SLAVE, 'get', [$key] );

        $unserialized = @unserialize( $value );

        return $unserialized === false ? $value : $unserialized;
    }

    /**
     * Method which sets a new key -> value.
     * 
     * By default this method will add a new key or replace the value of an existing key, 
     * to modify this behaivour one should supply the optional parameter $ifExists.
     * 
     * Optional parameters : 
     * 
     * $ifExists = TRUE: Will set a key, if it does exist FALSE: Will set the key, if it doesn't exist.
     * 
     * $timeout = numeric value greater than 0, the value is calculated in seconds.
     *
     * @param string $key
     * @param mixed $value
     * @param boolean $ifExists
     * @param int $timeout
     * @return boolean
     */
    public function set( $key, $value, $ifExists = null, $timeout = null )
    {
        if ( !$this->connect() ){
            return false;
        }

        $options = array();

        if ( $ifExists !== null ) {
            $options[] = $ifExists ? 'xx' : 'nx';
        }

        if ( $timeout !== null ) {
            $options['ex'] = (int) $timeout;
        }

        if ( is_array( $value )) {
            $value = serialize( $value );
        }

        return $this->executeCommand( self::REDIS_MASTER, 'set', [$key, $value, $options] );
    }

    /**
     * Method which executed allowed PHP Redis built-in functions.
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call( $name, $args )
    {
        if ( !$this->connect() ){
            return false;
        }

        $result = null;
        $isGet = false;
        $isSet = false;

        if (( $isGet = in_array( $name, self::$redisFuncGet )) | ( $isSet = in_array( $name, self::$redisFuncSet ))) {

            if ( $isSet ) {

                $argCount = count( $args );

                for ( $item = 0; $item < $argCount; $item++ ) {

                    $args[$item] = is_array( $args[$item] ) ? serialize( $args[$item] ) : $args[$item];

                }
            }

            $nodeType = $isSet ? self::REDIS_MASTER : self::REDIS_SLAVE;

            $result = $this->executeCommand( $nodeType, $name, $args );

            if ( $isGet ) {

                if ( is_array( $result )) {

                    $resultLength = count( $result );

                    for ( $item = 0; $item < $resultLength; $item++ ) {

                        $unserialized = @unserialize( $result[$item] );

                        $result[$item] = $unserialized === false ? $result[$item] : $unserialized;

                    }

                } else {

                    $unserialized = @unserialize( $result );

                    return $unserialized === false ? $result : $unserialized;

                }
            }
        }

         return $result;
    }

    protected function executeCommand( $nodeType, $command, $args = array() )
    {

        if ( !isset( $this->_redis['slave'] )) {
            
            $node = 'master';

        } else {
            
            $node = $nodeType === self::REDIS_SLAVE ? 'slave' : 'master';

        }

        $argCount = count( $args );

        try {

            if ( isset( $this->_redis[$node] )) {

                switch( $argCount ) {
                    case 0:
                        return $this->_redis[$node]->$command();
                    case 1:
                        return $this->_redis[$node]->$command( $args[0] );
                    case 2:
                        return $this->_redis[$node]->$command( $args[0], $args[1] );
                    case 3:
                        return $this->_redis[$node]->$command( $args[0], $args[1], $args[2] );
                    case 4:
                        return $this->_redis[$node]->$command( $args[0], $args[1], $args[2], $args[3] );
                }
            }

            return false;

        } catch ( \RedisException $e ) {

            echo $e->getMessage();

            if ( $nodeType === self::REDIS_MASTER ) {
                return false;
            }

            return $this->executeCommand( self::REDIS_MASTER, $command, $args );
        }
    }
}
