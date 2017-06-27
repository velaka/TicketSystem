<?php
namespace Tests;

abstract class WebTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @return \Yee\Http\Response
     */
    public function runApp($requestMethod, $requestUri)
    {
        // Create a mock environment for testing with
        $environment = \Yee\Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
				'PATH_INFO'      => $requestUri,
                'REQUEST_URI'    => $requestUri,
            ]
        );
		
        // Instantiate the application

        $app = new \Yee\Yee( require __DIR__ . '/../config.php'  );
		
		//$app->view(new \Yee\Views\Twig());

		new \Yee\Managers\RoutingCacheManager();
		
		$request  = $app->request();
		$response = $app->response();
		
		ob_start();
        $app->execute();
		ob_end_clean();
		
        return $response;
    }
}

