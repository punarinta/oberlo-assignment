<?php

namespace App\Tests;

use App\Model\Core;
use \App\Model\Cli\Test as Tester;
use App\Model\DB;
use App\Service\Message as MessageSvc;

/**
 * Class MarkRead
 *
 * Assures that marking a message as read works
 *
 * @package App\Tests
 */
class MarkRead
{
    public $description = 'Mark message read';

    public function run(Tester $tester)
    {
        // create a temporary message
        DB::connect($tester->getConfig('db'));

        $message = Core::svc('Message')->create(
        [
            'sender'    => $tester->randomText(8),
            'subject'   => $tester->randomText(12),
            'body'      => $tester->randomText(100),
            'ts'        => time(),
            'flags'     => 0,
        ]);

        $cleanup = function ($return = true) use ($message)
        {
            Core::svc('Message')->delete($message);
            DB::query('ALTER TABLE message AUTO_INCREMENT=1');
            return $return;
        };

        list ($code, $json) = $tester->curl('PUT', "/messages/{$message->id}/read");

        if ($code != 200 || $json->isError)
        {
            echo 'API error';
            return $cleanup(false);
        }

        $message = Core::svc('Message')->findById($message->id);

        if (!($message->flags & MessageSvc::FLAG_READ))
        {
            echo 'flag not set';
            return $cleanup(false);
        }

        return $cleanup();
    }
}
