<?php
/**
 * @category  Ebolution
 * @package   Ebolution/Core
 * @author    Avanzed Cloud Develop, S.L. desarrollo@ebolution.com
 * @copyright © 2023 Avanzed Cloud Develop, S.L. - All rights reserved.
 * @license   MIT
 */

namespace Ebolution\Core\Infrastructure\Contracts;

use Ebolution\Core\Domain\Contracts\AppConfigurationInterface;

class LaravelConfigHelper implements AppConfigurationInterface
{
    public function get(string $config_item, mixed $default = null): mixed
    {
        return config(env('APP_CONFIG') . '.' . $config_item, $default);
    }
}
