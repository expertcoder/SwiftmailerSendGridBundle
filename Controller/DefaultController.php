<?php

namespace ExpertCoder\Swiftmailer\SendGridBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ExpertCoderSwiftmailerSendGridBundle:Default:index.html.twig');
    }
}
