<?php

namespace ExpertCoder\Swiftmailer\SendGridBundle\Services;

use finfo;
use SendGrid;
use Swift_Events_EventListener;
use Swift_Mime_Attachment;
use Swift_Mime_Message;
use Swift_Transport;
use Psr\Log\LoggerInterface;

class SendGridTransport implements Swift_Transport
{
    /**
     * @see https://sendgrid.com/docs/API_Reference/Web_API_v3/Mail/errors.html
     * 2xx responses indicate a successful request. The request that you made is valid and successful.
     */
    const STATUS_SUCCESSFUL_MAX_RANGE = 299;

    /**
     * @see https://sendgrid.com/docs/API_Reference/Web_API_v3/Mail/errors.html
     * ACCEPTED : Your message is both valid, and queued to be delivered.
     */
    const STATUS_ACCEPTED = 202;

    /**
     * @see https://sendgrid.com/docs/API_Reference/Web_API_v3/Mail/errors.html
     * OK : Your message is valid, but it is not queued to be delivered. Sandbox mode only.
     */
    const STATUS_OK_SUCCESSFUL_MIN_RANGE = 200;

    /**
     * Sendgrid api key.
     *
     * @var string
     */
    private $sendGridApiKey;

    /**
     * Sendgrid mails categories.
     *
     * @var array
     */
    private $sendGridCategories;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($sendGridApiKey, $sendGridCategories, LoggerInterface $logger)
    {
        $this->sendGridApiKey = $sendGridApiKey;
        $this->sendGridCategories = $sendGridCategories;
        $this->logger = $logger;
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

    /**
     * WARNING : $failedRecipients and return value are faked.
     *
     * @param Swift_Mime_Message $message
     * @param array              $failedRecipients
     *
     * @return int
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        // prepare fake data.
        $sent = 0;
        $prepareFailedRecipients = [];

        //Get the first from email (SendGrid PHP library only seems to support one)
        $fromArray = $message->getFrom();
        $fromName = reset($fromArray);
        $fromEmail = key($fromArray);

        $mail = new SendGrid\Mail(); //Intentionally not using constructor arguments as they are tedious to work with

        // categories can be useful if you use them like tags to, for example, distinguish different applications.
        foreach ($this->sendGridCategories as $category) {
            $mail->addCategory($category);
        }

        $mail->setFrom(new SendGrid\Email($fromName, $fromEmail));
        $mail->setSubject($message->getSubject());

        // extract content type from body to prevent multi-part content-type error
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $contentType = $finfo->buffer($message->getBody());
        $mail->addContent(new SendGrid\Content($contentType, $message->getBody()));

        $personalization = new SendGrid\Personalization();

        // process TO
        if ($toArr = $message->getTo()) {
            foreach ($toArr as $email => $name) {
                $personalization->addTo(new SendGrid\Email($name, $email));
                ++$sent;
                $prepareFailedRecipients[] = $email;
            }
        }

        // process CC
        if ($ccArr = $message->getCc()) {
            foreach ($ccArr as $email => $name) {
                $personalization->addCc(new SendGrid\Email($name, $email));
                ++$sent;
                $prepareFailedRecipients[] = $email;
            }
        }

        // process BCC
        if ($bccArr = $message->getBcc()) {
            foreach ($bccArr as $email => $name) {
                $personalization->addBcc(new SendGrid\Email($name, $email));
                ++$sent;
                $prepareFailedRecipients[] = $email;
            }
        }

        // process attachment
        if ($attachments = $message->getChildren()) {
            foreach ($attachments as $attachment) {
                if ($attachment instanceof Swift_Mime_Attachment) {
                    $sAttachment = new SendGrid\Attachment();
                    $sAttachment->setContent(base64_encode($attachment->getBody()));
                    $sAttachment->setType($attachment->getContentType());
                    $sAttachment->setFilename($attachment->getFilename());
                    $sAttachment->setDisposition($attachment->getDisposition());
                    $sAttachment->setContentId($attachment->getId());
                    $mail->addAttachment($sAttachment);
                } elseif (in_array($attachment->getContentType(), ['text/plain', 'text/html'])) {
                    // add part if any is defined, to avoid error please set body as text and part as html
                    $mail->addContent(new SendGrid\Content($attachment->getContentType(), $attachment->getBody()));
                }
            }
        }

        $mail->addPersonalization($personalization);

        $sendGrid = new SendGrid($this->sendGridApiKey);

        $response = $sendGrid->client->mail()->send()->post($mail);

        // only 2xx status are ok
        if ($response->statusCode() < self::STATUS_OK_SUCCESSFUL_MIN_RANGE ||
            self::STATUS_SUCCESSFUL_MAX_RANGE < $response->statusCode()) {
            // to force big boom error uncomment this line
            //throw new \Swift_TransportException("Error when sending message. Return status :".$response->statusCode());
            $this->logger->error($response->statusCode().': '.$response->body());

            // copy failed recipients
            foreach ($prepareFailedRecipients as $recipient) {
                $failedRecipients[] = $recipient;
            }
            $sent = 0;
        }

        return $sent;
    }

    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        // unused
    }
}
