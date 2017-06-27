<?php
namespace Yee\Managers;

use Yee\Yee;
use Yee\Libraries\Database\MysqliDB;
use Yee\Libraries\Database\CassandraDB;
use Yee\Libraries\Connectors\MemcachedConnector;
use Yee\Libraries\Connectors\RedisConnector;
use App\Libraries\S3\S3;

class ConnectionManager
{
    protected $config;
    
    /**
     * @param array $options
     */
    function __construct()
    {
    	$app = \Yee\Yee::getInstance();
    	$this->config = $app->config('connection');
    	
    	foreach( $this->config as $key => $connection ) 
    	{ 
    	     if( $connection['connection.type'] == "cassandra" ) 
    	     {
    	        $app->connection[$key] = new CassandraDB( $connection['database.seeds'], $connection['database.user'], $connection['database.pass'], $connection['database.port'], $connection['database.keyspace'] );
    	     } 
    	     else if ( $connection['connection.type'] == "mysqli" ) 
    	     {
    	        $app->connection[$key] = new MysqliDB( $connection['database.host'], $connection['database.user'], $connection['database.pass'], $connection['database.name'], $connection['database.port'] );
    	     } 
    	     else if ( $connection['connection.type'] == "memcached" ) 
    	     {
    	        $app->connection[$key] = new MemcachedConnector( $connection['connection.host'] );
    	     } 
    	     else if ( $connection['connection.type'] == "redis" ) 
    	     {    
				$app->connection[$key] = new RedisConnector( $connection['connection.hosts'] );
    	     } 
    	     else if ( $connection['connection.type'] == "s3" ) 
    	     {    
    	        $app->connection[$key] = new S3( $connection['connection.access'], $connection['connection.secret'], $connection['connection.endpoint'], $connection['connection.bucket']  );
    	     }
    	}	
    }
}