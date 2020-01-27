<?php declare(strict_types=1);

namespace Cockpit\App\Assets;

use http\Exception\InvalidArgumentException;

final class Thumbnail
{
    public function thumbnailURL(array $options = [])
    {
        $options = array_merge($this->defaultOptions(), $options);

        if (!$options['width'] && !$options['height']) {
            throw new InvalidArgumentException('Target width/height parameter is missing.');
        }

        if (!$options['src']) {
            throw new InvalidArgumentException('Missing src parameter');
        }

        return $options['src'];

        // Is an asset?
        $src = $options['src'];
        $asset = null;

        /*
         -1 return $src

         1. if looks like an asset:
                - check if it's in our system
                - If not in our system, create fake asset array.

         2. if it does not look like an asset:
                - check if it's in our system anyway.
                - If it's not there jump to -1

         3. if src does not have image extension, $src must be an ID
                - check if in our system
        */


        if ($this->isAsset($src)) {

        }

        /*
        - If asset found:
            - if not in uploads folder, but in assets folder:
                - move it to uploads (damn...)


         */
        if ($asset) {

        }
    }

    private function defaultOptions(): array
    {
        return [
            'cachefolder' => 'thumbs://',
            'src' => '',
            'mode' => 'thumbnail',
            'fp' => null,
            'filters' => [],
            'width' => false,
            'height' => false,
            'quality' => 100,
            'rebuild' => false,
            'base64' => false,
            'output' => false
        ];
    }

    private function isAsset($src): bool
    {
        // is located in assets folder?
    }
}
