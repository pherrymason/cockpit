<?php declare(strict_types=1);

namespace Cockpit\Singleton\Controller;

use Cockpit\Singleton\Singleton;
use Cockpit\Singleton\SingletonRepository;
use Framework\IDs;
use Lime\App;

final class Admin extends \Cockpit\AuthController
{
    /** @var SingletonRepository */
    private $singletons;

    public function __construct(SingletonRepository $singletons, App $app)
    {
        $this->singletons = $singletons;
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

    public function singleton($name = null)
    {
        /*
        if ($name && !$this->module('singletons')->hasaccess($name, 'edit')) {
            return $this->helper('admin')->denyRequest();
        }

        if (!$name && !$this->module('cockpit')->hasaccess('singletons', 'create')) {
            return $this->helper('admin')->denyRequest();
        }*/

        $singleton = Singleton::create('', null, null, [], null);

        if ($name) {
            $singleton = $this->singletons->byName($name);

            if (!$singleton) {
                return false;
            }

            $meta = [];
            if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($singleton['_id'], $meta)) {
                return $this->render('cockpit:views/base/locked.php', compact('meta'));
            }

            $this->app->helper('admin')->lockResourceId($singleton['_id']);
        }

        // acl groups
        $aclgroups = [];

        foreach ($this->app->helper('acl')->getGroups() as $group => $superAdmin) {
            if (!$superAdmin) {
                $aclgroups[] = $group;
            }
        }

        return $this->render('singletons:views/singleton.php', compact('singleton', 'aclgroups'));
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
                $data['_id'],
                $data['name'],
                $data['label'] ?? null,
                $data['description'] ?? null,
                $data['fields'],
                $data['template'] ?? null
            );
        } else {
            $singleton = Singleton::create(
                IDs::new(),
                $data['name'],
                $data['label'] ?? null,
                $data['description'] ?? null,
                $data['fields'],
                $data['template'] ?? null);
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
}
