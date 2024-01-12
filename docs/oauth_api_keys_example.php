<?php

use Penneo\SDK\ApiConnector;
use Penneo\SDK\CaseFile;
use Penneo\SDK\OAuth\Config\Environment;
use Penneo\SDK\OAuth\OAuthBuilder;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;

session_start();

// set up where to store the tokens - either use the provided session storage
$tokenStorage = new SessionTokenStorage('optionalKeyToPlaceTokensInto');

// or build a custom one by implementing the interface
// $tokenStorage = new class implements \Penneo\SDK\OAuth\Tokens\TokenStorage {};

$penneoOAuth = OAuthBuilder::start()
    ->setEnvironment(Environment::SANDBOX)
    ->setClientId('clientId')                     // <- the credentials provided by Penneo
    ->setClientSecret('clientSecret')             // <-
    ->setRedirectUri('http://dev.php.local')      // the exact URL you provided to Penneo
    ->setAPIKey('apiKey')                         // <- can be found in your Penneo account settings
    ->setAPISecret('apiSecret')                   // <-
    ->setTokenStorage($tokenStorage)
    ->build();

ApiConnector::initializeOAuth($penneoOAuth);

$casefile = new CaseFile();
$casefile->setTitle('new test casefile from PHP');
CaseFile::persist($casefile);
