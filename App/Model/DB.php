<?php

namespace App\Model;

/**
 * Class DB
 *
 * Contains database abstractions.
 *
 * @package App\Model
 */
class DB
{
    /**
     * MySQLi connector object
     *
     * @var object
     */
    private static $connector;

    /**
     * Page start in case you need to paginate
     *
     * @var null
     */
    private static $pageStart = null;

    /**
     * Page length in case you need to paginate
     *
     * @var null
     */
    private static $pageLength = null;


    /**
     * Connects to the database using the parameters from the config
     *
     * @param $c
     * @throws \Exception
     */
    public static function connect($c)
    {
        if (!self::$connector = @mysqli_connect($c['host'], $c['user'], $c['pass'], $c['database'], $c['port']))
        {
            throw new \Exception('Cannot connect to the DB');
        }

        mysqli_query(self::$connector, 'SET NAMES utf8');
    }

    /**
     * Prepare an SQL statement
     *
     * @param $q
     * @param null $params
     * @return mixed
     * @throws \Exception
     */
    public static function prepare($q, $params = null)
    {
        $stmt = self::$connector->prepare($q);

        if ($stmt === false)
        {
            throw new \Exception('Invalid statement: ' . $q);
        }

        if ($params)
        {
            $parameters = [ str_repeat('s', count($params)) ];

            foreach ($params as $key => &$value)
            {
                $parameters[] = &$value;
            }

            call_user_func_array([$stmt, 'bind_param'], $parameters);
        }

        return $stmt;
    }

    /**
     * Turn array into an object
     *
     * @param $array
     * @return \stdClass
     */
    public static function toObject($array)
    {
        $obj = new \stdClass();

        foreach ($array as $k => $v)
        {
            $obj->{$k} = $v;
        }

        return $obj;
    }

    /**
     * Full cycle to get one row
     *
     * @param $q
     * @param null $params
     * @return null|\StdClass
     * @throws \Exception
     */
    public static function row($q, $params = null)
    {
        $stmt = self::prepare($q, $params);
        $stmt->execute();
        $stmt->store_result();

        if (!$stmt->num_rows)
        {
            $stmt->close();
            return null;
        }

        $row = self::stmtBindAssoc($stmt);
        $stmt->fetch();
        $stmt->close();

        return self::toObject($row);
    }

    /**
     * Full cycle to get all the rows with pagination
     *
     * @param $q
     * @param null $params
     * @return array
     */
    public static function rows($q, $params = null)
    {
        $rows = [];
        $array = self::exec($stmt, $q . self::sqlPaging(), $params);

        while ($stmt->fetch())
        {
            $rows[] = self::toObject($array);
        }

        $stmt->close();

        return $rows;
    }

    /**
     * Returns last inserted ID
     *
     * @return mixed
     */
    public static function lastInsertId()
    {
        return self::$connector->insert_id;
    }

    /**
     * Execute an arbitrary query and return
     *
     * @param $stmt
     * @param $q
     * @param null $params
     * @return array
     * @throws \Exception
     */
    public static function exec(&$stmt, $q, $params = null)
    {
        $stmt = self::prepare($q, $params);
        $stmt->execute();
        $stmt->store_result();

        return self::stmtBindAssoc($stmt);
    }

    /**
     * Make an arbitrary query
     *
     * @param $q
     * @param null $params
     */
    public static function query($q, $params = null)
    {
        $stmt = self::prepare($q, $params);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Set up database pagination
     *
     * @param $start
     * @param $length
     */
    public static function setupPagination($start, $length)
    {
        self::$pageStart  = $start;
        self::$pageLength = $length;
    }

    /**
     * Generates an SQL paging.
     *
     * @return string
     */
    public static function sqlPaging()
    {
        if (self::$pageStart !== null)
        {
            $pageLength = self::$pageLength ? self::$pageLength : 25;
            return ' LIMIT ' . $pageLength . ' OFFSET ' . self::$pageStart;
        }

        return '';
    }

    /**
     * Bind output data
     *
     * @param $stmt
     * @return array
     */
    private static function stmtBindAssoc(&$stmt)
    {
        $count  = 1;
        $out    = [];
        $fields = [ $stmt ];

        $data = mysqli_stmt_result_metadata($stmt);

        while ($field = mysqli_fetch_field($data))
        {
            $fields[$count] = &$out[$field->name];
            ++$count;
        }
        call_user_func_array('mysqli_stmt_bind_result', $fields);

        return $out;
    }
}
