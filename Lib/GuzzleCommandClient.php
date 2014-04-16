<?php

namespace Wk\GuzzleCommandClient\Lib;

use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Event\ProcessEvent;

/**
 * Class GuzzleCommandClient
 */
class GuzzleCommandClient
{

    /** @var  GuzzleHttp\Command\Guzzle\GuzzleClient */
    protected $client;

    /**
     * Constructor for the class
     */
    public function __construct()
    {
        $this->initClient();
    }

    /**
     * Getter for the client
     * @return GuzzleClient $client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Setter for the client
     * @param GuzzleClient $client
     */
    public function setClient(GuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * Set the base url to a new value and re-create the client
     * @param string $baseUrl
     */
    protected function setBaseUrl($baseUrl)
    {
        $this->initClient(array('base_url' => $baseUrl));
    }

    /**
     * @param string $commandName
     * @param array  $params
     *
     * @return array
     */
    protected function executeCommand ($commandName, array $params = array())
    {
        try {
            $command = empty($params) ? $this->client->getCommand($commandName) : $this->client->getCommand($commandName, $params);

            $command->getEmitter()->on('process', function (ProcessEvent $event) {
                $event->setResult($event->getResponse()->json());
            });

            $result = $this->client->execute($command);

        } catch (\Exception $e) {

            return array("status" => "error", "message" => $e->getMessage());
        }

        return $result ? array("status" => "success", "message" => $result) : array("status" => "error", "message" => "Empty response after API call.");
    }

    /**
     * initialize the client
     * @param array $options
     */
    private function initClient(array $options = array())
    {
        $client = empty($options) ? new Client() : new Client($options);

        $json = file_get_contents(__DIR__. "/../Resources/config/service.json");
        $config = json_decode($json, true);
        $description = new Description($config);
        $this->client = new GuzzleClient($client, $description);
    }
}