<?php

namespace App\Tests;

use \App\Model\Cli\Test as Tester;

/**
 * Class AccessControl
 *
 * Assures that no unauthorized access is possible
 *
 * @package App\Tests
 */
class AccessControl
{
    public $description = 'Access control';

    public function run(Tester $tester)
    {
        list ($code, $json) = $tester->curl('GET', '/messages');

        return $code == 401 && $json->isError;
    }
}
