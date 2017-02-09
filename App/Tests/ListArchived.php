<?php

namespace App\Tests;

use \App\Model\Cli\Test as Tester;

/**
 * Class Pagination
 *
 * Assures that /messages/archived returns archived messages only
 *
 * @package App\Tests
 */
class ListArchived
{
    public $description = 'Listing archived messages';

    public function run(Tester $tester)
    {
        $tester->auth();
        list ($code, $json) = $tester->curl('GET', "/messages/archived");

        if ($code != 200 || $json->isError)
        {
            echo 'API error';
            return false;
        }

        foreach ($json->data as $message)
        {
            if (!$message->is_archived)
            {
                echo 'non-archive message met';
                return false;
            }
        }

        return true;
    }
}
