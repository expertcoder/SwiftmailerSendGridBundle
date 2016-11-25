<?php

namespace ExpertCoder\Swiftmailer\SendGridBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class ExpertCoderSwiftmailerSendGridExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

		$loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

		$container->setParameter('expertcoder_swiftmailer_sendgrid.api_key', $config['api_key']);

		/*
		 Swiftmailer Bundle seems to prepend "swiftmailer.mailer.transport." to "swiftmailer.transport" specified
		 in config.yml to determine the name of the service to use, hence the need for this alias
		 */
		$container->setAlias('swiftmailer.mailer.transport.sendgrid', 'expertcoder_swift_mailer.send_grid.transport');

	}
}
