<?php

namespace ExpertCoder\Swiftmailer\SendGridBundle\Services;

use Swift_Events_EventListener;
use Swift_Mime_Message;

class SendGridTransport implements \Swift_Transport
{
	public function isStarted()
	{
		//Not used
		return true;
	}

	public function start()
	{
		//Not used
	}

	public function stop()
	{
		//Not used
	}

	public function send(Swift_Mime_Message $message, &$failedRecipients = null)
	{
		$from = new \SendGrid\Email(null, "me@example.com");
		$subject = $message->getSubject();
		$to = new \SendGrid\Email(null, "me@example.com");
		$content = new \SendGrid\Content($message->getContentType(), "Hello, Email!");

		$mail = new \SendGrid\Mail($from, $subject, $to, $content);

		$apiKey = '.......';
		$sg = new \SendGrid($apiKey);

		$response = $sg->client->mail()->send()->post($mail);
		echo $response->statusCode();
		echo $response->headers();
		echo $response->body();
	}

	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
		throw new \Exception('This method has not been implemented yet');
	}


}