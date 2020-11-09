<?php

declare(strict_types=1);

namespace Penneo\SDK\Tests;

class JsonArrayFile
{
    /** @var string */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @return object[]
     */
    public function get(): array
    {
        $data = file_get_contents($this->filePath) ?: '[]';
        $this->clear();

        return json_decode($data);
    }

    public function add(object $object): void
    {
        $data = $this->get();

        $data[] = $object;

        file_put_contents($this->filePath, json_encode($data));
    }

    public function __toString()
    {
        return $this->filePath;
    }

    public function clear(): void
    {
        file_put_contents($this->filePath, '[]');
    }
}
