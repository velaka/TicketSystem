<?php 

//use App\Controller\Controller;

new Yee\Managers\ConnectionManager(); 
new Yee\Managers\RoutingCacheManager();
$app->view( new \Yee\Views\Twig());

$app->notFound(function (  ) use ($app) { 
   
    $data = [
        'error' => [
            'status' => 404,
            'title'  => "404 - Endpoint not found",
            'detail' => "The Endpoint you are looking for has not been found. Please refer to our documentation on how to use it!",
            'code'   => "104-001-404"
                ],
                'data' => [],
                'link'   => $app->request->getUrl(). $app->request->getResourceUri(),
                'localtime'   => date( 'Y-m-d H:i:s' )
                ];

    $app->response->headers->set('Content-Type', 'application/json');
    
    $app->halt(
        404,
        json_encode( $data )
    ); 
});
  
