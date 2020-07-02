<?php
namespace App\HttpController;

use EasySwoole\Http\AbstractInterface\AbstractRouter;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        $routeCollector->addRoute('GET', '/home', '/Article/defaultArticle');
        $routeCollector->addRoute('GET', '/class', '/Article/articleClass');
        $routeCollector->addRoute('GET', '/details', '/Article/articleDetail');
    }
}
