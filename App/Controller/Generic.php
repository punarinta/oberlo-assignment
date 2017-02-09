<?php

namespace App\Controller;

use \App\Model\DB;
use App\Model\Core;

/**
 * Class Generic
 *
 * An entry point for all the controllers.
 *
 * @package App\Controller
 */
class Generic
{
    /**
     * Available routes
     *
     * @var array
     */
    static private $routes =
    [
        'list-messages'             => ['GET', '/messages', '\App\Controller\Message', 'listAll'],
        'list-archived-messages'    => ['GET', '/messages/archived', '\App\Controller\Message', 'listArchived'],
        'show-message'              => ['GET', '/messages/{id}', '\App\Controller\Message', 'show'],
        'read-message'              => ['PUT', '/messages/{id}/read', '\App\Controller\Message', 'read'],
        'archive-message'           => ['PUT', '/messages/{id}/archive', '\App\Controller\Message', 'archive'],
    ];

    /**
     * Dispatch method/path combination to an appropriate controller/method
     *
     * @param $method
     * @param $path
     * @return mixed
     * @throws \Exception
     */
    static public function dispatch($method, $path)
    {
        // go through set up routes
        foreach (self::$routes as $v)
        {
            if ($v[0] !== $method)
            {
                continue;
            }

            // try quick match
            if ($v[1] === $path)
            {
                // always good to check
                if (!method_exists($v[2], $v[3]))
                {
                    throw new \Exception("Method '{$v[3]}' does not exist.", 404);
                }

                return self::execute($v);
            }

            $routeVariables = [];

            // try parametrized match
            $regex = preg_replace_callback('#(\{[A-Za-z0-9_]+\})#', function ($d) use (&$routeVariables)
            {
                // extract names of the route variables
                $routeVariables[] = str_replace(['{','}'], ['',''], $d[1]);
                return '([A-Za-z0-9_]+)';
            }, $v[1]);

            if (strpos($regex, '(') === false)
            {
                continue;
            }

            if (preg_match('#' . $regex . '$#', $path, $d))
            {
                if (!method_exists($v[2], $v[3]))
                {
                    throw new \Exception("Method '{$v[3]}' does not exist.", 404);
                }

                // populate arguments
                $args = new \stdClass;

                for ($i = 1; $i < count($d); ++$i)
                {
                    $args->{$routeVariables[$i-1]} = $d[$i];
                }

                return self::execute($v, $args);
            }
        }

        throw new \Exception('Endpoint not found', 404);
    }

    /**
     * Execution wrapper
     *
     * @param $route
     * @param null $args
     * @return mixed
     */
    static private function execute($route, $args = null)
    {
        // if pagination is on add an additional header with pagination info
        $pageStart = Core::get('page_start') ?: 0;

        if ($pageLength = Core::get('page_length'))
        {
            DB::setupPagination($pageStart, $pageLength);
        }

        $items = forward_static_call([$route[2], $route[3]], $args);

        // limit pagination to GET
        if ($route[0] == 'GET' && $pageLength && count($items) == $pageLength)
        {
            $url = Generic::buildPath('list-messages');
            $pageStart += $pageLength;
            header("Link: <$url?page_start=$pageStart&page_length=$pageLength>; rel=\"next\"");
        }

        return $items;
    }

    /**
     * Build a path
     *
     * @param $name
     * @param bool $absolute
     * @return string
     * @throws \Exception
     */
    static public function buildPath($name, $absolute = true)
    {
        if (!isset (self::$routes[$name]))
        {
            throw new \Exception('Route does not exist');
        }

        return ($absolute ? Core::cfg('app.root') : '') . self::$routes[$name][1];
    }
}
