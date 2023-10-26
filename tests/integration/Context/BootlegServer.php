<?php

declare(strict_types=1);

namespace Penneo\SDK\Tests\Integration;

use GuzzleHttp\Psr7\Request;

class BootlegServer
{
    /** @var resource|null */
    private $handle;
    /** @var JsonArrayFile */
    private $responseFile;
    /** @var JsonArrayFile */
    private $requestFile;
    /** @var string */
    private $url;

    public function __construct()
    {
        $this->responseFile = new JsonArrayFile($this->makeFile());
        $this->requestFile  = new JsonArrayFile($this->makeFile());

        $portNumber         = $this->getRandomPort();
        $address            = "127.0.0.1:$portNumber";
        $this->handle       = $this->runHttpServer(
            $address,
            (string) $this->requestFile,
            (string) $this->responseFile
        );

        $this->url = "http://$address/bootleg_server.php/";
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function enqueueResponse(int $statusCode, string $body): void
    {
        $this->responseFile->add((object) ['status' => $statusCode, 'body' => $body]);
    }

    /**
     * @return Request[]
     */
    public function readRequests(): array
    {
        $requests = $this->requestFile->get();

        return array_map(
            function ($request) {
                return new Request(
                    $this->requireString($request, 'method'),
                    $this->requireString($request, 'uri'),
                    [],
                    $this->requireString($request, 'body')
                );
            },
            $requests
        );
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close(): void
    {
        if ($this->handle) {
            proc_terminate($this->handle);
            $this->handle = null;
        }

        $this->responseFile->clear();
        $this->requestFile->clear();
    }

    /**
     * @param string $address
     * @param string $requestFile
     * @param string $responseFile
     *
     * @return resource
     */
    private function runHttpServer(string $address, string $requestFile, string $responseFile)
    {
        $handle = proc_open(
            PHP_BINARY . " -S " . escapeshellarg($address),
            [
                ['file', '/dev/null', 'r'], // stdin
                ['file', '/dev/null', 'w'], // stdout
                ['file', '/dev/null', 'w'], // stderr
            ],
            $_,
            __DIR__,
            [
                'PENNEO_REQUEST_FILE' => $requestFile,
                'PENNEO_RESPONSE_FILE' => $responseFile,
            ]
        );

        if (!$handle) {
            throw new \RuntimeException("Port probably taken");
        }

        return $handle;
    }

    private function requireString(object $request, string $field): string
    {
        if (!isset($request->{$field}) || !is_string($request->{$field})) {
            throw new \RuntimeException("Invalid $field on request");
        }

        return $request->{$field};
    }

    private function getRandomPort(): int
    {
        return range(16666, 17000)[random_int(0, 17000 - 16666)];
    }

    private function makeFile(): string
    {
        return tempnam('/tmp/', 'penneosdk-test-');
    }
}
