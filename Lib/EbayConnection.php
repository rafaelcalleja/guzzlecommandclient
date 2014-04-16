<?php

namespace Wk\GuzzleCommandClient\Lib;

use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Event\ProcessEvent;
use Monolog\Logger;

/**
 * Class GuzzleCommandClient
 */
class GuzzleCommandClient
{

    protected $logger;

    /** @var  Guzzle\Service\Client */
    protected $client;

    protected $appId;

    protected $ebayUrls = array();

    /**
     * Constructor for the class
     */
    public function __construct()
    {
        $this->initClient();
    }

    /**
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @param array $urls
     */
    public function setEbayUrls (array $urls)
    {
        $this->ebayUrls = $urls;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Getter for the client
     * @return Client $client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Setter for the client
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get the product info from ebay
     * @param integer $ebayProductId
     *
     * @return string
     */
    public function findProductByEbayId($ebayProductId)
    {
        if (empty($this->ebayUrls['product_api'])) {
            return json_encode(array("error" => "Product API url not found"));
        }

        $this->setBaseUrl($this->ebayUrls['product_api']);

        $params = array(
            "appid" => $this->appId,
            "productIdValue" => $ebayProductId,
        );

        $result = $this->executeCommand('FindProductById', $params);

        return ("Success" == $result['Ack'] && isset($result['Product'])) ? json_encode($result['Product']) : json_encode(array("error" => "Product not found on ebay"));
    }

    /**
     * @param string     $commandName
     * @param null|array $params
     *
     * @return integer|boolean
     */
    protected function executeCommand ($commandName, $params = null)
    {
        try {
            $this->logger->addInfo("Executing " . $commandName . " with params " . print_r($params, true));
            $command = $params ? $this->client->getCommand($commandName, $params) : $this->client->getCommand($commandName);

            $command->getEmitter()->on('process', function (ProcessEvent $event) {
                    $event->setResult($event->getResponse()->json());
                });

            $result = $this->client->execute($command);

            $this->logger->addInfo("Result: \n" . print_r($result, true));

        } catch (\Exception $e) {
            $this->logger->addInfo("Exception: " . $e->getMessage());

            return null;
        }

        return $result;
    }

    /**
     * @param string $baseUrl
     */
    protected function setBaseUrl($baseUrl)
    {
        $this->initClient(array('base_url' => $baseUrl));
    }

    /**
     * @param array $options
     */
    private function initClient(array $options = null)
    {
        $client = $options ? new Client($options) : new Client();

        $json = file_get_contents(__DIR__. "/../Resources/config/service.json");
        $config = json_decode($json, true);
        $description = new Description($config);
        $this->client = new GuzzleClient($client, $description);
    }
}