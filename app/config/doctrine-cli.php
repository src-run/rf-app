<?php

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @param array $more
 *
 * @return string
 */
function resolveAutoloadPath(array $more = []): string
{
    $tryAutoloadPaths = array_merge([
        __DIR__.'/../autoload.php',
        __DIR__.'/../../vendor/autoload.php',
    ], $more);

    foreach ($tryAutoloadPaths as $path) {
        if (file_exists($path) && is_readable($path)) {
            return $path;
        }
    }

    throw new \RuntimeException(sprintf('Could not resolve autoload paths (tried: %s)!', implode(',', array_map(function (string $path) {
        return sprintf('"%s"', $path);
    }, $tryAutoloadPaths))));
}

/**
 * @param mixed|HelperSet $helperSet
 *
 * @return mixed
 */
function resolveHelperSet($helperSet)
{
	if ($helperSet instanceof HelperSet) {
	    return $helperSet;
	}

	foreach ($GLOBALS as $hs) {
        if ($hs instanceof HelperSet) {
            return $hs;
        }
    }

    throw new \RuntimeException('Could not resolve helper set!');
}

/**
 * @param ContainerInterface $container
 * @param string             $service
 *
 * @return Connection
 */
function getDoctrineConnection(ContainerInterface $container, string $service = 'doctrine.dbal.default_connection'): Connection
{
    if ($container->has($service)) {
        return $container->get($service);
    }

    throw new \RuntimeException('Could not resolve default doctrine dbal connection!');
}

/**
 * @param ContainerInterface $container
 * @param string             $service
 *
 * @return EntityManagerInterface
 */
function getDoctrineEntityManager(ContainerInterface $container, string $service = 'doctrine.orm.default_entity_manager'): EntityManagerInterface
{
    if ($container->has($service)) {
        return $container->get($service);
    }

    throw new \RuntimeException('Could not resolve default doctrine entity manager!');
}

/**
 * @param ContainerInterface $container
 *
 * @return Configuration
 */
function getMigrationsConfig(ContainerInterface $container): Configuration
{
    $configuration = new Configuration(getDoctrineConnection($container));

    $configuration->setMigrationsDirectory($container->getParameter('doctrine_migrations.dir_name'));
    $configuration->setMigrationsNamespace($container->getParameter('doctrine_migrations.namespace'));
    $configuration->setMigrationsTableName($container->getParameter('doctrine_migrations.table_name'));
    $configuration->setName($container->getParameter('doctrine_migrations.name'));

    return $configuration;
}

/**
 * @return KernelInterface
 */
function getAppKernel(): KernelInterface
{
    $input = new ArgvInput();

    $envType = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'dev');
    $isDebug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(['--no-debug', '']) && $envType !== 'prod';

    if ($isDebug) {
        Debug::enable();
    }

    $kernel = new AppKernel($envType, $isDebug);
    $kernel->boot();

    return $kernel;
}

/**
 * @param ContainerInterface $container
 *
 * @return HelperSet
 */
function getCommandHelperSet(ContainerInterface $container): HelperSet
{
    $helperSet = new HelperSet();
    $helperSet->set(new EntityManagerHelper(getDoctrineEntityManager($container)), 'em');
    $helperSet->set(new ConnectionHelper(getDoctrineConnection($container)), 'db');
    $helperSet->set(new ConfigurationHelper(getDoctrineConnection($container), getMigrationsConfig($container)), 'configuration');
    $helperSet->set(new QuestionHelper(), 'question');

    return $helperSet;
}

require_once resolveAutoloadPath();

return getCommandHelperSet(getAppKernel()->getContainer());
