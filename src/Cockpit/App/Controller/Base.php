<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cockpit\App\Controller;

use Cockpit\Framework\TemplateController;
use League\Plates\Engine;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Base extends TemplateController
{
    /**
     * @var \Cockpit\Framework\EventSystem
     */
    private $eventSystem;

    public function __construct(\Cockpit\Framework\EventSystem $eventSystem, Engine $templateEngine, \Psr\Container\ContainerInterface $container)
    {
        parent::__construct($templateEngine, $container);
        $this->eventSystem = $eventSystem;
    }

    public function dashboard(RequestInterface $request, ResponseInterface $response)
    {
        $config = $this->container->get('database.config');
        $keyStorage = $this->container->get($config['driver']);

        /** @var UserInterface $user */
        $user = $request->getAttributes()[UserInterface::class];
        $settings = $keyStorage->getKey('cockpit/options', 'dashboard.widgets.'.$user->getDetail('id'), []);

        $widgets  = new \ArrayObject([]);

        $this->eventSystem->trigger('admin.dashboard.widgets', [$widgets]);

        $areas = [
            'main' => new \SplPriorityQueue(),
            'aside-left' => new \SplPriorityQueue(),
            'aside-right' => new \SplPriorityQueue()
        ];

        foreach($widgets as &$widget) {

            $name = $widget['name'];
            $area = isset($widget['area']) && in_array($widget['area'], ['main', 'aside-left', 'aside-right']) ? $widget['area'] : 'main';

            $area = \Lime\fetch_from_array($settings, "{$name}/area", $area);
            $prio = \Lime\fetch_from_array($settings, "{$name}/prio", 0);

            $areas[$area]->insert($widget, -1 * $prio);
        }

        return $this->renderResponse($request, $response, 'base/dashboard', compact('areas', 'widgets'));
    }

    public function savedashboard() {

        $widgets = $this->app->param('widgets', []);

        $this->app->storage->setKey('cockpit/options', 'dashboard.widgets.'.$this->user["_id"], $widgets);

        return $widgets;
    }

    public function search() {

        \session_write_close();

        $query = $this->app->param('search', false);
        $list  = new \ArrayObject([]);

        if ($query) {
            $this->eventSystem->trigger('cockpit.search', [$query, $list]);
        }

        return json_encode($list->getArrayCopy());
    }

    /**
     * @todo Deprecate this call, it has no sense as modules provide their own controllers
     */
    public function call($module, $method) {

        $args = (array)$this->param('args', []);
        $acl  = $this->param('acl', null);

        if (!$acl) {
            return false;
        }

        if (!$this->module('cockpit')->hasaccess($module, $acl)) {
            return false;
        }

        $return = call_user_func_array([$this->app->module($module), $method], $args);

        return '{"result":'.json_encode($return).'}';
    }
}
