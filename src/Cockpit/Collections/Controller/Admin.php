<?php declare(strict_types=1);

namespace Cockpit\Collections\Controller;

use Cockpit\Collections\Collection;
use Cockpit\Collections\CollectionRepository;
use Cockpit\Collections\EntriesRepository;
use Cockpit\Collections\Entry;
use Cockpit\Collections\Role;
use Cockpit\App\Revisions;
use Cockpit\Framework\EventSystem;
use Cockpit\Framework\TemplateController;
use League\Plates\Engine;
use Mezzio\Authentication\UserInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

final class Admin extends TemplateController
{
    /** @var CollectionRepository */
    private $collections;
    /** @var EntriesRepository */
    private $entries;
    /** @var Revisions */
    private $revisions;
    /** @var EventSystem */
    private $eventSystem;

    public function __construct(CollectionRepository $collections, EntriesRepository $entries, Revisions $revisions, Engine $engine, EventSystem $eventSystem, ContainerInterface $container)
    {
        $this->collections = $collections;
        $this->entries = $entries;
        $this->revisions = $revisions;
        $this->eventSystem = $eventSystem;
        parent::__construct($engine, $container);
    }

    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $collections = $this->collections->all();
        $frontendData = array_map(
            function (Collection $collection) {
                return $collection->toArray();
            },
            $collections
        );

        return $this->renderResponse($request, 'collections::views/index', ['collections' => $frontendData]);
    }

    public function collection($name = null)
    {
        if ($name) {
            /** @var string $name */
            $collection = $this->collections->byName($name);
        } else {
            $collection = Collection::create();
        }

        if (($name && !$collection->hasAccess(Role::EDIT)) || (!$name && !$collection->hasAccess(Role::CREATE))) {
            return $this->helper('admin')->denyRequest();
        }

        /*
                if ($name && !$this->module('collections')->hasaccess($name, 'collection_edit')) {
                    return $this->helper('admin')->denyRequest();
                }

                if (!$name && !$this->module('cockpit')->hasaccess('collections', 'create')) {
                    return $this->helper('admin')->denyRequest();
                }
        */
        /*
                $collection = [
                    'name' => '',
                    'label' => '',
                    'color' => '',
                    'fields'=> [],
                    'acl' => new \ArrayObject,
                    'sortable' => false,
                    'in_menu' => false
                ];
        */

        if (!$name) {
            $collection = Collection::create();
        } else {
            //$collection = $this->module('collections')->collection($name);
            /** @var string $name */
            $collection = $this->collections->byName($name);

            if (!$collection) {
                return false;
            }

            /*
            if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($collection['_id'], $meta)) {
                return $this->render('cockpit:views/base/locked.php', compact('meta'));
            }
            */

            //$this->app->helper('admin')->lockResourceId($collection['_id']);
        }

        // get field templates
        $templates = [];

        foreach ($this->app->helper('fs')->ls('*.php', 'collections:fields-templates') as $file) {
            $templates[] = include($file->getRealPath());
        }

        foreach ($this->app->module('collections')->collections() as $col) {
            $templates[] = $col;
        }

        // acl groups
        $aclgroups = [];

        foreach ($this->app->helper('acl')->getGroups() as $group => $superAdmin) {

            if (!$superAdmin) $aclgroups[] = $group;
        }

        // rules
        $rules = [
            'create' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read(
                "#storage:collections/rules/{$name}.create.php"
            ),
            'read' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read(
                "#storage:collections/rules/{$name}.read.php"
            ),
            'update' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read(
                "#storage:collections/rules/{$name}.update.php"
            ),
            'delete' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read(
                "#storage:collections/rules/{$name}.delete.php"
            ),
        ];

        return $this->render(
            'collections:views/collection.php',
            [
                'collection' => $collection->toArray(),
                'templates' => $templates,
                'aclgroups' => $aclgroups,
                'rules' => $rules
            ]
        );
    }

    public function save_collection()
    {
        $collection = $this->param('collection');
        $rules = $this->param('rules', null);

        if (!$collection) {
            return false;
        }

        $isUpdate = isset($collection['_id']);
        /*
        // @todo
        if (!$isUpdate && !$this->module('cockpit')->hasaccess('collections', 'create')) {
            return $this->helper('admin')->denyRequest();
        }

        // @todo
        if ($isUpdate && !$this->module('collections')->hasaccess($collection['name'], 'collection_edit')) {
            return $this->helper('admin')->denyRequest();
        }
        // @todo
        if ($isUpdate && !$this->app->helper('admin')->isResourceEditableByCurrentUser($collection['_id'])) {
            $this->stop(['error' => "Saving failed! Collection is locked!"], 412);
        }
        */

        $this->collections->save($collection);
        //$collection = $this->module('collections')->saveCollection($collection['name'], $collection, $rules);

        if (!$isUpdate) {
//            $this->app->helper('admin')->lockResourceId($collection['_id']);
        }

        return $collection;
    }

    public function entries(RequestInterface $request, ResponseInterface $response, string $name)
    {
//        if (!$this->module('collections')->hasaccess($collection, 'entries_view')) {
//            return $this->helper('admin')->denyRequest();
//        }

        $collection = $this->collections->byName($name);

        if (!$collection) {
            return false;
        }

//        $collection = array_merge([
//            'sortable' => false,
//            'color' => '',
//            'icon' => '',
//            'description' => ''
//        ], $collection);

//        $context = _check_collection_rule($collection, 'read', ['options' => ['filter'=>[]]]);

        /*$this->app->helper('admin')->favicon = [
            'path' => 'collections:icon.svg',
            'color' => $collection->color()
        ];*/
//        if ($context && isset($context->options['fields'])) {
//            foreach ($collection['fields'] as &$field) {
//                if (isset($context->options['fields'][$field['name']]) && !$context->options['fields'][$field['name']]) {
//                    $field['lst'] = false;
//                }
//            }
//        }

        $view = 'collections::views/entries';
//      Allow different template for this specific collection
//        if ($override = $this->app->path('#config:collections/' . $collection->name() . '/views/entries.php')) {
//            $view = $override;
//        }

        return $this->renderResponse(
            $request,
            $view,
            [
                'collection' => $collection->toArray()
            ]
        );
    }

    public function find(RequestInterface $request)
    {
        $params = $request->getParsedBody();

        $collectionName = $params['collection'];
        $options = $params['options'];

        if (!$collectionName) {
            return false;
        }

        $collection = $this->collections->byName($collectionName);
        if (!$collection) {
            return false;
        }
//        $collection = $this->app->module('collections')->collection($collection);

//        if (isset($options['filter']) && is_string($options['filter'])) {
//
//            if (\preg_match('/^\{(.*)\}$/', $options['filter']) && $filter = json_decode($options['filter'], true)) {
//                $options['filter'] = $filter;
//            } else {
//                $options['filter'] = $this->_filter($options['filter'], $collection, $options['lang'] ?? null);
//            }
//        }

        $this->eventSystem->trigger("collections.admin.find.before.{$collection->name()}", [&$options]);
        $entries = $this->entries->byCollectionFiltered($collection, [], $options);

        $this->eventSystem->trigger("collections.admin.find.after.{$collection->name()}", [&$entries, $options]);

        $count = $this->entries->count($collection, $options['filter'] ?? []);

        $pages = isset($options['limit']) ? ceil($count / $options['limit']) : 1;
        $page = 1;

        if ($pages > 1 && isset($options['skip'])) {
            $page = ceil($options['skip'] / $options['limit']) + 1;
        }

        return new JsonResponse([
            'entries' => array_map(
                function (Entry $entry) {
                    return $entry->toArray();
                },
                $entries
            ),
            'count' => $count,
            'pages' => $pages,
            'page' => $page]
        );
    }

    public function entry(RequestInterface $request, ResponseInterface $response, $name, $id = null)
    {
//        if ($id && !$this->module('collections')->hasaccess($collection, 'entries_view')) {
//            return $this->helper('admin')->denyRequest();
//        }

//        if (!$id && !$this->module('collections')->hasaccess($collection, 'entries_create')) {
//            return $this->helper('admin')->denyRequest();
//        }

        $collection = $this->collections->byName($name);
        $excludeFields = [];

        if (!$collection) {
            return false;
        }

//        $collection = array_merge([
//            'sortable' => false,
//            'color' => '',
//            'icon' => '',
//            'description' => ''
//        ], $collection);

        /*$this->app->helper('admin')->favicon = [
            'path' => 'collections:icon.svg',
            'color' => $collection->color()
        ];*/

        if ($id) {
            $entry = $this->entries->byId($collection, $id);
//            $entry = $this->module('collections')->findOne($collection['name'], ['_id' => $id]);
            //$entry = $this->app->storage->findOne("collections/{$collection['_id']}", ['_id' => $id]);

            if (!$entry) {
                throw new HttpNotFoundException();
                //return cockpit()->helper('admin')->denyRequest();
            }

//            if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($id, $meta)) {
//                return $this->render('collections:views/locked.php', compact('meta', 'collection', 'entry'));
//            }
//
//            $this->app->helper('admin')->lockResourceId($id);
            $entryAsArray = $entry->toArray();
        } else {
            $entryAsArray = [];
        }

//        $context = _check_collection_rule($collection, 'read', ['options' => ['filter'=>[]]]);
//
//        if ($context && isset($context->options['fields'])) {
//            foreach ($context->options['fields'] as $field => $include) {
//                if(!$include) $excludeFields[] = $field;
//            }
//        }

        $view = 'collections::views/entry';

       // if ($override = $this->app->path('#config:collections/' . $collection->name() . '/views/entry.php')) {
       //     $view = $override;
       //}

        return $this->renderResponse(
            $request,
            $view,
            [
                'collection' => $collection->toArray(),
                'entry' => $entryAsArray,
                'excludeFields' => $excludeFields
            ]
        );
    }

    public function save_entry(ServerRequestInterface $request, ResponseInterface $response, $name)
    {
        $collection = $this->collections->byName($name);

        if (!$collection) {
            return false;
        }

        $params = $request->getParsedBody();
        $entry = $params['entry'] ?? false;

        if (!$entry) {
            return false;
        }

//        if (!isset($entry['_id']) && !$this->module('collections')->hasaccess($collection['name'], 'entries_create')) {
//            return $this->helper('admin')->denyRequest();
//        }
//
//        if (isset($entry['_id']) && !$this->module('collections')->hasaccess($collection['name'], 'entries_edit')) {
//            return $this->helper('admin')->denyRequest();
//        }

        /** @var UserInterface $user */
        $user = $request->getAttribute(UserInterface::class);

        $entry['_mby'] = $user->getDetail('id');
        $entry['_modified'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $isUpdate = false;
        if (isset($entry['_id'])) {
//            if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($entry['_id'])) {
//                $this->stop(['error' => "Saving failed! Entry is locked!"], 412);
//            }

            $_entry = $this->entries->byId($collection, $entry['_id']);
//            $_entry = $this->module('collections')->findOne($collection['name'], ['_id' => $entry['_id']]);
            if ($_entry) {
                $revision = !(json_encode($_entry->toArray()) == json_encode($entry));
            } else {
                $revision = true;
            }

        } else {
            $entry['_created'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            $entry['_by'] = $entry['_mby'];
            $revision = true;
            $isUpdate = true;
//              @todo
//            if ($collection->sortable()) {
//                $entry['_o'] = $this->app->storage->count("collections/{$collection['_id']}", ['_pid' => ['$exists' => false]]);
//            }
        }

        $this->eventSystem->trigger('collections.save.before', [$collection->name(), &$entry, $isUpdate]);
        $this->eventSystem->trigger("collections.save.before.{$collection->name()}", [$collection->name(), &$entry, $isUpdate]);

        $options = ['revision' => $revision];
        $entry = $this->entries->save($collection, $entry, $options);
        $entryArray = $entry->toArray();

        $this->eventSystem->trigger('collections.save.after', [$collection->name(), &$entryArray, $isUpdate]);
        $this->eventSystem->trigger(
            "collections.save.after.{$collection->name()}",
            [$collection->name(), &$entryArray, $isUpdate]
        );

        if ($options['revision']) {
            $this->revisions->add($entry, $user->getDetail('id'), "collections/{$collection->name()}");
        }

        return new JsonResponse($entryArray);
    }

    public function revisions($collectionName, $id)
    {
//        if (!$this->module('collections')->hasaccess($collection, 'entries_edit')) {
//            return $this->helper('admin')->denyRequest();
//        }

        $collection = $this->collections->byName($collectionName);

        if (!$collection) {
            return false;
        }

        $entry = $this->entries->byId($collection, $id);

        if (!$entry) {
            return false;
        }

        $revisions = $this->revisions->getList($id);

        return $this->render(
            'collections:views/revisions.php',
            [
                'collection' => $collection->toArray(),
                'entry' => $entry->toArray(),
                'revisions' => $revisions
            ]
        );
    }

    public function getUserCollections()
    {
        $collections = $this->collections->byGroup(null, true);

        return new JsonResponse(
            array_map(
                function (Collection $collection) {
                    return $collection->toArray();
                },
                $collections
            )
        );
    }
}
