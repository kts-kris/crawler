<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 15/01/2018
 * Time: 13:52
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as Capsule;


require_once dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';


$capsule = new Capsule;



$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'database'  => 'weixin',
    'username'  => 'root',
    'password'  => '1q2w3e!@#',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();


$app = new \Slim\App;
$app->get('/acount/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];

    $account = Capsule::table('offical_account')->where('wx_id', '=', $id)->get();

    $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
    $response->getBody()->write(json_encode(
            [
                'status'    =>  200,
                'code'      =>  '',
                'data'      =>  $account,
                'message'   =>  ''
            ]
        )
    );
    return $response;
//    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->get('/article/{id}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->get('/search/{key}/type/{type}', function (Request $request, Response $response, array $args) {
    $name = $args['key'];
    $response->getBody()->write("Hello, $name:{$args['type']}");

    return $response;
});


$app->run();