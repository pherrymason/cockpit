<?php

namespace Cockpit\App\Assets;

class FrontendAssetsSerializer
{
    public function serializeCollection(array $assets): array
    {
        $serialized = array_map(function ($asset) {
            return [
                '_id' => $asset['_id'],
                'folder' => $asset['folder'],
                'path' => $asset['path'],
                'title' => $asset['title'],
                'mime' => $asset['mime'],
                'description' => $asset['description'],
                'tags' => json_decode($asset['tags']),
                'size' => $asset['size'],
                'image' => $asset['image'],
                'video' => $asset['video'],
                'audio' => $asset['audio'],
                'archive' => $asset['archive'],
                'document' => $asset['document'],
                'code' => $asset['code'],
                'width' => $asset['width'],
                'height' => $asset['height'],
                'colors' => json_decode($asset['colors']),
                'created' => $asset['created'],
                'modified' => $asset['modified'],
                '_by' => [
                    '_id' => $asset['userId'],
                    'name' => $asset['userName'],
                    'email' => $asset['userEmail'],
                    'emailHash' => md5($asset['userEmail'])
                ]
            ];
        }, $assets);

        return $serialized;
    }
}