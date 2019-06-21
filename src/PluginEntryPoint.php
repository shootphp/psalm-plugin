<?php
declare(strict_types=1);

namespace Shoot\PsalmPlugin;

use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use ReflectionClass;
use ReflectionException;
use Shoot\PsalmPlugin\Hooks\PresentationModels;
use SimpleXMLElement;

final class PluginEntryPoint implements PluginEntryPointInterface
{
    /**
     * @param RegistrationInterface $api
     * @param SimpleXMLElement|null $config
     *
     * @throws ReflectionException
     */
    public function __invoke(RegistrationInterface $api, ?SimpleXMLElement $config = null): void
    {
        $this->registerHooks($api, PresentationModels::class);
    }

    /**
     * @param RegistrationInterface $api
     * @param string                $className
     *
     * @throws ReflectionException
     */
    private function registerHooks(RegistrationInterface $api, string $className): void
    {
        $class = new ReflectionClass($className);

        require_once $class->getFileName();

        $api->registerHooksFromClass($className);
    }
}
