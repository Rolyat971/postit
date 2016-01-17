<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use RedBeanPHP\R;

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

R::setup('mysql:host=mysql;dbname=artev','root','password');
R::freeze(true);

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

$app->get('/api/v1/', function (Request $request, Response $response) {
    $response->getBody()->write('Welcome to my first API !');

    return $response;
});

$app->get('/api/v1/postits', function (Request $request, Response $response) {
    try{
        $postits = R::find('postit');

        $response->withStatus(200);
        $response->withAddedHeader('Content-Type', 'application/json');

        if($postits){
            $response->getBody()->write(json_encode(R::exportAll($postits)));
        }else{
            $response->getBody()->write(json_encode('No Post-it'));
        }
    }catch(ResourceNotFoundException $e){;
        $response->withStatus(404);
        $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
    }


    return $response;
});

$app->get('/api/v1/postits/{id}', function (Request $request, Response $response, $args) {
    try{
        $postits = R::findOne('postit', 'id=?', array($args['id']));

        if($postits){
            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(R::exportAll($postits)));
        }else{
            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode('No Post-it'));
        }
    }catch(ResourceNotFoundException $e){;
        $response->withStatus(404);
        $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
    }


    return $response;
});

$app->post('/api/v1/postits', function (Request $request, Response $response, $args) {
    try{
        $input = $request->getParsedBody();

        $postit = R::dispense('postit');
        $postit->title = (string)$input['title'];
        $postit->content = (string)$input['content'];
        $postit->color = (int)$input['color'];
        $id = R::store($postit);

        if($id){
            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(R::exportAll($postit)));
        }
    }catch(ResourceNotFoundException $e){;
        $response->withStatus(404);
        $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
    }


    return $response;
});

$app->put('/api/v1/postits/{id}', function (Request $request, Response $response, $args) {
    try{
        $input = $request->getParsedBody();

        $postit = R::findOne('postit', 'id=?', array($args['id']));

        if($postit){
            $postit->title = (string)$input['title'];
            $postit->content = (string)$input['content'];
            $postit->color = (int)$input['color'];
            R::store($postit);

            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(R::exportAll($postit)));
        }
    }catch(ResourceNotFoundException $e){;
        $response->withStatus(404);
        $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
    }


    return $response;
});

$app->delete('/api/v1/postits/{id}', function (Request $request, Response $response, $args) {
    try{
        $postit = R::findOne('postit', 'id=?', array($args['id']));

        if($postit){
            R::trash($postit);
            $response->withStatus(204);
        }
    }catch(ResourceNotFoundException $e){;
        $response->withStatus(404);
        $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
    }


    return $response;
});


// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
