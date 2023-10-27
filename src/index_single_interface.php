<?php

use Penneo\SDK\ApiConnector;
use Penneo\SDK\CaseFile;
use Penneo\SDK\OAuth\Environment;
use Penneo\SDK\OAuth\OAuthBuilder;
use Penneo\SDK\OAuth\SessionTokenStorage;
use Penneo\SDK\PenneoSDKException;

$tokenStorage = new SessionTokenStorage();

$penneoOAuth = OAuthBuilder::start()
    ->setEnvironment(Environment::SANDBOX)
    ->setClientId('someClientId')
    ->setClientSecret('someSecret')
    ->setRedirectUri('https://some-redirect-uri.com')
    ->setTokenStorage($tokenStorage)
    ->build();

if (isset($_GET['error'])) {
    // something went wrong!
    print_r($_GET['error']);
    exit;
}

// when returning with a successful response
if (isset($_GET['code'])) {
    $penneoOAuth->useCodeChallenge($_SESSION['code_challenge']);

    try {
        // this performs the exchange and stores it in the TokenStorage
        $penneoOAuth->exchangeAuthCode($_GET['code']);
    } catch (PenneoSDKException $e) {
        /// something went wrong
        print_r($e);
        exit;
   }

    print_r($_GET['state']); // you can do whatever you'd like with the returned state
}


// if we're not authorized
if (!$penneoOAuth->isAuthorized()) {
    // is code challenge required in Pluto?
    // do we want to enforce the usage of it, if not?
    // store the code challenge so it can be used for verification later
    $_SESSION['code_challenge'] = $penneoOAuth->setUpS256CodeChallenge();

    // maybe we want to have "redirect to auth" instead of building the URL
    try {
        $url = $penneoOAuth->buildRedirectUrl();

        // we let the users get the URL so they can do whatever they want with it
        header("HTTP/1.1 302 Moved Temporarily");
        header("Location: ${url}");
        exit;
    } catch (PenneoSDKException $e) {
        // something went wrong
    }
}

// we are authorized, so we supply the token storage to the ApiConnector, which will refresh and update the
// tokens automatically (or delegate that responsibility to another class, but the developers don't need to worry about
// that)
ApiConnector::initializeOAuth($tokenStorage);

$casefile = new CaseFile();
$casefile->setTitle('My Demo Casefile');
CaseFile::persist($casefile);