<?php declare(strict_types=1);

namespace Cockpit\App\Controller;

use Cockpit\App\Assets\AssetRepository;
use Cockpit\Framework\Database\Constraint;
use Lime\App;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use function Cockpit\Controller\parent_sort;

final class Assets
{
    /** @var AssetRepository */
    private $assets;
    /** @var \Cockpit\App\Assets\FolderRepository */
    private $folders;
    /** @var \Cockpit\App\Assets\Uploader */
    private $uploader;
    /** @var \Cockpit\Framework\EventSystem */
    private $eventSystem;

    public function __construct(AssetRepository $assets, \Cockpit\App\Assets\FolderRepository $folders, \Cockpit\App\Assets\Uploader $uploader, \Cockpit\Framework\EventSystem $eventSystem)
    {
        $this->assets = $assets;
        $this->folders = $folders;
        $this->uploader = $uploader;
        $this->eventSystem = $eventSystem;
    }

    public function index()
    {
        return $this->render('cockpit:views/assets/index.php');
    }

    public function listAssets(RequestInterface $request)
    {
        $params = $request->getParsedBody();

        $contraint = new Constraint(
            $params['filter'] ?? $_REQUEST['filter'] ?? null,
            $params['limit'] ?? null,
            $params['sort'] ?? ['created' => -1],
            $params['skip'] ?? null
        );

        $assets = $this->assets->byConstraint($contraint);
        $this->eventSystem->trigger('cockpit.assets.list', [$assets]);

        // virtual folders
        $folderFilter = $params['folder'] ?? null;
        $filters = [];
        /*if ($folderFilter) {
            $filters = ['_p' => $folderFilter];
        }*/
        $folders = $this->folders->children(new Constraint($filters, null, ['name' => 1]), $folderFilter);

        return new JsonResponse(['assets' => $assets['assets'], 'folders' => $folders, 'total' => $assets['total']]);
    }

    public function asset($id)
    {
        return $this->assets->byId($id);
    }

    public function upload()
    {
        $meta = ['folder' => $params['folder'] ?? ''];
        $folder = $params['folder'] ?? null;
        $userID = $this->app->module('cockpit')->getUser('_id');

        $result = $this->uploader->upload('files', $folder, $userID);
        return $result;
        // Register uploaded assets

//        return $this->module('cockpit')->uploadAssets('files', $meta);
    }

    public function removeAssets()
    {
        if ($assets = $params['assets'] ?? false) {
            return $this->module('cockpit')->removeAssets($assets);
        }

        return false;
    }

    public function updateAsset()
    {
        if ($asset = $params['asset'] ?? false) {
            return $this->module('cockpit')->updateAssets($asset);
        }

        return false;
    }

    public function addFolder()
    {
        $name = $params['name'] ?? null;
        $parent = $params['parent'] ?? '';

        if (!$name) return;

        $folder = [
            'name' => $name,
            '_p' => $parent
        ];

        $this->app->storage->save('cockpit/assets_folders', $folder);

        return $folder;
    }

    public function renameFolder()
    {
        $folder = $params['folder'];
        $name = $params['name'];

        if (!$folder || !$name) {
            return false;
        }

        $folder['name'] = $name;

        $this->app->storage->save('cockpit/assets_folders', $folder);

        return $folder;
    }

    public function removeFolder()
    {
        $folder = $params['folder'];

        if (!$folder || !isset($folder['_id'])) {
            return false;
        }

        $ids = [$folder['_id']];
        $f = ['_id' => $folder['_id']];

        while ($f = $this->app->storage->findOne('cockpit/assets_folders', ['_p' => $f['_id']])) {
            $ids[] = $f['_id'];
        }

        $this->app->storage->remove('cockpit/assets_folders', ['_id' => ['$in' => $ids]]);

        return $ids;
    }

    public function _folders()
    {
        $_folders = $this->app->storage->find('cockpit/assets_folders', [
            'sort' => ['name' => 1]
        ])->toArray();

        $folders = $this->parent_sort($_folders);

        return $folders;
    }

    private function parent_sort(array $objects, array &$result = [], $parent = '', $depth = 0)
    {
        foreach ($objects as $key => $object) {

            if ($object['_p'] == $parent) {
                $object['_lvl'] = $depth;
                array_push($result, $object);
                unset($objects[$key]);
                $this->parent_sort($objects, $result, $object['_id'], $depth + 1);
            }
        }
        return $result;
    }
}
