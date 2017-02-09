<?php

namespace App\Controller;

use \App\Model\Core;
use \App\Service\Message as MessageSvc;

/**
 * Class Message
 *
 * @package App\Controller
 */
class Message extends Generic
{
    /**
     * Returns all the messages
     *
     * @return string
     */
    public static function listAll()
    {
        $items = [];

        foreach (Core::svc('Message')->findAll() as $message)
        {
            $items[] = self::message_structure($message);
        }

        return $items;
    }

    /**
     * Returns all archived messages
     *
     * @return string
     */
    public static function listArchived()
    {
        $items = [];

        foreach (Core::svc('Message')->findArchived() as $message)
        {
            $items[] = self::message_structure($message);
        }

        return $items;
    }

    /**
     * Displays a particular message
     *
     * @param $args
     * @return string
     * @throws \Exception
     */
    public static function show($args)
    {
        if (!$message = Core::svc('Message')->findById($args->id))
        {
            throw new \Exception('Message not found', 404);
        }

        return self::message_structure($message);
    }

    /**
     * Marks a message as read
     *
     * @param $args
     * @return string
     * @throws \Exception
     */
    public static function read($args)
    {
        if (!$message = Core::svc('Message')->findById($args->id))
        {
            throw new \Exception('Message not found', 404);
        }

        $message->flags |= MessageSvc::FLAG_READ;
        Core::svc('Message')->update($message);

        return self::message_structure($message);
    }

    /**
     * Marks message as archived
     *
     * @param $args
     * @return string
     * @throws \Exception
     */
    public static function archive($args)
    {
        if (!$message = Core::svc('Message')->findById($args->id))
        {
            throw new \Exception('Message not found', 404);
        }

        $message->flags |= MessageSvc::FLAG_ARCHIVED;
        Core::svc('Message')->update($message);

        return self::message_structure($message);
    }

    /**
     * Formats an output message structure
     *
     * @param $message
     * @return array
     */
    protected static function message_structure($message)
    {
        return
        [
            'uid'           => $message->id,
            'sender'        => $message->sender,
            'subject'       => $message->subject,
            'message'       => $message->body,
            'time_sent'     => $message->ts,
            'is_read'       => (bool) ($message->flags & MessageSvc::FLAG_READ),
            'is_archived'   => (bool) ($message->flags & MessageSvc::FLAG_ARCHIVED),
        ];
    }
}
