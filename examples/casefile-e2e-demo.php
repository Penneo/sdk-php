#!/usr/bin/env php
<?php
/**
 * End-to-end demo: build a case file with the Penneo SDK (multiple entity types).
 *
 * Run from the repository root after `composer install`:
 *   php examples/casefile-e2e-demo.php
 *
 * Authentication (pick one):
 *   WSSE (default API v1 sandbox):
 *     PENNEO_WSSE_KEY=... PENNEO_WSSE_SECRET=... [PENNEO_WSSE_USER=...] [PENNEO_API_BASE=https://sandbox.penneo.com/api/v1/]
 *
 *   OAuth programmatic (API v3):
 *     PENNEO_AUTH=oauth PENNEO_OAUTH_ENV=sandbox \
 *       PENNEO_CLIENT_ID=... PENNEO_CLIENT_SECRET=... PENNEO_API_KEY=... PENNEO_API_SECRET=...
 *
 * Optional:
 *   PENNEO_DEMO_SIGNER_EMAIL=you@example.com   (defaults to demo@example.invalid)
 *   PENNEO_DEMO_PDF=/path/to/file.pdf          (defaults to a minimal generated PDF)
 */

declare(strict_types=1);

use Penneo\SDK\ApiConnector;
use Penneo\SDK\CaseFile;
use Penneo\SDK\CopyRecipient;
use Penneo\SDK\Document;
use Penneo\SDK\Folder;
use Penneo\SDK\InsecureSigningMethods;
use Penneo\SDK\OAuth\OAuthBuilder;
use Penneo\SDK\OAuth\Tokens\SessionTokenStorage;
use Penneo\SDK\SignatureLine;
use Penneo\SDK\Signer;
use Penneo\SDK\SigningRequest;

require dirname(__DIR__) . '/vendor/autoload.php';

function fail(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
    exit(1);
}

/**
 * Minimal valid-enough PDF for sandbox uploads (one blank page).
 */
function createTempDemoPdf(): string
{
    $path = sys_get_temp_dir() . '/penneo-sdk-demo-' . uniqid('', true) . '.pdf';
    $pdf = "%PDF-1.4\n"
        . "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
        . "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
        . "3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R>>endobj\n"
        . "trailer<</Size 4/Root 1 0 R>>\n"
        . "%%EOF\n";
    if (file_put_contents($path, $pdf) === false) {
        fail('Could not write temporary PDF.');
    }
    return $path;
}

function getenvOrDefault(string $key, string $default): string
{
    $v = getenv($key);
    return ($v !== false && $v !== '') ? $v : $default;
}

function printStep(string $label): void
{
    echo "\n=== {$label} ===\n";
}

$demoPdf = getenv('PENNEO_DEMO_PDF') ?: null;
$tmpPdf = null;
if ($demoPdf === null || !is_readable($demoPdf)) {
    $tmpPdf = createTempDemoPdf();
    $demoPdf = $tmpPdf;
}

register_shutdown_function(static function () use ($tmpPdf): void {
    if ($tmpPdf !== null && is_file($tmpPdf)) {
        @unlink($tmpPdf);
    }
});

$auth = strtolower(getenvOrDefault('PENNEO_AUTH', 'wsse'));

ApiConnector::throwExceptions(true);

if ($auth === 'oauth') {
    $env = getenvOrDefault('PENNEO_OAUTH_ENV', 'sandbox');
    $clientId = getenv('PENNEO_CLIENT_ID');
    if ($clientId === false || $clientId === '') {
        fail('Set PENNEO_CLIENT_ID for OAuth mode.');
    }
    $clientSecret = getenv('PENNEO_CLIENT_SECRET');
    if ($clientSecret === false || $clientSecret === '') {
        fail('Set PENNEO_CLIENT_SECRET for OAuth mode.');
    }
    $apiKey = getenv('PENNEO_API_KEY');
    if ($apiKey === false || $apiKey === '') {
        fail('Set PENNEO_API_KEY for OAuth mode.');
    }
    $apiSecret = getenv('PENNEO_API_SECRET');
    if ($apiSecret === false || $apiSecret === '') {
        fail('Set PENNEO_API_SECRET for OAuth mode.');
    }

    $oauth = OAuthBuilder::start()
        ->setEnvironment($env)
        ->setClientId($clientId)
        ->setClientSecret($clientSecret)
        ->setTokenStorage(new SessionTokenStorage())
        ->setApiKey($apiKey)
        ->setApiSecret($apiSecret)
        ->build();

    ApiConnector::initializeOAuth($oauth, 'v3');
} else {
    $key = getenv('PENNEO_WSSE_KEY');
    if ($key === false || $key === '') {
        fail('Set PENNEO_WSSE_KEY (or use PENNEO_AUTH=oauth).');
    }
    $secret = getenv('PENNEO_WSSE_SECRET');
    if ($secret === false || $secret === '') {
        fail('Set PENNEO_WSSE_SECRET.');
    }
    $base = getenv('PENNEO_API_BASE') ?: null;
    $user = getenv('PENNEO_WSSE_USER');
    $userId = ($user !== false && $user !== '') ? (int) $user : null;

    ApiConnector::initializeWsse($key, $secret, $base, $userId);
}

$signerEmail = getenvOrDefault('PENNEO_DEMO_SIGNER_EMAIL', 'demo.signer@example.invalid');
$copyEmail = getenvOrDefault('PENNEO_DEMO_COPY_EMAIL', 'copy.recipient@example.invalid');

try {
    printStep('Folder (Folder)');
    $folder = new Folder();
    $folder->setTitle('SDK demo folder ' . gmdate('Y-m-d H:i:s') . ' UTC');
    Folder::persist($folder);
    echo 'Folder id: ' . $folder->getId() . PHP_EOL;

    printStep('Case file (CaseFile)');
    $caseFile = new CaseFile();
    $caseFile->setTitle('SDK E2E demo ' . gmdate('c'));
    $caseFile->setLanguage('en');
    $caseFile->setMetaData('sdk-demo=e2e');
    $caseFile->setReference('ref-sdk-e2e-' . uniqid());
    $caseFile->setDocumentDisplayMode(CaseFile::DISPLAY_MODE_TABBED);
    $expire = new DateTime('now', new DateTimeZone('UTC'));
    $expire->modify('+30 days');
    $caseFile->setExpireAt($expire);
    CaseFile::persist($caseFile);
    echo 'Case file id: ' . $caseFile->getId() . ', status: ' . $caseFile->getStatus() . PHP_EOL;

    printStep('Link case file to folder');
    $folder->addCaseFile($caseFile);
    $inFolder = $folder->getCaseFiles();
    echo 'Case files in folder: ' . count($inFolder) . PHP_EOL;

    printStep('Optional: case file templates & types (CaseFileTemplate, DocumentType, SignerType)');
    try {
        $templates = $caseFile->getCaseFileTemplates();
        echo 'Case file templates available: ' . count($templates) . PHP_EOL;
    } catch (Throwable $e) {
        echo '(skip templates: ' . $e->getMessage() . ')' . PHP_EOL;
    }
    try {
        $docTypes = $caseFile->getDocumentTypes();
        echo 'Document types on case file: ' . count($docTypes) . PHP_EOL;
    } catch (Throwable $e) {
        echo '(skip document types: ' . $e->getMessage() . ')' . PHP_EOL;
    }
    $signerTypes = [];
    try {
        $signerTypes = $caseFile->getSignerTypes();
        echo 'Signer types on case file: ' . count($signerTypes) . PHP_EOL;
    } catch (Throwable $e) {
        echo '(skip signer types: ' . $e->getMessage() . ')' . PHP_EOL;
    }

    printStep('Annex document (Document, attachment)');
    $annex = new Document($caseFile);
    $annex->setTitle('Annex (not signable)');
    $annex->setPdfFile($demoPdf);
    Document::persist($annex);
    echo 'Annex document id: ' . $annex->getId() . PHP_EOL;

    printStep('Signable document (Document::makeSignable)');
    $document = new Document($caseFile);
    $document->setTitle('Signable agreement');
    $document->setPdfFile($demoPdf);
    $document->makeSignable();
    Document::persist($document);
    echo 'Signable document id: ' . $document->getId() . PHP_EOL;

    printStep('Signer (Signer)');
    $signer = new Signer($caseFile);
    $signer->setName('Demo Signer');
    $signer->setOnBehalfOf('Demo Org');
    $signer->setStoreAsContact(false);
    Signer::persist($signer);
    echo 'Signer id: ' . $signer->getId() . PHP_EOL;

    if ($signerTypes !== []) {
        $firstType = $signerTypes[0];
        echo 'Linking signer type: ' . $firstType->getName() . PHP_EOL;
        $signer->addSignerType($firstType);
    }

    printStep('Signature line + link (SignatureLine::setSigner → Signer)');
    $line = new SignatureLine($document);
    $line->setRole('signer');
    $line->setSignOrder(1);
    SignatureLine::persist($line);
    $line->setSigner($signer);
    echo 'Signature line id: ' . $line->getId() . PHP_EOL;

    printStep('Signing request (SigningRequest)');
    $signingRequest = $signer->getSigningRequest();
    if ($signingRequest === null) {
        fail('Signer::getSigningRequest returned null.');
    }
    $signingRequest->setEmail($signerEmail);
    $signingRequest->setEmailSubject('Please sign (SDK demo)');
    $signingRequest->setEmailText('This is an automated SDK demo message.');
    $signingRequest->setReminderInterval(7);
    $signingRequest->setSuccessUrl('https://example.com/signed');
    $signingRequest->setFailUrl('https://example.com/failed');
    $signingRequest->setEnableInsecureSigning(true);
    $signingRequest->setInsecureSigningMethods([
        InsecureSigningMethods::DRAW,
        InsecureSigningMethods::TEXT,
    ]);
    SigningRequest::persist($signingRequest);
    echo 'Signing request status: ' . $signingRequest->getStatus() . PHP_EOL;

    printStep('Copy recipient (CopyRecipient)');
    $copy = new CopyRecipient($caseFile);
    $copy->setName('Copy Recipient');
    $copy->setEmail($copyEmail);
    CopyRecipient::persist($copy);
    echo 'Copy recipient id: ' . $copy->getId() . PHP_EOL;

    printStep('Activate case file (CaseFile::activate — no outbound send)');
    $caseFile->activate();
    echo 'Case file status after activate: ' . $caseFile->getStatus() . PHP_EOL;

    printStep('Round-trip reads');
    $docs = $caseFile->getDocuments();
    echo 'Documents on case: ' . (is_array($docs) ? count($docs) : 0) . PHP_EOL;
    $signers = $caseFile->getSigners();
    echo 'Signers on case: ' . (is_array($signers) ? count($signers) : 0) . PHP_EOL;
    $copies = $caseFile->getCopyRecipients();
    echo 'Copy recipients: ' . (is_array($copies) ? count($copies) : 0) . PHP_EOL;

    $link = $signingRequest->getLink();
    echo "\nSigning link:\n{$link}\n";

    printStep('Download document content (Document::getContent)');
    try {
        $binary = $document->getContent();
        echo 'Downloaded document binary: ' . strlen($binary) . " bytes\n";
        $format = $document->getFormat();
        echo 'Document format: ' . ($format ?? 'n/a') . PHP_EOL;
    } catch (Throwable $e) {
        echo '(skip content download: ' . $e->getMessage() . ')' . PHP_EOL;
    }

    try {
        $log = $signer->getEventLog();
        echo 'Signer event log entries (LogEntry): ' . count($log) . PHP_EOL;
    } catch (Throwable $e) {
        echo '(skip signer event log: ' . $e->getMessage() . ')' . PHP_EOL;
    }

    echo "\nDone. Case file id {$caseFile->getId()}, folder id {$folder->getId()}.\n";
} catch (Throwable $e) {
    fail('Error: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
}
