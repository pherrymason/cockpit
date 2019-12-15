<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LimeExtra;

use Psr\Container\ContainerInterface;

/**
 * Class App
 * @package LimeExtra
 */
class App extends \Lime\App {

    public function __construct (ContainerInterface $container, array $settings = []) {
        parent::__construct($container, $settings);

        if ($this->retrieve('session.init', true)) {
            $this('session')->init();
        }
    }

    /**
     * Render view.
     * @param string $template Path to view
     * @param  []  $slots   Passed variables
     * @return string               Rendered view
     */
    public function view($template, $slots = []) {

        $this->trigger('app.render.view', [&$template, &$slots]);

        if (\is_string($template) && $template) {
            $this->trigger("app.render.view/{$template}", [&$template, &$slots]);
        }

        /** @var \Lexy $renderer */
        $renderer     = $this->container->get('renderer');
        $olayout      = $this->layout;

        $slots        = \array_merge($this->viewvars, $slots);
        $layout       = $olayout;

        $this->layout = false;

        if (\strpos($template, ' with ') !== false ) {
            list($template, $layout) = \explode(' with ', $template, 2);
        }

        if (\strpos($template, ':') !== false && $file = $this->path($template)) {
            $template = $file;
        }

        $slots['extend'] = function($from) use(&$layout) {
            $layout = $from;
        };

        if (!\file_exists($template)) {
            return "Couldn't resolve {$template}.";
        }

        $output = $renderer->file($template, $slots);

        if ($layout) {

            if (\strpos($layout, ':') !== false && $file = $this->path($layout)) {
                $layout = $file;
            }

            if(!\file_exists($layout)) {
                return "Couldn't resolve {$layout}.";
            }

            $slots['content_for_layout'] = $output;

            $output = $renderer->file($layout, $slots);
        }

        $this->layout = $olayout;

        return $output;
    }

    /**
     * Outputs view content result
     * @param $template
     * @param array $slots
     */
    public function renderView($template, $slots = []) {
        echo $this->view($template, $slots);
    }

    public function assets($src, $version=false){

        $list   = [];
        $js     = [];
        $debug  = $this->retrieve('debug');
        $jshash = '';

        foreach ((array)$src as $asset) {

            $src = $asset;

            if (\is_array($asset)) {
                extract($asset);
            }

            if (@\substr($src, -3) == '.js') {

                $ispath = \strpos($src, ':') !== false && !\preg_match('#^(|http\:|https\:)//#', $src);

                if (!$debug && $ispath && $path = $this->path($src)) {
                    $js[] = $path;
                    $jshash = md5($jshash.md5_file($path));
                } else {
                    $list[] = $this->script($asset, $version);
                }

            } elseif (@\substr($src, -4) == '.css') {
                $list[] = $this->style($asset, $version);
            }
        }

        if (count($js)) {
            
            $path = '#pstorage:tmp/'.$jshash.'.js';

            if (!$this->path($path)) {
                $contents = [];
                foreach ($js as $p) {$contents[] = file_get_contents($p); }
                $this->helper('fs')->write($path, implode("\n", $contents));
            }

            $url = $this->pathToUrl($path);
            $list[] = '<script src="'.($url.($version ? "?ver={$version}":'')).'" type="text/javascript"></script>';
        }

        return \implode("\n", $list);
    }

    public function invoke($class, $action="index", $params=[])
    {
        // Check if existing in container
        if ($this->container->has($class)) {
            $controller = $this->container->get($class);
        } else {
            $controller = new $class($this);
        }

        return \method_exists($controller, $action) && \is_callable([$controller, $action])
            ? \call_user_func_array([$controller,$action], $params)
            : false;
    }
}
