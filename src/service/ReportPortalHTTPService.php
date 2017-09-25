<?php
namespace ReportPortalBasic\Service;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use ReportPortal\Basic\Enum\ItemStatusesEnum;
use Symfony\Component\Yaml\Yaml;

/**
 * Report portal HTTP service.
 * Provides basic methods to collaborate with Report portal.
 *
 * @author Mikalai_Kabzar
 */
class ReportPortalHTTPService
{

    /**
     *
     * @var string
     */
    public const DAFAULT_LAUNCH_MODE = 'DEFAULT';

    /**
     *
     * @var string
     */
    protected const EMPTY_ID = 'empty id';

    /**
     *
     * @var string
     */
    protected const DEFAULT_FEATURE_DESCRIPTION = '';

    /**
     *
     * @var string
     */
    protected const DEFAULT_SCENARIO_DESCRIPTION = '';

    /**
     *
     * @var string
     */
    protected const DEFAULT_STEP_DESCRIPTION = '';

    /**
     *
     * @var string
     */
    protected const FORMAT_DATE = 'Y-m-d\TH:i:s';

    /**
     *
     * @var string
     */
    protected const BASE_URI_TEMPLATE = 'http://%s/api/';

    /**
     *
     * @var string
     */
    protected static $timeZone;

    /**
     *
     * @var string
     */
    protected static $UUID;

    /**
     *
     * @var string
     */
    protected static $baseURI;

    /**
     *
     * @var string
     */
    protected static $host;

    /**
     *
     * @var string
     */
    protected static $projectName;

    /**
     *
     * @var string
     */
    protected static $launchID = ReportPortalHTTPService::EMPTY_ID;

    /**
     *
     * @var string
     */
    protected static $rootItemID = ReportPortalHTTPService::EMPTY_ID;

    /**
     *
     * @var string
     */
    protected static $featureItemID = ReportPortalHTTPService::EMPTY_ID;

    /**
     *
     * @var string
     */
    protected static $scenarioItemID = ReportPortalHTTPService::EMPTY_ID;

    /**
     *
     * @var string
     */
    protected static $stepItemID = ReportPortalHTTPService::EMPTY_ID;

    /**
     *
     * @var \GuzzleHttp\Client
     */
    protected static $client;

    function __construct()
    {
        ReportPortalHTTPService::$client = new Client([
            'base_uri' => ReportPortalHTTPService::$baseURI
        ]);
    }

    /**
     * Check if any suite has running status
     *
     * @return boolean - true if any suite has running status
     */
    public static function isSuiteRunned()
    {
        return ReportPortalHTTPService::$rootItemID != ReportPortalHTTPService::EMPTY_ID;
    }

    /**
     * Check if any step has running status
     *
     * @return boolean - true if any step has running status
     */
    public static function isStepRunned()
    {
        return ReportPortalHTTPService::$stepItemID != ReportPortalHTTPService::EMPTY_ID;
    }

    /**
     * Check if any scenario has running status
     *
     * @return boolean - true if any scenario has running status
     */
    public static function isScenarioRunned()
    {
        return ReportPortalHTTPService::$scenarioItemID != ReportPortalHTTPService::EMPTY_ID;
    }

    /**
     * Check if any feature has running status
     *
     * @return boolean - true if any feature has running status
     */
    public static function isFeatureRunned()
    {
        return ReportPortalHTTPService::$featureItemID != ReportPortalHTTPService::EMPTY_ID;
    }

    /**
     * Set configuration for Report portal from yaml file
     *
     * @param string $yamlFilePath
     *            - path to configuration file
     */
    public static function configureReportPortalHTTPService(string $yamlFilePath)
    {
        $yamlArray = Yaml::parse($yamlFilePath);
        ReportPortalHTTPService::$UUID = $yamlArray['UUID'];
        ReportPortalHTTPService::$host = $yamlArray['host'];
        ReportPortalHTTPService::$baseURI = sprintf(ReportPortalHTTPService::BASE_URI_TEMPLATE, ReportPortalHTTPService::$host);
        ReportPortalHTTPService::$projectName = $yamlArray['projectName'];
        ReportPortalHTTPService::$timeZone = $yamlArray['timeZone'];
    }

    /**
     * Launch test run
     *
     * @param string $name
     *            - name of test launch
     * @param string $description
     *            - description of test run
     * @param string $mode
     *            - mode
     * @param array $tags
     *            - array with tags of test run
     * @return ResponseInterface - result of request
     */
    public static function launchTestRun(string $name, string $description, string $mode, array $tags)
    {
        $result = ReportPortalHTTPService::$client->post('v1/' . ReportPortalHTTPService::$projectName . '/launch', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . ReportPortalHTTPService::$UUID
            ),
            'json' => array(
                'description' => $description,
                'mode' => $mode,
                'name' => $name,
                'start_time' => ReportPortalHTTPService::getTime(),
                'tags' => $tags
            )
        ));
        ReportPortalHTTPService::$launchID = ReportPortalHTTPService::getValueFromResponse('id', $result);
        return $result;
    }

    /**
     * Finish test run
     *
     * @param string $runStatus
     *            - status of test run
     * @return ResponseInterface - result of request
     */
    public static function finishTestRun(string $runStatus)
    {
        $result = ReportPortalHTTPService::$client->put('v1/' . ReportPortalHTTPService::$projectName . '/launch/' . ReportPortalHTTPService::$launchID . '/finish', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . ReportPortalHTTPService::$UUID
            ),
            'json' => array(
                'end_time' => ReportPortalHTTPService::getTime(),
                'status' => $runStatus
            )
        ));
        return $result;
    }

    /**
     * Create root item
     *
     * @param string $name
     *            - root item name
     * @param string $description
     *            - root item description
     * @param array $tags
     *            - array with tags
     * @return ResponseInterface - result of request
     */
    public static function createRootItem(string $name, string $description, array $tags)
    {
        $result = ReportPortalHTTPService::$client->post('v1/' . ReportPortalHTTPService::$projectName . '/item', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . ReportPortalHTTPService::$UUID
            ),
            'json' => array(
                'description' => $description,
                'launch_id' => ReportPortalHTTPService::$launchID,
                'name' => $name,
                'start_time' => ReportPortalHTTPService::getTime(),
                "tags" => $tags,
                "type" => "SUITE"
            )
        ));
        ReportPortalHTTPService::$rootItemID = ReportPortalHTTPService::getValueFromResponse('id', $result);
        return $result;
    }

    /**
     * Finish root item
     *
     * @param string $resultStatus
     *            - result of root item
     * @return ResponseInterface - result of request
     */
    public static function finishRootItem(string $resultStatus)
    {
        $result = ReportPortalHTTPService::finishItem(ReportPortalHTTPService::$rootItemID, ItemStatusesEnum::PASSED, '');
        ReportPortalHTTPService::$rootItemID = ReportPortalHTTPService::EMPTY_ID;
        return $result;
    }

    /**
     * Add a log message to item
     *
     * @param string $item_id
     *            - item id to add log message
     * @param string $message
     *            - log message
     * @param string $logLevel
     *            - log level of log message
     * @return ResponseInterface - result of request
     */
    protected static function addLogMessage(string $item_id, string $message, string $logLevel)
    {
        $result = ReportPortalHTTPService::$client->post('v1/' . ReportPortalHTTPService::$projectName . '/log', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . ReportPortalHTTPService::$UUID
            ),
            'json' => array(
                'item_id' => $item_id,
                'message' => $message,
                'time' => ReportPortalHTTPService::getTime(),
                'level' => $logLevel
            )
        ));
        return $result;
    }

    /**
     * Get value from response.
     *
     * @param string $lookForRequest
     *            - string to find value
     * @param ResponseInterface $response
     * @return string value by $lookForRequest.
     */
    protected static function getValueFromResponse(string $lookForRequest, ResponseInterface $response)
    {
        $array = json_decode($response->getBody()->getContents());
        return $array->{$lookForRequest};
    }

    /**
     * Start child item.
     *
     * @param string $parentItemID
     *            - id of parent item.
     * @param string $description
     *            - item description
     * @param string $name
     *            - item name
     * @param string $type
     *            - item type
     * @param array $tags
     *            - array with tags
     * @return ResponseInterface - result of request
     */
    protected static function startChildItem(string $parentItemID, string $description, string $name, string $type, array $tags)
    {
        $result = ReportPortalHTTPService::$client->post('v1/' . ReportPortalHTTPService::$projectName . '/item/' . $parentItemID, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . ReportPortalHTTPService::$UUID
            ),
            'json' => array(
                'description' => $description,
                'launch_id' => ReportPortalHTTPService::$launchID,
                'name' => $name,
                'start_time' => ReportPortalHTTPService::getTime(),
                'tags' => $tags,
                'type' => $type
            )
        ));
        return $result;
    }

    /**
     * Finish item by id
     *
     * @param string $itemID
     *            - test item ID
     * @param string $status
     *            - status of test item
     * @param string $description
     *            - description of test item
     * @return ResponseInterface - result of request
     */
    protected static function finishItem(string $itemID, string $status, string $description)
    {
        $result = ReportPortalHTTPService::$client->put('v1/' . ReportPortalHTTPService::$projectName . '/item/' . $itemID, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . ReportPortalHTTPService::$UUID
            ),
            'json' => array(
                'description' => $description,
                'end_time' => ReportPortalHTTPService::getTime(),
                'status' => $status
            )
        ));
        return $result;
    }

    /**
     * Get local time
     *
     * @return string with local time
     */
    protected static function getTime()
    {
        return date(ReportPortalHTTPService::FORMAT_DATE) . ReportPortalHTTPService::$timeZone;
    }
}