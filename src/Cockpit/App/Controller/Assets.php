<?php declare(strict_types=1);

namespace Cockpit\App\Controller;

use Cockpit\App\Assets\Asset;
use Cockpit\App\Assets\AssetRepository;
use Cockpit\App\Assets\Folder;
use Cockpit\App\Assets\FolderRepository;
use Cockpit\App\Assets\Uploader;
use Cockpit\Framework\Database\Constraint;
use Cockpit\Framework\EventSystem;
use League\Flysystem\Filesystem;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Laminas\Diactoros\Response\JsonResponse;

final class Assets
{
    /** @var AssetRepository */
    private $assets;
    /** @var FolderRepository */
    private $folders;
    /** @var Uploader */
    private $uploader;
    /** @var EventSystem */
    private $eventSystem;
    /** @var Filesystem  */
    private $fileSystem;
    /** @var string */
    private $assetsAbsolutePath;

    public function __construct(AssetRepository $assets, FolderRepository $folders, Uploader $uploader, EventSystem $eventSystem, Filesystem $fileSystem, string $assetsAbsolutePath)
    {
        $this->assets = $assets;
        $this->folders = $folders;
        $this->uploader = $uploader;
        $this->eventSystem = $eventSystem;
        $this->fileSystem = $fileSystem;
        $this->assetsAbsolutePath = dirname($assetsAbsolutePath);
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

    public function asset(RequestInterface $request, ResponseInterface $response, $id)
    {
        $asset = $this->assets->byId($id);

        return new JsonResponse($asset);
    }

    public function upload(RequestInterface $request)
    {
        $params = $request->getParsedBody();
        $meta = ['folder' => $params['folder'] ?? ''];
        $folderID = $params['folder'] ?? null;
        $user = $request->getAttributes()[UserInterface::class];

        $folder = null;
        if ($folderID) {
            $folder = $this->folders->byID($folderID);
        }

        $result = $this->uploader->upload('files', $folder, $user);

        return $result;
        // Register uploaded assets

//        return $this->module('cockpit')->uploadAssets('files', $meta);
    }

    public function removeAssets(RequestInterface $request)
    {
        $params = $request->getParsedBody();
        $assets = $params['assets'] ?? false;
        if (!$assets) {
            return new HttpNotFoundException();
        }

        $assets = isset($assets[0]) ? $assets : [$assets];

        foreach($assets as &$inputAsset) {

            if (!isset($inputAsset['_id'])) {
                continue;
            }

            /** @var Asset|null $asset */
            $asset = $this->assets->byId($inputAsset['_id']);

            if (!$asset) {
                continue;
            }

            $this->assets->delete($asset['_id']);
            $this->fileSystem->delete($this->assetsAbsolutePath.'/'.$asset['path']);

//            if ($this->app->filestorage->has('assets://'.trim($asset['path'], '/'))) {
//                $this->app->filestorage->delete('assets://'.trim($asset['path'], '/'));
//            }
            $asset = null;
        }

        $this->eventSystem->trigger('cockpit.assets.remove', [$assets]);

        return $assets;
    }

    public function updateAsset(RequestInterface $request)
    {
        $params = $request->getParsedBody();
        $asset = $params['asset'] ?? false;
        if (!$asset) {
            return new HttpNotFoundException();
        }

        /** @var UserInterface $user */
        $user = $request->getAttributes()[UserInterface::class];
        $assets = isset($asset[0]) ? $asset : [$asset];

        foreach ($assets as &$asset) {

            $existingAsset = $this->assets->byId($asset['_id']);
            if (!$existingAsset) {
                continue;
            }

            $asset['modified'] = time();
            $asset['_by'] = $user->getDetail('id');

            $this->eventSystem->trigger('cockpit.asset.save', [&$asset]);
//            $this->assets->save($asset);
        }

        return new JsonResponse($assets);
    }

    public function addFolder(RequestInterface $request)
    {
        $params = $request->getParsedBody();
        $name = $params['name'] ?? null;
        $parent = $params['parent'] ?? '';

        if (!$name) {
            return;
        }

        $parentFolder = $this->folders->byID($parent);
        $folder = Folder::create($name, $parentFolder);

        $this->folders->save($folder);
//        $this->app->storage->save('cockpit/assets_folders', $folder);

        return new JsonResponse($folder->toArray());
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
        $folders = $this->folders->byConstraint(new Constraint(null, null, ['name' => 1]));

        $folders = $this->parent_sort($folders['folders']);

        return new JsonResponse($folders);
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
