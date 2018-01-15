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


\Workerman\Protocols\Http::header('Access-Control-Allow-Origin: *');
\Workerman\Protocols\Http::header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
\Workerman\Protocols\Http::header('Access-Control-Allow-Methods: GET, POST, PUT,OPTIONS');

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

$app->add(function ($request, $response, $next) {
    $response = $response->withHeader('Access-Control-Allow-Origin','*');
    //$response = $response->withAddedHeader('Access-Control-Allow-Origin','*');
    $response = $next($request, $response);
    $response = $response->withHeader('Access-Control-Allow-Origin','*');

    return $response;
});

$app->get('/account/{id}', function (Request $request, Response $response, array $args) {
    $id = $args['id'];
    $account = Capsule::table('offical_account')->where('wx_id', '=', $id)->get();
    $response = $response->withStatus(200)->withHeader('Content-type', 'application/json')->withHeader('Access-Control-Allow-Origin','*');
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

$app->group('/article', function () {
    $this->get('/list/type/{type}[/{pageSize}[/{page}]]', function ($request, $response, $args) {
        $type = $args['type'];
        $pageSize = isset($args['pageSize']) ? $args['pageSize'] : 10;
        $page = isset($args['page']) ? ($args['page'] - 1) * $pageSize : 0;

        $typeStr = '';
        switch($type){
            case 'lastest':
                $typeStr = 'article_publish_time';
                $orderByStr = 'desc';
                break;

            default:
                $typeStr = 'article_publish_time';
                $orderByStr = 'desc';
        }
        //$orderByStr = $typeStr . ' ' . $orderByStr;
        //Capsule::connection()->enableQueryLog();
        $article = Capsule::table('articles')->selectRaw("select pid, articles.article_title, articles.article_brief, articles.article_thumbnail, articles.article_author, FROM_UNIXTIME(articles.article_publish_time, '%Y-%m-%d') as article_publish_time, articles.wx_title_cn, articles.wx_id, articles.weixin_avatar")->whereRaw("article_title IS NOT NULL")->orderby($typeStr, $orderByStr)->limit($pageSize)->get();
//        $article = Capsule::select("select pid, articles.article_title, articles.article_brief, articles.article_thumbnail, articles.article_author, FROM_UNIXTIME(articles.article_publish_time, '%Y-%m-%d') as article_publish_time, articles.wx_title_cn, articles.wx_id, articles.weixin_avatar from articles where article_title IS NOT NULL order by ? limit ?,?", array($orderByStr, $page, $pageSize));
//        $article = Articles::whereRaw("article_title IS NOT NULL")
//                    ->orderBy($typeStr, $orderByStr)
//                    ->take($pageSize)
//                    ->get(['pid', 'articles.article_title', 'articles.article_brief', 'articles.article_thumbnail', 'articles.article_author', "date_format(FROM_UNIXTIME(articles.article_publish_time, '%Y-%m-%d')) as article_publish_time", 'articles.wx_title_cn', 'articles.wx_id', 'articles.weixin_avatar']);
        //$res = Capsule::connection()->getQueryLog();
        //var_dump($res);
        $data = [
            'data'      =>  $article,
            'status'    =>  200,
            'code'      =>  '',
            'message'   =>  ''
        ];
        $response = $response->withHeader('Access-Control-Allow-Origin','*');
        return $response->withJson($data, 200);
    })->setName('list-article');

    $this->get('/detail/{id}', function($request, $response, $args){
        $id = $args['id'];

        $article = Capsule::select("select * from articles where pid = ? ", array($id));
        $data = [
            'data'      =>  $article,
            'status'    =>  200,
            'code'      =>  '',
            'message'   =>  ''
        ];
        $response = $response->withHeader('Access-Control-Allow-Origin','*');

        return $response->withJson($data, 200);
    });
});



$app->get('/search/{key}/type/{type}', function (Request $request, Response $response, array $args) {
    $name = $args['key'];
    $response->getBody()->write("Hello, $name:{$args['type']}");

    return $response;
});


$app->run();