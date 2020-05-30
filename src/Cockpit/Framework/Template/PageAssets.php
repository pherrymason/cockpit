<?php

namespace Cockpit\Framework\Template;

final class PageAssets
{
    /** @var array */
    private $assets;

    public function __construct()
    {
        $this->assets = [];
    }

    public function addAsset(string $collection, string $asset): void
    {
        if (!isset($this->assets[$collection])) {
            $this->assets[$collection] = [];
        }

        $this->assets[$collection][] = $asset;
    }

    public function addAssets(string $collection, array $assets): void
    {
        if (!isset($this->assets[$collection])) {
            $this->assets[$collection] = [];
        }

        $this->assets[$collection] = array_merge($this->assets[$collection], $assets);
    }

    public function assets(string $collection): array
    {
        return $this->assets[$collection] ?? [];
    }
}
