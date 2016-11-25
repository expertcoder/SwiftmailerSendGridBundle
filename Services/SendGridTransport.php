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
		//TODO - return value should be the number of recipients who were accepted for delivery
		//TODO - populate $failedRecipients ?

		//Get the first from email (SendGrid PHP library only seems to support one)
		$fromArray = $message->getFrom();
		$fromName = reset($fromArray);
		$fromEmail = key($fromArray);

		$mail = new SendGrid\Mail(); //Intentionally not using constructor arguments as they are tedious to work with

		$mail->setFrom(new SendGrid\Email($fromName, $fromEmail));
		$mail->setSubject($message->getSubject() );
		$mail->addContent(new SendGrid\Content($message->getContentType(), $message->getBody() ));

		$personalization = new SendGrid\Personalization();


		if ($toArr = $message->getTo()) {
			foreach ($toArr as $email => $name ) {
				$personalization->addTo(new SendGrid\Email($name, $email) );
			}
		}

		if ($ccArr = $message->getCc()) {
			foreach ($ccArr as $email => $name ) {
				$personalization->addCc(new SendGrid\Email($name, $email) );
			}
		}

		if ($bccArr = $message->getBcc()) {
			foreach ($bccArr as $email => $name ) {
				$personalization->addBcc(new SendGrid\Email($name, $email) );
			}
		}


		$mail->addPersonalization($personalization);

		$sendGrid = new SendGrid($this->sendGridApiKey);

		$response = $sendGrid->client->mail()->send()->post($mail);
	}

	public function registerPlugin(Swift_Events_EventListener $plugin)
	{

	}


}