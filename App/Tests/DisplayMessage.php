<?php

namespace App\Tests;

use \App\Model\Cli\Test as Tester;

/**
 * Class DisplayMessage
 *
 * Assures that a message can be displayed by its ID
 *
 * @package App\Tests
 */
class DisplayMessage
{
    public $description = 'Showing message by ID';

    public function run(Tester $tester)
    {
        $tester->auth();
        list ($code, $json) = $tester->curl('GET', "/messages");

        if ($code != 200 || $json->isError)
        {
            echo 'API error';
            return false;
        }

        $message = $json->data[mt_rand(0, count($json->data) - 1)];

        list ($code, $json) = $tester->curl('GET', "/messages/{$message->uid}");

        if ($code != 200 || $json->isError)
        {
            echo 'API error 2';
            return false;
        }

        if ($json->data->uid != $message->uid)
        {
            echo 'wrong output message ID';
            return false;
        }

        return true;
    }
}
