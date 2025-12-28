<?php

/**
 * Inane: Services
 *
 * Service Manager.
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.4
 *
 * @author Philip Michael Raab<philip@cathedral.co.za>
 * @package inanepain\services
 * @category services
 *
 * @license UNLICENSE
 * @license https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types=1);

namespace Inane\Services;

use Inane\Config\ConfigAwareInterface;
use Inane\Config\ConfigAwareTrait;
use Inane\Stdlib\Array\OptionsInterface;
use Inane\Stdlib\Exception\Exception;
use Inane\Stdlib\Options;

class ServiceManager implements ConfigAwareInterface {
    use ConfigAwareTrait;

    //#region Properties
    private OptionsInterface $services;

    public function getConfig(): OptionsInterface {
        return $this->config;
    }

    //#endregion Properties

    public static function createServiceManager(OptionsInterface $services): static {
        $sm = new static();
        $sm->services = new Options();

        foreach($services as $name => $function) {
            $sm->register($name, $function);
        }

        return $sm;
    }

    public function register(string $name, callable $factory): void {
        $this->services->set($name, ['factory' => $factory, 'result' => null]);
    }

    public function get(string $name, bool $useCache = true) {
        if (!$this->services->has($name)) {
            throw new Exception("Service '{$name}' not found.");
        }

        if ($useCache && $rst = $this->services->{$name}->result) return $rst;

        $rst = call_user_func($this->services->{$name}->factory, $this); // Pass the service manager for dependency resolution
        $this->services->{$name}->set('result', $rst);
        return $rst;
    }
}