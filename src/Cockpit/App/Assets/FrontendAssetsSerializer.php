<?php

namespace Cockpit\App\Assets;

class FrontendAssetsSerializer
{
    public function serializeCollection(array $assets): array
    {
        $serialized = array_map(function (Asset $asset) {
            return [
                '_id' => $asset->id(),
                'folder' => $asset->folderId(),
                'path' => $asset->path(),
                'title' => $asset->title(),
                'mime' => $asset->mime(),
                'description' => $asset->description(),
                'tags' => $asset->tags(),
                'size' => $asset->size(),
                'type' => $asset->type(),
                'image' => $asset->isImage(),
                'video' => $asset->isVideo(),
                'audio' => $asset->isAudio(),
                'archive' => $asset->isArchive(),
                'document' => $asset->isDocument(),
                'code' => $asset->isCode(),
                'width' => $asset->width(),
                'height' => $asset->height(),
                'colors' => $asset->colors(),
                'created' => $asset->created()->format('Y-m-d H:i:s'),
                'modified' => $asset->modified()->format('Y-m-d H:i:s'),
                '_by' => [
                    '_id' => $asset->authorId(),
                    'name' => $asset->authorName(),
                    'email' => $asset->authorEmail(),
                    'emailHash' => md5($asset->authorEmail())
                ]
            ];
        }, $assets);

        return $serialized;
    }
}