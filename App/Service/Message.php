<?php

namespace App\Service;

use App\Model\DB;

/**
 * Class Message
 *
 * @package App\Service
 */
class Message extends Generic
{
    /**
     * Flag constants
     */
    const FLAG_READ     = 0b01;
    const FLAG_ARCHIVED = 0b10;

    /**
     * Returns all archived messages
     *
     * @return array
     */
    public static function findArchived()
    {
        return DB::rows('SELECT * FROM message WHERE flags & ?', [self::FLAG_ARCHIVED]);
    }
}
