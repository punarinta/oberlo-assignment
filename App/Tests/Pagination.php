<?php

namespace App\Tests;

use \App\Model\Cli\Test as Tester;

/**
 * Class Pagination
 *
 * Assures that pagination works
 *
 * @package App\Tests
 */
class Pagination
{
    public $description = 'Pagination';

    public function run(Tester $tester)
    {
        $pageSize = mt_rand(1, 4);  // we have only 5 messages in our sample data

        $tester->auth();
        list ($code, $json, $headers) = $tester->curl('GET', "/messages?page_start=0&page_length=$pageSize");

        if ($code != 200 || $json->isError)
        {
            echo 'API error';
            return false;
        }

        if (count ($json->data) != $pageSize)
        {
            echo 'output page size is wrong';
            return false;
        }

        $headerFound = false;

        foreach (explode("\r", $headers) as $header)
        {
            $header = explode(':', trim($header), 2);

            if ($header[0] == 'Link')
            {
                $headerFound = true;
                $root = $tester->getConfig('app.root');

                if (trim($header[1]) !== "<{$root}/messages?page_start=$pageSize&page_length=$pageSize>; rel=\"next\"")
                {
                    echo 'Link header is wrong';
                    return false;
                }
            }
        }

        if (!$headerFound)
        {
            echo 'no Link header found';
            return false;
        }

        return true;
    }
}
