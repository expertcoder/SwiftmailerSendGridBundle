<?php

namespace ExpertCoder\Swiftmailer\SendGridBundle\Tests;

use ExpertCoder\Swiftmailer\SendGridBundle\ExpertCoderSwiftmailerSendGridBundle;
use ExpertCoder\Swiftmailer\SendGridBundle\Services\SendGridTransport;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;

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
        // Create a new Kernel
        $kernel = $this->createKernel();
        $kernel->addBundle(SwiftmailerBundle::class);

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config_test.yml');

        // Boot the kernel.
        $this->bootKernel();
    }

    public function testInitBundle()
    {
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

    // This help us ensure the API is well used
    public function testSimpleMail()
    {
        $message = (new \Swift_Message('[Test] SwiftSendGrid'))
            ->setFrom('noreply@swiftsendgrid.bundle')
            ->setTo('nobody@send.grid')
            ->setBody('Test body.', 'text/plain')
        ;
        $transport = $this->getContainer()->get('swiftmailer.mailer.transport.expertcoder_swift_mailer.send_grid');
        $transport->setHttpClientOptions([
            'curl' => [CURLOPT_TIMEOUT => 1],
        ]);
        $mailer = new \Swift_Mailer($transport);
        $result = $mailer->send($message);

        $this->assertSame(0, $mailer->send($message)); // This should gives us 0 for no email sent
    }
}
