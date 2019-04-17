<?php

require './model/pdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
//ini_set('default_charset', 'utf8mb4');
//error_reporting(E_ALL); ini_set("display_errors", 1);

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    //Main Server API
    $r->addRoute('GET', '/', 'index');
    $r->addRoute('GET', '/test/{no}', 'test');
    $r->addRoute('GET', '/home', 'index');

    $r->addRoute('POST', '/user', 'user');
    $r->addRoute('DELETE', '/user','deleteUser');
    $r->addRoute('GET', '/token/{id}/{pw}', 'token');


    $r->addRoute('POST', '/my/comic', 'myComic');
    $r->addRoute('GET', '/my/comic/list', 'myComicList');


    $r->addRoute('GET', '/comic/all', 'comicAll');
    $r->addRoute('GET', '/comic/day/{day}', 'comicDay');
    $r->addRoute('POST', '/comic/like','comicLike');
    $r->addRoute('GET', '/comic/{input}','comicSearch');

    $r->addRoute('GET', '/comic/contentAll/{comicno}', 'contentAll');
    $r->addRoute('GET', '/comic/contents/{contentno}', 'pagingContent');
    $r->addRoute('GET', '/comic/content/{contentno}', 'comicContent');
    $r->addRoute('GET', '/comic/content/first/{comicno}', 'contentFirst');
    $r->addRoute('POST', '/comic/content/like', 'contentLike');
    $r->addRoute('PUT', '/comic/content/rate', 'contentRate');


    $r->addRoute('GET', '/comic/content/comment/{contentno}','comment');
    $r->addRoute('GET', '/comic/content/bestcomment/{contentno}','bestComment');
    $r->addRoute('POST', '/comic/content/comment','makeComment');
    $r->addRoute('POST', '/comic/content/comment/like', 'commentLike');
    $r->addRoute('POST', '/comic/content/comment/dislike', 'commentDislike');
    $r->addRoute('DELETE', '/comic/content/comment','deleteComment');





//    $r->addRoute('GET', '/logs/error', 'ERROR_LOGS');
//    $r->addRoute('GET', '/logs/access', 'ACCESS_LOGS');

//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI


if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
    //$uri = rawurlencode($uri);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs =  new Logger('BIGS_ACCESS');
$errorLogs =  new Logger('BIGS_ERROR');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        require './controller/mainController.php';

        break;
}




