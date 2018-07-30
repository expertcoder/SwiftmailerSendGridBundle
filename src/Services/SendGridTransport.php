<?php

namespace ExpertCoder\Swiftmailer\SendGridBundle\Services;

use finfo;
use SendGrid;
use Swift_Events_EventListener;
use Swift_Mime_Attachment;
use Swift_Mime_SimpleMessage;
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

    /**
     * Http client options.
     *
     * @var array
     */
    private $httpClientOptions;

    public function __construct($sendGridApiKey, $sendGridCategories)
    {
        $this->sendGridApiKey = $sendGridApiKey;
        $this->sendGridCategories = $sendGridCategories;
        $this->httpClientOptions = [];
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

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * WARNING : $failedRecipients and return value are faked.
     *
     * @param Swift_Mime_SimpleMessage $message
     * @param array                    $failedRecipients
     *
     * @return int
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        // prepare fake data.
        $sent = 0;
        $prepareFailedRecipients = [];

        //Get the first from email (SendGrid PHP library only seems to support one)
        $fromArray = $message->getFrom();
        $fromName = reset($fromArray);
        $fromEmail = key($fromArray);

        $mail = new SendGrid\Mail\Mail(); //Intentionally not using constructor arguments as they are tedious to work with

        // categories can be useful if you use them like tags to, for example, distinguish different applications.
        foreach ($this->sendGridCategories as $category) {
            $mail->addCategory($category);
        }

        $mail->setFrom(new SendGrid\Mail\From($fromEmail, $fromName));
        $mail->setSubject($message->getSubject());

        // extract content type from body to prevent multi-part content-type error
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $contentType = $finfo->buffer($message->getBody());
        $mail->addContent(new SendGrid\Mail\Content($contentType, $message->getBody()));

        // personalization
        if (!empty($mail->getPersonalizations())) {
            $personalization = $mail->getPersonalizations()[0];
        } else {
            $personalization = new SendGrid\Mail\Personalization();
            $mail->addPersonalization($personalization);
        }

        // process TO
        if ($toArr = $message->getTo()) {
            foreach ($toArr as $email => $name) {
                $personalization->addTo(new SendGrid\Mail\To($email, $name));
                ++$sent;
                $prepareFailedRecipients[] = $email;
            }
        }

        // process CC
        if ($ccArr = $message->getCc()) {
            foreach ($ccArr as $email => $name) {
                $personalization->addCc(new SendGrid\Mail\Cc($email, $name));
                ++$sent;
                $prepareFailedRecipients[] = $email;
            }
        }

        // process BCC
        if ($bccArr = $message->getBcc()) {
            foreach ($bccArr as $email => $name) {
                $personalization->addBcc(new SendGrid\Mail\Bcc($email, $name));
                ++$sent;
                $prepareFailedRecipients[] = $email;
            }
        }

        // process attachment
        if ($attachments = $message->getChildren()) {
            foreach ($attachments as $attachment) {
                if ($attachment instanceof Swift_Mime_Attachment) {
                    $sAttachment = new SendGrid\Mail\Attachment();
                    $sAttachment->setContent(base64_encode($attachment->getBody()));
                    $sAttachment->setType($attachment->getContentType());
                    $sAttachment->setFilename($attachment->getFilename());
                    $sAttachment->setDisposition($attachment->getDisposition());
                    $sAttachment->setContentId($attachment->getId());
                    $mail->addAttachment($sAttachment);
                } elseif (in_array($attachment->getContentType(), ['text/plain', 'text/html'])) {
                    // add part if any is defined, to avoid error please set body as text and part as html
                    $mail->addContent(new SendGrid\Mail\Content($attachment->getContentType(), $attachment->getBody()));
                }
            }
        }

        $sendGrid = new SendGrid($this->sendGridApiKey, $this->httpClientOptions);

        $response = $sendGrid->client->mail()->send()->post($mail);

        // only 2xx status are ok
        if ($response->statusCode() < self::STATUS_OK_SUCCESSFUL_MIN_RANGE ||
            self::STATUS_SUCCESSFUL_MAX_RANGE < $response->statusCode()) {
            // to force big boom error uncomment this line
            //throw new \Swift_TransportException("Error when sending message. Return status :".$response->statusCode());
            if (null !== $this->logger) {
                $this->logger->error($response->statusCode().': '.$response->body());
            }

            // copy failed recipients
            foreach ($prepareFailedRecipients as $recipient) {
                $failedRecipients[] = $recipient;
            }
            $sent = 0;
        }

        return $sent;
    }

    public function ping()
    {
        return true;
    }

    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        // unused
    }

    /**
     * @param array $httpClientOptions
     *
     * @return self
     */
    public function setHttpClientOptions(array $httpClientOptions)
    {
        $this->httpClientOptions = $httpClientOptions;

        return $this;
    }
}
