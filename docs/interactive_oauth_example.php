<?php

/**
 * Interactive OAuth (PKCE): open this script in a browser after starting a local server.
 *
 * 1) Register this exact Redirect URI in Penneo (OAuth client config):
 *    http://127.0.0.1:8080/interactive_oauth_example.php
 *    (or http://localhost:8080/... — must match character-for-character what you open in the browser)
 *
 * 2) From repository root:
 *    export PENNEO_OAUTH_CLIENT_ID="..."
 *    export PENNEO_OAUTH_CLIENT_SECRET="..."
 *    php -S 127.0.0.1:8080 -t docs
 *
 * 3) Open in browser: http://127.0.0.1:8080/interactive_oauth_example.php
 *
 * Optional: PENNEO_OAUTH_REDIRECT_URI — if unset, defaults to 127.0.0.1 URL above.
 * Optional: PENNEO_OAUTH_ENV=sandbox|production (default sandbox).
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Penneo\SDK\ApiConnector;
use Penneo\SDK\CaseFile;
use Penneo\SDK\OAuth\Config\Environment;
use Penneo\SDK\OAuth\OAuthBuilder;
use Penneo\SDK\OAuth\PKCE\PKCE;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;
use Penneo\SDK\PenneoSdkRuntimeException;

session_start();

function interactiveOauthFail(string $message): void
{
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $message . PHP_EOL);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
    }
    exit(1);
}

$clientId = getenv('PENNEO_OAUTH_CLIENT_ID') ?: '';
$clientSecret = getenv('PENNEO_OAUTH_CLIENT_SECRET') ?: '';
$redirectUri = getenv('PENNEO_OAUTH_REDIRECT_URI') ?: 'http://127.0.0.1:8080/interactive_oauth_example.php';
$environment = getenv('PENNEO_OAUTH_ENV') ?: Environment::SANDBOX;

if ($clientId === '' || $clientSecret === '') {
    interactiveOauthFail(
        "Set environment variables before running:\n"
        . "  export PENNEO_OAUTH_CLIENT_ID='...'\n"
        . "  export PENNEO_OAUTH_CLIENT_SECRET='...'\n"
        . "Optional:\n"
        . "  export PENNEO_OAUTH_REDIRECT_URI='http://127.0.0.1:8080/interactive_oauth_example.php'\n"
        . "  export PENNEO_OAUTH_ENV=sandbox\n"
        . "\n"
        . 'The redirect URI must be registered identically in your Penneo OAuth client.'
    );
}

if (!Environment::isSupported($environment)) {
    interactiveOauthFail("PENNEO_OAUTH_ENV must be 'sandbox' or 'production'. Got: {$environment}");
}

$tokenStorage = new SessionTokenStorage('optionalKeyToPlaceTokensInto');

$penneoOAuth = OAuthBuilder::start()
    ->setEnvironment($environment)
    ->setClientId($clientId)
    ->setClientSecret($clientSecret)
    ->setRedirectUri($redirectUri)
    ->setTokenStorage($tokenStorage)
    ->build();

if (isset($_GET['error'])) {
    $detail = $_GET['error_description'] ?? '';
    header('Content-Type: text/plain; charset=utf-8');
    echo 'OAuth error: ' . $_GET['error'] . ($detail !== '' ? "\n" . $detail : '');
    exit(1);
}

if (isset($_GET['code'])) {
    if (empty($_SESSION['code_verifier'])) {
        interactiveOauthFail(
            "Missing PKCE code_verifier in session. Open this URL first in the same browser (no private window switch):\n"
            . $redirectUri
        );
    }
    try {
        $penneoOAuth->exchangeAuthCode($_GET['code'], $_SESSION['code_verifier']);
    } catch (PenneoSdkRuntimeException $e) {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Token exchange failed: ' . $e->getMessage();
        exit(1);
    }
} elseif (!$penneoOAuth->isAuthorized()) {
    $pkce = new PKCE();
    $codeVerifier = $pkce->getCodeVerifier();
    $_SESSION['code_verifier'] = $codeVerifier;

    try {
        $url = $penneoOAuth->buildRedirectUrl(
            ['full_access'],
            $pkce->getCodeChallenge($codeVerifier)
        );

        if (PHP_SAPI === 'cli') {
            fwrite(
                STDOUT,
                "Open in a browser (after: php -S 127.0.0.1:8080 -t docs):\n"
                . $redirectUri . "\n\n"
                . "Or paste this authorize URL:\n" . $url . "\n"
            );
            exit(0);
        }

        header('Location: ' . $url);
        exit;
    } catch (PenneoSdkRuntimeException $e) {
        if (PHP_SAPI === 'cli') {
            var_dump($e);
            exit(1);
        }
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Could not build authorize URL: ' . $e->getMessage();
        exit(1);
    }
}

ApiConnector::initializeOAuth($penneoOAuth);

$casefile = new CaseFile();
$casefile->setTitle('new test casefile from PHP');
CaseFile::persist($casefile);

header('Content-Type: text/plain; charset=utf-8');
echo 'OK — Case file created. id=' . (string) $casefile->getId() . PHP_EOL;
