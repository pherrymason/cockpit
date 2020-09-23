<?php declare(strict_types=1);

namespace Cockpit\Framework;

final class PathResolver
{
    /** @var string[] */
    private $pathMap;
    /** @var string|null */
    private $siteURL;
    /** @var string */
    private $docsRoot;
    /** @var string */
    private $baseURL;

    public function __construct(array $pathMap, string $docsRoot, string $baseURL, ?string $siteURL)
    {
        $this->pathMap = [];
        foreach ($pathMap as $key => $path) {
            $path = rtrim($path,  DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $this->pathMap[$key] = [$path];
        }

        $this->siteURL = $siteURL;
        $this->docsRoot = $docsRoot;
        $this->baseURL = $baseURL;
    }

    public function paths($namespace = null)
    {
        if (!$namespace) {
            return $this->pathMap;
        }

        return $this->pathMap[$namespace] ?? [];
    }

    public function relativePath(string $file)
    {
        $path = $this->path($file);

        return str_replace($this->docsRoot, '', $path);
    }

    public function assetsPath(): string
    {
        $path = rtrim($this->pathMap['assets'][0], '/');

        // Fix for systems with open_basedir restriction in effect
        return str_replace($this->docsRoot, '', $path);
    }

    /**
     * @return false|string
     */
    public function path(string $file)
    {
        if ($this->isAbsolutePath($file) && \file_exists($file)) {
            return $file;
        }

        $parts = \explode(':', $file, 2);

        if (count($parts) === 2) {
            if (!isset($this->pathMap[$parts[0]])) {
                return false;
            }

            foreach ($this->pathMap[$parts[0]] as $path) {
                if (\file_exists($path . $parts[1])) {
                    return rtrim($path . $parts[1], '/');
                }
            }
        }

        return isset($this->pathMap[$file]) ? rtrim($this->pathMap[$file], '/') : null;
    }

    public function setPath($a, $b)
    {
        if (!isset($this->paths[$a])) {
            $this->pathMap[$a] = [];
        }
        \array_unshift($this->pathMap[$a], \rtrim(\str_replace(DIRECTORY_SEPARATOR, '/', $b), '/') . '/');
    }

    public function isAbsolutePath(string $path): bool
    {
        return strpos($path, '/') === 0
            || '\\' === $path[0]
            || (3 < \strlen($path) && \ctype_alpha($path[0]) && $path[1] === ':' && ('\\' === $path[2] || '/' === $path[2]));
    }

    /**
     * @bug: cannot convert to URL a path that goes below $root.
     */
    public function pathToUrl($path, $full = false)
    {
        $url = false;

        if ($file = $this->path($path)) {

            $file = \str_replace(DIRECTORY_SEPARATOR, '/', $file);
            $root = \str_replace(DIRECTORY_SEPARATOR, '/', $this->docsRoot);

            $url = rtrim($this->baseURL, '/') . '/';

            if (!$this->isNotParentPath($root, $file)) {
                $url.= '/' . \ltrim(\str_replace($root, '', $file), '/');
                $url = \implode('/', \array_map('rawurlencode', explode('/', $url)));
            }

            if ($full) {
                $url = \rtrim($this->siteURL, '/') . $url;
            }
        }

        return $url;
    }

    private function isNotParentPath(string $parentPath, string $childPath): bool
    {
        $parentSeparators = count(explode(DIRECTORY_SEPARATOR, $parentPath));
        $childSeparators = count(explode(DIRECTORY_SEPARATOR, $childPath));

        // Parent should have more / than child
        if ($parentSeparators < $childSeparators) {
            return false;
        }

        // Check if childPath is below parentPath
        if (strpos($parentPath, $childPath) === 0) {
            return true;
        }

        return false;
    }
}
