<?php 

namespace Tests;

/**
* 
*/
class  LoginControllerTest extends \Tests\WebTestCase
{
	public function testindexAction($value='')
	{
		$response = $this->runApp('POST', '/users/login');
		$this->assertEquals(200, $response->getStatus());
	}
	
}




