<?php

namespace App\Model\Cli;

use App\Model\Core;

/**
 * Class Test
 *
 * A simple API testing tool
 *
 * @package App\Model\Cli
 */
class Test
{
    /**
     * cURL handle
     *
     * @var resource
     */
    private $ch;

    /**
     * App config
     *
     * @var array
     */
    private $config = [];

    /**
     * Extra HTTP headers for cURL
     *
     * @var array
     */
    private $headers = [];

    /**
     * Just setup cURL in this constructor.
     */
    public function __construct()
    {
        $this->ch = curl_init();
        $this->config = require_once 'config/config.php';
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * We don't want config data to be written publicly, but reading is fine
     *
     * @param array $path
     * @return null
     */
    public function getConfig($path = [])
    {
        return Core::aPath($this->config, $path);
    }

    /**
     * Shows a coloured verdict
     *
     * @param $value
     * @return int
     */
    public function verdict($value)
    {
        echo $value === true ? " \033[1;32mOK" : " \033[1;31mFAIL";

        if (is_numeric($value))
        {
            echo " (code:$value)";
        }

        echo "\033[0m\n";

        return (int) ($value !== true);
    }

    /**
     * Add authentication header
     */
    public function auth()
    {
        $this->headers = ['Authorization: Basic ' . base64_encode($this->config['auth']['username'] . ':' . $this->config['auth']['password'])];
    }

    /**
     * Makes a cURL request to the endpoint and processes it a bit.
     *
     * @param $method
     * @param $endpoint
     * @param array $payload
     * @return array
     */
    public function curl($method, $endpoint, $payload = [])
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->config['app']['root'] . $endpoint);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);

        $response = curl_exec($this->ch);
        $info = curl_getinfo($this->ch);

        $headers = substr($response, 0, $info['header_size']);
        $body = substr($response, $info['header_size']);

        $json = json_decode($body);

        return
        [
            $info['http_code'],
            $json && is_object($json) ? $json : $body,
            $headers
        ];
    }

    /**
     * Run all the tests in App/Tests dir
     *
     * @return int
     */
    public function all()
    {
        $status = 0;

        echo "Running all tests:\n\n";

        foreach (glob('App/Tests/*.php') as $testFile)
        {
            $testClassName = preg_replace('/App\/Tests\/([a-zA-Z]+).php/i', '$1', $testFile);

            if ($testClassName == 'Generic')
            {
                continue;
            }

            $testClassName = '\App\Tests\\' . $testClassName;
            $test = (object) new $testClassName;

            echo "* {$test->description} ... ";

            $status = max($status, $this->verdict($test->run($this)));
        }

        echo "\n";

        return $status;
    }

    /**
     * Run a custom test from App/Tests dir.
     *
     * @param $testName
     * @return int
     * @throws \Exception
     */
    public function custom($testName)
    {
        $testClassName = '\App\Tests\\' . $testName;

        if (!file_exists("App/Tests/$testName.php"))
        {
            throw new \Exception("Custom test pattern not found: $testName");
        }

        $test = (object) new $testClassName;

        echo "Running test '{$test->description}' ... ";

        return $this->verdict($test->run($this));
    }

    /**
     * General purpose random text generator
     *
     * @param int $length
     * @param bool $specialSymbols
     * @return string
     */
    public function randomText($length = 8, $specialSymbols = false)
    {
        $pass = '';
        $alphabet = 'abcdef ghijkl mnopqr stuvwx yzABCD EFGHIJ KLMNOP QRSTUV WXYZ01 23456789' . ($specialSymbols ? '.,!#%=?+-' : '');

        for ($i = 0; $i < $length; $i++)
        {
            $n = rand(0, strlen($alphabet) - 1);
            $pass .= $alphabet[$n];
        }

        return $pass;
    }
}
