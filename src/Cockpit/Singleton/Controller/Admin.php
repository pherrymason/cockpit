<?php declare(strict_types=1);

namespace Cockpit\Singleton\Controller;

use Cockpit\App\Revisions;
use Cockpit\Singleton\Singleton;
use Cockpit\Singleton\SingletonRepository;
use Cockpit\Framework\IDs;
use Lime\App;

final class Admin extends \Cockpit\AuthController
{
    /** @var SingletonRepository */
    private $singletons;
    /** @var Revisions */
    private $revisions;

    public function __construct(SingletonRepository $singletons, App $app, \Cockpit\App\Revisions $revisions)
    {
        $this->singletons = $singletons;
        $this->revisions = $revisions;
        parent::__construct($app);
    }

    public function index()
    {
        $userGroup = 'admin';
        $singletons = $this->singletons->byGroup($userGroup);

        $arraySingletons = [];
        foreach ($singletons as $singleton) {

            $raw = $singleton->toArray();
            $raw['meta'] = [
                'allowed' => [
                    'delete' => true, //$this->module('cockpit')->hasaccess('singletons', 'delete'),
                    'create' => true, //$this->module('cockpit')->hasaccess('singletons', 'create'),
                    'singleton_edit' => true, //$this->module('singletons')->hasaccess($name, 'edit'),
                    'singleton_form' => true, //$this->module('singletons')->hasaccess($name, 'form')
                ]
            ];
            $arraySingletons[] = $raw;
        }

        // sort singletons
        /*usort($singletons, function ($a, $b) {
            return mb_strtolower($a['label']) <=> mb_strtolower($b['label']);
        });
*/
        return $this->render('singletons:views/index.php', ['singletons' => $arraySingletons]);
    }

    public function form($name = null)
    {
        if (!$name) {
            return false;
        }

        $singleton = $this->singletons->byName($name);

        if (!$singleton) {
            return false;
        }
        /*
        if (!$this->module('singletons')->hasaccess($singleton['name'], 'form')) {
            return $this->helper('admin')->denyRequest();
        }*/

        $this->app->helper('admin')->favicon = [
            'path' => 'singletons:icon.svg',
            'color' => '#FF000',//$singleton['color']
        ];
        /*
                $lockId = "singleton_{$singleton['name']}";

                if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($lockId, $meta)) {
                    return $this->render('singletons:views/locked.php', compact('meta', 'singleton'));
                }
                $data = $this->module('singletons')->getData($name);

                $this->app->helper('admin')->lockResourceId($lockId);
        */

        $singletonArray = $singleton->data();
        return $this->render(
            'singletons:views/form.php', [
            'singleton' => $singleton->toArray(),
            'data' => empty($singletonArray) ? null : $singletonArray
        ]);
    }

    public function singleton($name = null)
    {
        /*
        if ($name && !$this->module('singletons')->hasaccess($name, 'edit')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$name && !$this->module('cockpit')->hasaccess('singletons', 'create')) {
            return $this->helper('admin')->denyRequest();
        }*/


        if ($name) {
            $singleton = $this->singletons->byName($name);

            if (!$singleton) {
                return false;
            }

            $meta = [];
            /*
            if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($singleton->id(), $meta)) {
                return $this->render('cockpit:views/base/locked.php', compact('meta'));
            }

            $this->app->helper('admin')->lockResourceId($singleton->id());
            */
        } else {
            $singleton = Singleton::create('', '', null, '', [], null, []);
        }

        // acl groups
        $aclgroups = [];

        foreach ($this->app->helper('acl')->getGroups() as $group => $superAdmin) {
            if (!$superAdmin) {
                $aclgroups[] = $group;
            }
        }

        return $this->render('singletons:views/singleton.php', [
            'singleton' => $singleton->toArray(),
            'aclgroups' => $aclgroups
        ]);
    }

    public function save($name)
    {
        list($_name, $data) = $this->param('args', []);
        if (!trim($name)) {
            return false;
        }

        if (isset($data['_id'])) {
            // Update
            $singleton = Singleton::create(
                $data['_id'], $data['name'], $data['label'] ?? null, $data['description'] ?? null, $data['fields'], $data['template'] ?? null,
                []);
        } else {
            $singleton = Singleton::create(
                IDs::new(),
                $data['name'],
                $data['label'] ?? null,
                $data['description'] ?? null,
                $data['fields'] ?? [],
                $data['template'] ?? null,
                []);
        }

        // @todo Singletons can't be modified
        $this->app->trigger('singleton.save.before', [$singleton->toArray()]);
        $this->app->trigger("singleton.save.before.{$name}", [$singleton->toArray()]);

        // Save singleton definition
        $this->singletons->save($singleton);

        // @todo Singletons can't be modified
        $this->app->trigger('singleton.save.after', [$singleton]);
        $this->app->trigger("singleton.save.after.{$name}", [$singleton]);

        //return isset($data['_id']) ? $this->updateSingleton($name, $data) : $this->createSingleton($name, $data);

        return json_encode(['result' => $singleton->toArray()]);
    }

    public function update_data($singleton)
    {
        $singleton = $this->singletons->byName($singleton);
        $data = $this->param('data');

        if (!$singleton || !$data) {
            return false;
        }

        /*
        if (!$this->module('singletons')->hasaccess($singleton->name(), 'form')) {
            return $this->helper('admin')->denyRequest();
        }*/
        /*
                $lockId = "singleton_{$singleton['name']}";

                if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($lockId)) {
                    $this->stop(['error' => "Saving failed! Singleton is locked!"], 412);
                }
        */
        $data['_mby'] = $this->module('cockpit')->getUser('_id');

        if (isset($data['_by'])) {
            $data['_by'] = $data['_mby'];
            $singleton = $this->singletons->byName($singleton->name());
            $revision = !(json_encode($singleton->data()) == json_encode($data));
        } else {
            $data['_by'] = $data['_mby'];
            $revision = true;
        }

        // @todo Events can't modify singleton
        $this->app->trigger('singleton.saveData.before', [$singleton->toArray(), &$data]);
        $this->app->trigger("singleton.saveData.before.{$singleton->name()}", [$singleton->toArray(), &$data]);

        unset($data['_d']);
        $data = $this->singletons->saveData($singleton->name(), $data);

        // @todo Events can't modify singleton
        $this->app->trigger('singleton.saveData.after', [$singleton->toArray(), $data]);
        $this->app->trigger("singleton.saveData.after.{$singleton->name()}", [$singleton->toArray(), $data]);

        if ($revision) {
            $this->revisions->add($singleton, $data['_by'], 'singletons/' . $singleton->name());
        }

        //      $this->app->helper('admin')->lockResourceId($lockId);

        return ['data' => $data];
    }
}
