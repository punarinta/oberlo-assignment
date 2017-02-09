<?php

namespace App\Tests;

use \App\Model\Cli\Test as Tester;

/**
 * Class Authorization
 *
 * Checks how well does authorization work.
 *
 * @package App\Tests
 */
class Authorization
{
    public $description = 'Authorization';

    public function run(Tester $tester)
    {
        $tester->auth();
        $result = $tester->curl('GET', '/messages');

        return $result[0] == 200 && !$result[1]->isError;
    }
}
