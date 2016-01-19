<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\NotFoundException;
use RedBeanPHP\R;

// Routes
$app->get('/[{name}]', function (Request $request, Response $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

/**
 * Initial route
 */
$app->get('/api/v1/', function (Request $request, Response $response) {
    $response->getBody()->write('Welcome to my first API !');

    return $response;
});


/**
 *  CRUD Postit
 */
$app->get('/api/v1/postits', function (Request $request, Response $response) {
    try{
        $postits = R::find('postit');

        $response->withStatus(200);
        $response->withAddedHeader('Content-Type', 'application/json');

        if($postits){
            $response->getBody()->write(json_encode(array("data" => R::exportAll($postits))));
        }else{
            $response->getBody()->write(json_encode('No Post-it'));
        }
    }catch(NotFoundException $e){;
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
    }catch(NotFoundException $e){;
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
    }catch(NotFoundException $e){;
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
    }catch(NotFoundException $e){;
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
    }catch(NotFoundException $e){;
        $response->withStatus(404);
        $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
    }


    return $response;
});

/**
 *  Recherche POSTIT
 */
$app->get('/api/v1/postits/search/{keys}', function (Request $request, Response $response, $args) {
    try{

        if(true){

            $postits = R::getAll('SELECT * FROM postit WHERE title LIKE :keys OR content LIKE :keys', array(':keys' => '%'.$args['keys'].'%'));

            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');

            if($postits){
                $response->getBody()->write(json_encode(array("data" => $postits)));
            }else{
                $response->getBody()->write(json_encode('No Post-it'));
            }
        }else{
            $e = new NotFoundException($request, $response);
            $response->withStatus(404);
            $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
        }
    }catch(NotFoundException $e){;
        $response->withStatus(404);
        $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
    }

    return $response;
});

/**
 * Filtre POSTIT
 */
$app->get('/api/v1/postits/search/{column}/{order}', function (Request $request, Response $response, $args) {
    try{
        if(($args['column'] == 'title' or $args['column'] == 'color' or $args['column'] == 'date')
            && ($args['order'] == 'asc' or $args['order'] == 'desc')){

            $postits = R::findAll('postit', ' ORDER BY '.$args['column'].' '.$args['order']);

            $response->withStatus(200);
            $response->withAddedHeader('Content-Type', 'application/json');

            if($postits){
                $response->getBody()->write(json_encode(array("data" => R::exportAll($postits))));
            }else{
                $response->getBody()->write(json_encode('No Post-it'));
            }
        }else{
            $e = new NotFoundException($request, $response);
            $response->withStatus(404);
            $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
        }
    }catch(NotFoundException $e){;
        $response->withStatus(404);
        $response->getBody()->write('{"error":{"text":'. $e->getMessage() .'}}');
    }

    return $response;
});