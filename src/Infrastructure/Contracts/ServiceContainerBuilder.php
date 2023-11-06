<?php
/**
 * @category  Ebolution
 * @package   Ebolution/Core
 * @author    Avanzed Cloud Develop, S.L. desarrollo@ebolution.com
 * @copyright © 2023 Avanzed Cloud Develop, S.L. - All rights reserved.
 * @license   MIT
 */

namespace Ebolution\Core\Infrastructure\Contracts;

use Ebolution\Core\Domain\Contracts\BuilderInterface;

class ServiceContainerBuilder implements BuilderInterface
{

    public function build(string $class): mixed
    {
        return app()->make($class);
    }
}
