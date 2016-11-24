<?php

namespace ExpertCoder\Swiftmailer\SendGridBundle\Services;

use Swift_Events_EventListener;
use Swift_Mime_Message;
use SendGrid;

class SendGridTransport implements \Swift_Transport
{
	private $sendGridApiKey;

	public function __construct($sendGridApiKey)
	{
		$this->sendGridApiKey = $sendGridApiKey;
	}

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
		//Get the first from email (SendGrid PHP library only seems to support one)
		$fromArray = $message->getFrom();
		reset($fromArray);
		$fromStr = key($fromArray);
		$from = new SendGrid\Email(null, $fromStr);

		$subject = $message->getSubject();
		$content = new SendGrid\Content($message->getContentType(), $message->getBody() );

		$mail = new SendGrid\Mail(); //Intentionally not using constructor arguments as they are tedious to work with

		$mail->setFrom($from);
		$mail->setSubject($subject);
		$mail->addContent($content);

		$personalization = new SendGrid\Personalization();
		foreach ($message->getTo() as $email => $name ) {
			$personalization->addTo($email);
		}

		foreach ($message->getCC() as $email => $name ) {
			$personalization->addCC($email);
		}

		foreach ($message->getBcc() as $email => $name ) {
			$personalization->addBcc($email);
		}

		$sendGrid = new SendGrid($this->sendGridApiKey);

		$response = $sendGrid->client->mail()->send()->post($mail);
//		echo $response->statusCode();
//		echo $response->headers();
//		echo $response->body();

		//TODO - need to return correct value
	}

	public function registerPlugin(Swift_Events_EventListener $plugin)
	{
		throw new \Exception('This method has not been implemented yet');
	}


}