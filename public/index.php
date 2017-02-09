<?php

use \App\Model\Core;

/**
 * Class App
 *
 * Welcome!
 * In this class we will mostly care about the run-time environment. We do not really want to keep any code in 'public',
 * so we pass execution to the actual code as soon as possible.
 */
class App
{
    /**
     * This class is a singleton, so we simply run and leave.
     */
    static public function run()
    {
        /*
         * 1. Do some environment preparations first.
         */

        // convenient for relative file path usage
        chdir(__DIR__ . '/..');

        // in our case we don't need it, but PHP wants it anyways
        date_default_timezone_set('UTC');

        // no external libraries are used => use a tiny autoloader
        spl_autoload_register(function ($class)
        {
            if (0 === strpos($class, 'App\\'))
            {
                include_once './' . strtr($class, '\\', '/') . '.php';
                return true;
            }

            return false;

        }, true, true);


        /*
         * 2. OK Google, run the actual app!
         */

        $data       = null;
        $errMsg     = null;
        $isError    = false;
        $returnCode = 200;

        try
        {
            $data = Core::init();
        }
        catch (\Exception $e)
        {
            $isError    = true;
            $errMsg     = $e->getMessage();
            $returnCode = $e->getCode() ?: 500;
        }

        echo json_encode(
        [
            'isError'   => $isError,
            'errMsg'    => $errMsg,
            'data'      => $data,
        ]);

        return http_response_code($returnCode);
    }
}

\App::run();
