<?php declare(strict_types=1);

namespace Framework;

use DI\DependencyException;
use DI\NotFoundException;

final class Container extends \DI\Container
{
    /**
     * @throws DependencyException
     */
    public function parameter($key, $defaultValue = null)
    {
        try {
            return $this->get($key);
        } catch (DependencyException $e) {
            throw $e;
        } catch (NotFoundException $e) {
            return $defaultValue;
        }
    }

    public function hasExplicitly($service)
    {

    }
}
