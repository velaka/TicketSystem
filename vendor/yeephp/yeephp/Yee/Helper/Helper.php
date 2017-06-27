<?php 
namespace Yee\Helper;

class Helper{
	
	public function getExtension( $path )
	{
		$temp = explode( '.', $path );
		$temp = array_reverse( $temp );
		return $temp[0];
	}
	
	public static function jsonHeader( $xresponse_header, $xresponse_value )
	{
	    $app = \Yee\Yee::getInstance();
	    $app->response()->header( $xresponse_header, $xresponse_value );
	    $app->response()->header( 'content-type', 'application/json' );
	    $app->response()->header( 'expires', 0 );
	}
	
	public static function version( $version )
	{
	    return "v".(int)str_ireplace( ".", "", $version );
	}
	
	public static function setSessionParams( $params )
	{
	    foreach ( $params as $key => $value )
	    {
	        $_SESSION[ $key ] = $value;
	    }    
	} 
	
	
	public static function myLogger( $message ) 
	{
	    $app = \Yee\Yee::getInstance();
	
	    $base_path = $app->config( 'cache.path' );
	
	    $f = fopen( $base_path . '/json/response.txt', 'a+' );
	
	    fwrite( $f, date('Y-m-d H:i:s').' - '.$message.PHP_EOL );
	
	    fclose( $f );
	}
	
}

