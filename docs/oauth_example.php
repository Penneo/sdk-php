<?php

use Penneo\SDK\ApiConnector;
use Penneo\SDK\CaseFile;
use Penneo\SDK\OAuth\Config\Environment;
use Penneo\SDK\OAuth\OAuthBuilder;
use Penneo\SDK\OAuth\PKCE\PKCE;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;
use Penneo\SDK\PenneoSDKException;

session_start();

// set up where to store the tokens - either use the provided session storage
$tokenStorage = new SessionTokenStorage('optionalKeyToPlaceTokensInto');

// or build a custom one by implementing the interface
// $tokenStorage = new class implements \Penneo\SDK\OAuth\Tokens\TokenStorage {};

$penneoOAuth = OAuthBuilder::start()
    ->setEnvironment(Environment::SANDBOX)
    ->setClientId('clientId')                     // <- the credentials provided by Penneo
    ->setClientSecret('clientSecret')             // <-
    ->setRedirectUri('http://dev.php.local')   // the exact URL you provided to Penneo
    ->setTokenStorage($tokenStorage)
    ->build();

if (isset($_GET['error'])) {
    // something went wrong - handle the error
    print_r($_GET['error']);
    exit;
} elseif (isset($_GET['code'])) {
    // we are returning with a code after authorization
    try {
        $penneoOAuth->exchangeAuthCode($_GET['code'], $_SESSION['code_verifier']);
    } catch (PenneoSDKException $e) {
        /// something went wrong - handle the error
        print_r($e);
        exit;
    }

    // optionally, handle the returned state
    print_r($_GET['state']);
} elseif (!$penneoOAuth->isAuthorized()) {
    // set up the code challenge
    $pkce = new PKCE();
    $codeVerifier = $pkce->getCodeVerifier();
    $_SESSION['code_verifier'] = $codeVerifier;

    try {
        // build the redirect URL for authorization
        $url = $penneoOAuth->buildRedirectUrl(
            ['full_access'],
            $pkce->getCodeChallenge($codeVerifier)
        );

        header('Location: ' . $url);
        exit;
    } catch (PenneoSDKException $e) {
        // something went wrong - handle the error
        var_dump($e);
    }
}

// the OAuth flow has finished, so we can start using the API
ApiConnector::initializeOAuth($penneoOAuth);

$casefile = new CaseFile();
$casefile->setTitle('new test casefile from PHP');
CaseFile::persist($casefile);
