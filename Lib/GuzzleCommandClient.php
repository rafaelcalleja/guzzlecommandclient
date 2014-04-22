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

    /** @var  \GuzzleHttp\Command\Guzzle\GuzzleClient */
    protected $client;

    /** @var  \GuzzleHttp\Command\Guzzle\Description */
    protected $description;


    public function __construct($jsonServiceDescription)
    {
        $this->setServiceDescription($jsonServiceDescription);
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
     * @param string $jsonConfig
     */
    protected function setServiceDescription($jsonConfig)
    {
        $config = json_decode($jsonConfig, true);
        $this->description = new Description($config);
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
     * @param array $options
     *
     * @throws \Exception
     */
    protected function initClient(array $options = array())
    {
        if (!$this->description) {
            throw new \Exception("Service description not configured.");
        }

        $client = empty($options) ? new Client() : new Client($options);
        $this->client = new GuzzleClient($client, $this->description);
    }
}