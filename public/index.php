<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

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

function getDB()
{
    $dbhost = "mysql";
    $dbuser = "root";
    $dbpass = "password";
    $dbname = "artev";

    $mysql_conn_string = "mysql:host=$dbhost;dbname=$dbname";
    $dbConnection = new PDO($mysql_conn_string, $dbuser, $dbpass);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbConnection;
}

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

$app->get('/api/v1/', function (Request $request, Response $response) {
    $response->getBody()->write('Welcome to my first API !');

    return $response;
});

$app->get('/api/v1/postits', function (Request $request, Response $response) {
    try{
        $db = getDB();

        $sth = $db->prepare('SELECT * FROM postit');
        $sth->execute();

        $postits = $sth->fetch(PDO::FETCH_OBJ);
        $sth->closeCursor();

        if($postits){
            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($postits));
        }else{
            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode('No Post-it'));
        }
    }catch(PDOException $e){;
        $response->withStatus(404);
        $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
    }


    return $response;
});

$app->get('/api/v1/postits/{id}', function (Request $request, Response $response, $args) {
    try{
        $db = getDB();

        $sth = $db->prepare('SELECT * FROM postit WHERE id = :id');
        $sth->bindParam(':id', $args['id']);
        $sth->execute();

        $postits = $sth->fetch(PDO::FETCH_OBJ);
        $sth->closeCursor();

        if($postits){
            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($postits));
        }else{
            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode('No Post-it'));
        }
    }catch(PDOException $e){;
        $response->withStatus(404);
        $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
    }


    return $response;
});

$app->put('/api/v1/postits/{id}', function (Request $request, Response $response, $args) {
    try{
        $db = getDB();

        $sth = $db->prepare('UPDATE postit
                        SET title = :title
                        WHERE id = :id');
        $sth->bindParam(':id', $args['id']);

        $body = $request->getBody();
        $input = json_decode($body);

        $sth->execute();

        $postits = $sth->fetch(PDO::FETCH_OBJ);
        //$sth->closeCursor();

        if($postits){
            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($postits));
        }else{
            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode('No Post-it'));
        }
    }catch(PDOException $e){;
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
