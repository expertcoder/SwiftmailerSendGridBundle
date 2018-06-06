<?php

namespace ExpertCoder\Swiftmailer\SendGridBundle\Tests;

use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use ExpertCoder\Swiftmailer\SendGridBundle\ExpertCoderSwiftmailerSendGridBundle;
use ExpertCoder\Swiftmailer\SendGridBundle\Services\SendGridTransport;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return ExpertCoderSwiftmailerSendGridBundle::class;
    }

    protected function setUp()
    {
        parent::setUp();

        // Make services public that have an idea that matches a regex
        $this->addCompilerPass(new PublicServicePass('|swiftmailer.mailer.transport.expertcoder_swift_mailer.*|'));
    }

    public function testInitBundle()
    {
        // Create a new Kernel
        $kernel = $this->createKernel();

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config_test.yml');

        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        // Test if services exists
        $this->assertTrue($container->has('swiftmailer.mailer.transport.expertcoder_swift_mailer.send_grid'));
        $service = $container->get('swiftmailer.mailer.transport.expertcoder_swift_mailer.send_grid');
        $this->assertInstanceOf(SendGridTransport::class, $service);

        // Test if parameters exists
        $this->assertTrue($container->hasParameter('expertcoder_swiftmailer_sendgrid.api_key'));
        $this->assertTrue($container->hasParameter('expertcoder_swiftmailer_sendgrid.categories'));
    }
}
