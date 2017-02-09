<?php

namespace App\Model;

use App\Controller\Generic;

/**
 * Class Core
 *
 * @package App\Model
 */
class Core
{
    /**
     * Application config. Keep is safe from writing.
     *
     * @var array
     */
    private static $config = [];

    /**
     * Service instances
     *
     * @var array
     */
    private static $services = [];

    /**
     * Start the application
     *
     * @return null
     * @throws \Exception
     */
    public static function init()
    {
        switch ($_SERVER['REQUEST_METHOD'])
        {
            // cross-origin browser requests will send OPTIONS first, do not let such in
            case 'OPTIONS':
                header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
                return null;

            case 'GET':
            case 'PUT':
                break;

            default:
                throw new \Exception('Method not allowed');
        }

        // the output is JSON anyway
        header('Content-Type: application/json;charset=UTF-8');

        // allow cross-origin requests to test from browser
        header('Access-Control-Allow-Origin: *');

        self::$config = require_once 'config/config.php';

        //  Check authentication, use HTTP Basic, as defined.
        if (isset ($_SERVER['HTTP_AUTHORIZATION']))
        {
            $username = Core::cfg('auth.username');
            $password = Core::cfg('auth.password');

            $auth = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

            if (count($auth) < 2 || $auth[0] != 'Basic' || base64_decode($auth[1]) !== "$username:$password")
            {
                throw new \Exception('Unauthorized', 401);
            }
        }
        else
        {
            throw new \Exception('Unauthorized', 401);
        }

        DB::connect(Core::cfg('db'));

        // path dispatch
        $uri = rtrim(explode('?', $_SERVER['REQUEST_URI'])[0], '\\');

        return Generic::dispatch($_SERVER['REQUEST_METHOD'], $uri);
    }

    /**
     * Get configuration part
     *
     * @param $k
     * @return null
     */
    public static function cfg($k)
    {
        return self::aPath(self::$config, $k);
    }

    /**
     * HTTP GET parameters
     *
     * @param $k
     * @return null
     */
    public static function get($k)
    {
        return isset ($_GET[$k]) ? $_GET[$k] : null;
    }

    /**
     * Cached access to services
     *
     * @param $service
     * @return mixed
     */
    public static function svc($service)
    {
        if (!isset (self::$services[$service]))
        {
            $class = '\App\Service\\' . $service;
            self::$services[$service] = new $class;
        }

        return self::$services[$service];
    }

    /**
     * Provides an APath access to the array element.
     *
     * @param $a
     * @param null $k
     * @return null
     */
    public static function aPath($a, $k = null)
    {
        // return full object
        if ($k === null) return $a;

        // I forgot what
        if (empty ($a)) return null;

        $k = [0, $k];

        while (1)
        {
            $k = explode('.', $k[1], 2);

            if (isset ($a[$k[0]])) $a = $a[$k[0]];
            else return null;

            if (count($k) === 1) break;
        }

        return $a;
    }
}
