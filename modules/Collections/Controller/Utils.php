<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Collections\Controller;


class Utils extends \Cockpit\AuthController {

    public function getUserCollections() {

        $collections = $this->module('collections')->getCollectionsInGroup(null, true);

        return $collections;
    }
}