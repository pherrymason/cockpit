<?php declare(strict_types=1);

namespace Cockpit\App\Controller;

use Cockpit\App\Revisions\RevisionsRepository;
use Psr\Http\Message\RequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

final class Utils
{
    /** @var RevisionsRepository */
    private $revisions;
    /** @var \Cockpit\App\Assets\Thumbnail */
    private $thumbnail;

    public function __construct(\Cockpit\App\Revisions\RevisionsRepository $revisions, \Cockpit\App\Assets\Thumbnail $thumbnail)
    {
        $this->revisions = $revisions;
        $this->thumbnail = $thumbnail;
    }

    public function thumb_url()
    {
        \session_write_close(); // improve concurrency loading

        $options = [
            'src' => $this->param('src', false),
            'fp' => $this->param('fp', null),
            'mode' => $this->param('m', 'thumbnail'),
            'filters' => (array)$this->param('f', []),
            'width' => intval($this->param('w', null)),
            'height' => intval($this->param('h', null)),
            'quality' => intval($this->param('q', 85)),
            'rebuild' => intval($this->param('r', false)),
            'base64' => intval($this->param('b64', false)),
            'output' => intval($this->param('o', false)),
        ];

        // Set single filter when available
        foreach ([
                     'blur', 'brighten',
                     'colorize', 'contrast',
                     'darken', 'desaturate',
                     'edge detect', 'emboss',
                     'flip', 'invert', 'opacity', 'pixelate', 'sepia', 'sharpen', 'sketch'
                 ] as $f) {
            if ($this->param($f)) $options[$f] = $this->param($f);
        }

        return $this->thumbnail->thumbnailURL($options);
    }

    public function revisionsCount(RequestInterface $request)
    {
        $input = $request->getParsedBody();

        $cnt = 0;
        if ($id = $input['id']) {
            $cnt = $this->revisions->count($id);
        }

        return new JsonResponse(['count' => $cnt]);
    }
}
