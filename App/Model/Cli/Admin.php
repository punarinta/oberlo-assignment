<?php

namespace App\Model\Cli;

use \App\Model\DB;
use \App\Model\Core;

/**
 * Class Admin
 *
 * @package App\Model\Cli
 */
class Admin
{
    /**
     * Populate the database with sample data
     *
     * @param $filename
     * @return int
     * @throws \Exception
     */
    public function populate($filename)
    {
        $cfg = require_once 'config/config.php';

        if (!file_exists($filename))
        {
            throw new \Exception("File '$filename' not found");
        }

        DB::connect($cfg['db']);

        if (!$json = json_decode(file_get_contents($filename)))
        {
            throw new \Exception('Data must be in JSON format.');
        }

        if (!is_array($json->messages))
        {
            throw new \Exception('Messages must be arranged in array.');
        }

        DB::query('TRUNCATE TABLE message');

        foreach ($json->messages as $message)
        {
            Core::svc('Message')->create(
            [
                'id'        => $message->uid,
                'sender'    => $message->sender,
                'subject'   => $message->subject,
                'body'      => $message->message,
                'ts'        => $message->time_sent,
                'flags'     => 0,
            ]);
        }

        return 0;
    }
}