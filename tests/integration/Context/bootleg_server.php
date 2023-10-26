<?php

require_once __DIR__ . '/../../../vendor/autoload.php';


$requestsFile = new \Penneo\SDK\Tests\Integration\JsonArrayFile(getenv('PENNEO_REQUEST_FILE'));
$responseFile = new \Penneo\SDK\Tests\Integration\JsonArrayFile(getenv('PENNEO_RESPONSE_FILE'));

$requestsFile->add((object) [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => preg_replace('%^/[^/]+%', '', $_SERVER['REQUEST_URI']),
    'body' =>  file_get_contents('php://input') ?: '',
]);

$responses = $responseFile->get();
$response = array_shift($responses);

if (!$response) {
    http_response_code(598);
    die();
}

http_response_code($response->status);
echo $response->body;
