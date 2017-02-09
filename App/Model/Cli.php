<?php

namespace App\Model;

/**
 * Class Cli
 *
 * Console interface. Used for testing and administration.
 *
 * @package App\Model
 */
class Cli
{
    /**
     * Parses and executes a CLI command
     *
     * @param $argv
     * @return string
     */
    public function dispatch($argv)
    {
        try
        {
            array_shift($argv);
            $toolName = '\App\Model\Cli\\' . ucfirst($argv[0]);

            if (!class_exists($toolName))
            {
                throw new \Exception("Unknown command '" . implode(' ', $argv) . "'. No class found: '$toolName'.");
            }

            $tool = new $toolName;
            $method = isset ($argv[1]) ? $argv[1] : 'main';

            if (!method_exists($tool, $method))
            {
                throw new \Exception("Unknown command '" . implode(' ', $argv) . "'. No method found: '$method'.");
            }

            return call_user_func_array([$tool, $method], array_splice($argv, 2));
        }
        catch (\Exception $e)
        {
            echo $e->getMessage() . "\n\n";

            return $e->getCode() ?: 1;
        }
    }
}
