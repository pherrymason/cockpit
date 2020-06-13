<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

use Cockpit\Framework\EventSystem;
use Cockpit\Framework\IDs;
use Cockpit\Framework\PathResolver;
use Cocur\Slugify\Slugify;
use League\Flysystem\Filesystem;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;

final class Uploader
{
    /** @var Filesystem */
    private $fileSystem;
    /** @var PathResolver */
    private $pathResolver;
    /** @var EventSystem */
    private $events;
    /** @var Slugify */
    private $slugify;
    /** @var array */
    private $allowedUploads;
    /** @var */
    private $maxUploadSize;
    /** @var AssetRepository */
    private $assets;
    /** @var FolderRepository */
    private $folders;

    public function __construct(Filesystem $fileSystem, PathResolver $pathResolver, AssetRepository $assets, FolderRepository $folders, EventSystem $events, Slugify $slugify, array $allowedUploads, $maxUploadSize)
    {
        $this->fileSystem = $fileSystem;
        $this->pathResolver = $pathResolver;
        $this->allowedUploads = $allowedUploads;
        $this->maxUploadSize = $maxUploadSize;
        $this->events = $events;
        $this->slugify = $slugify;
        $this->assets = $assets;
        $this->folders = $folders;
    }

    public function upload(string $key, Folder $folder, UserInterface $user)
    {
        /**
         * {"name":["limoncello.jpg"],"type":["image\/jpeg"],"tmp_name":["\/private\/var\/tmp\/phpBKEFUu"],"error":[0],"size":[35475]}
         */
        $files = $_FILES[$key] ?? [];
        $uploaded = [];
        $failed = [];
        $_files = [];
        $assets = [];

        $max_size = $this->maxUploadSize;

        if (!isset($files['name']) && is_array($files['name'])) {
            return false;
        }

        $meta = $folder !== null ? ['folder' => $folder] : [];

        $iMax = count($files['name']);
        for ($i = 0; $i < $iMax; $i++) {
            $pathinfo = pathinfo($files['name'][$i]);
            $cleanName = $this->slugify->slugify($pathinfo['filename']) . '.' . $pathinfo['extension'];

            $path = $folder->path() . '/' . $cleanName;
            $assetsPath = $this->pathResolver->assetsPath();

            $_isAllowed = $this->isFileTypeAllowed($files['tmp_name'][$i]);
            $filesize = filesize($files['tmp_name'][$i]);
            $_sizeAllowed = $max_size ? $filesize < $max_size : true;

            if ($files['error'][$i] || !$_isAllowed || !$_sizeAllowed) {
                $failed[] = $files['name'][$i];
                continue;
            }

            try {
//                @todo this SVG sanitizer is disabled temporarily, needs more investigation.
//                if (\preg_match('/\.(svg|xml)$/i', $_file)) {
//                    file_put_contents($_file, \SVGSanitizer::clean(\file_get_contents($_file)));
//                }
                $stream = fopen($files['tmp_name'][$i], 'r+');

                $asset = $this->createAsset(
                    $assetsPath . $path,
                    (string)$filesize,
                    mime_content_type($files['tmp_name'][$i]),
                    $user->getDetail('id'),
                    $folder
                );
                $assetArray = $asset->toArray();

                // @todo Changes done in assetArray are lost
                $opts = ['mimetype' => $asset->mime()];
                $this->events->trigger('cockpit.asset.upload', [&$assetArray, &$meta, &$opts]);
                $this->fileSystem->writeStream($assetsPath . $path, $stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                $assets[] = $asset;
                $uploaded[] = $files['name'][$i];
            } catch (\Exception $e) {
                // Error uploading file
                $failed[] = $files['name'][$i];
            }
        }

        $uploadedAssets = [];
        if (count($assets)) {
            $uploadedAssets = $_files;

            //$assets = $this->addAssets($_files, $meta);
            foreach ($assets as $asset) {
                $assetArray = $asset->toArray();

                // @todo Changes done in assetArray are lost
                $this->events->trigger('cockpit.asset.save', [&$assetArray]);
                $this->assets->save($asset);
            }

            foreach ($_files as $file) {
                unlink($file);
            }
        }

        return new JsonResponse([
            'uploaded' => $uploaded,
            'failed' => $failed,
            'assets' => array_map(
                function (Asset $asset) {
                    return $asset->toArray();
                },
                $assets
            )
        ]);
    }

    private function isFileTypeAllowed(string $filename): bool
    {
        if ($this->allowedUploads === ['*']) {
            return true;
        }

        $pattern = is_array($this->allowedUploads) ? implode(', ', $this->allowedUploads) : $this->allowedUploads;
        $pattern = preg_quote($pattern, '/');

        $matched = preg_match("/\.({$pattern})$/i", $filename);

        return $matched === 1;
    }

    private function createAsset(string $fullFilenamePath, string $filesize, string $mime, string $userID, Folder $folder): Asset
    {
        $filename = basename($fullFilenamePath);

        return new Asset(
            IDs::new(),
            $folder,
            $filename,
            $filename,
            '',
            [],
            (string)$filesize,
            $mime,
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            $userID
        );
    }
}
