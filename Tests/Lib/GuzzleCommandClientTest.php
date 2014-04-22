<?php


namespace Wk\GuzzleCommandClient\Tests\Lib;

use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Adapter\MockAdapter;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream;
use Wk\GuzzleCommandClient\Lib\GuzzleCommandClient;

/**
 * Class GuzzleCommandClientTest
 */
class GuzzleCommandClientTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteCommand()
    {
        $jsonString = '{"status":true}';
        $jsonStringArray = json_decode($jsonString, true);

        $response = new Response(
            200, array(
                'Location' => 'asgoodasnu.test.com',
                'Content-Type' => 'application/json'
            ), Stream\create($jsonString)
        );
        $adapter = new MockAdapter();
        $adapter->setResponse($response);
        $client = new Client(['adapter' => $adapter]);
        $json = file_get_contents(__DIR__ . "/../../Resources/config/service.json");
        $config = json_decode($json, true);
        $description = new Description($config);
        $clientMock = new GuzzleClient($client, $description);

        $guzzle = new GuzzleCommandClient($json);
        $guzzle->setClient($clientMock);

        $result = $guzzle->executeCommand('getRandomStuff');
        $this->assertEquals($jsonStringArray['status'], $result['status']);
    }
}
