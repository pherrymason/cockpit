<?php declare(strict_types=1);

namespace Cockpit\Collections\Controller;

use Cockpit\Collections\Collection;
use Cockpit\Collections\CollectionRepository;
use Cockpit\Collections\Role;
use Lime\App;

final class Admin extends \Cockpit\AuthController
{
    /** @var CollectionRepository */
    private $collections;

    public function __construct(CollectionRepository $collections, App $app)
    {
        $this->collections = $collections;
        parent::__construct($app);
    }


    public function index()
    {
/*
        $_collections = $this->module('collections')->getCollectionsInGroup(null, false);

        foreach ($_collections as $collection => $meta) {

            $meta['allowed'] = [
                'delete' => $this->module('cockpit')->hasaccess('collections', 'delete'),
                'create' => $this->module('cockpit')->hasaccess('collections', 'create'),
                'edit' => $this->module('collections')->hasaccess($collection, 'collection_edit'),
                'entries_create' => $this->module('collections')->hasaccess($collection, 'collection_create'),
                'entries_delete' => $this->module('collections')->hasaccess($collection, 'entries_delete'),
            ];

            $meta['itemsCount'] = null;

            $collections[] = [
                'name' => $collection,
                'label' => isset($meta['label']) && $meta['label'] ? $meta['label'] : $collection,
                'meta' => $meta
            ];
        }

        // sort collections
        usort($collections, function($a, $b) {
            return mb_strtolower($a['label']) <=> mb_strtolower($b['label']);
        });
*/
 //       $collections = $this->collections->byGroup();
        $collections  = $this->collections->all();
        $frontendData = array_map(function (Collection $collection) {
            return $collection->toFrontendArray();
        }, $collections);

        return $this->render('collections:views/index.php', ['collections' => $frontendData]);
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
            'create' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.create.php"),
            'read'   => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.read.php"),
            'update' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.update.php"),
            'delete' => !$name ? "<?php\n\n" : $this->app->helper('fs')->read("#storage:collections/rules/{$name}.delete.php"),
        ];

        return $this->render('collections:views/collection.php',
            [
                'collection' => $collection->toFrontendArray(),
                'templates' => $templates,
                'aclgroups' => $aclgroups,
                'rules' => $rules
            ]
        );
    }

    public function save_collection()
    {
        $collection = $this->param('collection');
        $rules      = $this->param('rules', null);

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
            $this->app->helper('admin')->lockResourceId($collection['_id']);
        }

        return $collection;
    }
}
