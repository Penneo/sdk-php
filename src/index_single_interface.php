<?php

use Penneo\SDK\ApiConnector;
use Penneo\SDK\OAuth\CodeChallengeFactory;
use Penneo\SDK\OAuth\OAuthCodeExchanger;
use Penneo\SDK\OAuth\OAuthUrlBuilder;
use Penneo\SDK\OAuth\PenneoTokens;
use Penneo\SDK\OAuth\S256CodeChallenge;
use Penneo\SDK\OAuth\TokenStorage;

// user implemented storage mechanism for tokens
$tokenStorage = new class implements TokenStorage {
    function saveTokens(PenneoTokens $tokens) {
        $_SESSION['penneoTokens'] = $tokens->serialize();
    }

    function getTokens(): ?PenneoTokens {
        if ($_SESSION['penneoTokens']) {
            return PenneoTokens::deserialize($_SESSION['penneoTokens']);
        }
    }
};

// provide this as a default -> SessionTokenStorage implements TokenStorage

// if we're not authorized
if (!$tokenStorage->getTokens()) {
    // is it required in Pluto?
    // do we want to enforce the usage?
    $codeChallenge = new S256CodeChallenge();
    // store the code challenge so it can be used for verification later
    $_SESSION['code_challenge'] = $codeChallenge->serialize();

    $url = OAuthUrlBuilder::create()
        ->setEnvironment('sandbox') // do the developers have to care about the URLs? environment might be enough
        ->setClientId('someClientId')
        ->setClientSecret('someSecret')
        ->setRedirectUri('https://some-redirect-uri.com') // the URL they specified when asking for client credentials
        ->setState('optional state string')
        ->setCodeChallenge($codeChallenge)
        ->build();

    // we let the users get the URL so they can do whatever they want with it
    header("HTTP/1.1 301 Moved Temporarily");
    header("Location: ${url}");
    exit;
}

// when returning with a successful response
if (isset($_GET['code'])) {
    $codeChallenge = CodeChallengeFactory::fromSerialized(
        $_SESSION['code_challenge']
    );

    $tokenStorage->saveTokens(
        OAuthCodeExchanger::exchangeCode(
            $_GET['code'],
            $codeChallenge,
            // client ID
            // secret
        )
    );

    print_r($_GET['state']); // you can do whatever you'd like with the returned state
}

// when returning from pluto with an error (oh no!)
if (isset($_GET['error'])) {
    // something went wrong!
    print_r($_GET['error']);
}

// we are authorized, so we supply the token storage to the ApiConnector, which will refresh and update the
// tokens automatically (or delegate that responsibility to another class, but the developers don't need to worry about
// that)
ApiConnector::initializeOAuth($tokenStorage);

// do we need to expose a token refreshing method? I'm assuming that we don't.