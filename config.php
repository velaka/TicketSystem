<?php
    return [

                'start_time'        => $start_time,
                'templates.path'    => __DIR__ . '/templates/',
                'debug'             => true,
                'log.enabled'       => true,
                'log.writer' => new \Yee\Log\FileLogger(
                    [
                        'path' => __DIR__.'/logs',
                        'name_format' => 'Y-m-d',
                        'message_format' => '%label% - %date% - %message%'
                    ]
                ),
                //'session' => 'php',   // php, database or memcached
                'cache.path'    => __DIR__ . '/cache',
                'cache.timeout' => 1800,
                

                // cookies 
                'cookies.encrypt'       => true,
                'cookies.secret_key'    => '123456',
                'cookies.cipher'        => MCRYPT_RIJNDAEL_256,
                'cookies.cipher_mode'   => MCRYPT_MODE_CBC,
                
                
        
                
        
                // group of parameters for routing
                'routing' => [
                    'cacheFile'     => __DIR__ . '/cache/routing/compiledRoutes.php',
                    'refreshTime'   => 5 * 60, // 5 minutes
                    'cache'         => __DIR__ . '/cache/routing',
                    'controller'    => [
                        __DIR__ . '/App/Controllers',
                    ],
                    'prefix' => null
                ],
        
                // group for conncetion parameters. This can hold anything from DB to Memcache to anything that
                // needs a connection being established
                'connection' => [
                    'user' => [
                        'connection.type'       => 'cassandra',
                        'database.user'         => '', 
                        'database.pass'         => '', 
                        'database.seeds'        => [ '10.10.10.110', '10.10.10.120', '10.10.10.130' ], 
                        'database.port'         => 9042,
                        'database.keyspace'     => '',

                    ],
                    'ticket_system' => [
                        'connection.type'       => 'mysqli',
                        'database.host'        	=> 'localhost',
                        'database.user'         => 'root',
                        'database.pass'         => '',
                        'database.port'         => 3306,
                        'database.name'     	=> 'ticket_system',
                    ],
                    'memcached' => [
                        'connection.type'       => 'memcached',
                        'connection.host'       => [ ['127.0.0.1', 11211, 50], ] 
                    ],
                    'redis' => [
                        'connection.type'       => 'redis',
                        'connection.hosts'      => [
                            [
                                // master
                                'host'          => '',
                                'port'          => 6379,
                                'password'      => '',
                                'timeout'       => 60
                            ],
                            [
                                // slave
                                'host'          => '',
                                'port'          => 6379,
                                'password'      => '',
                                'timeout'       => 60
                            ]
                        ]
                    ],
                   
                    
                ],				
				
    ];
