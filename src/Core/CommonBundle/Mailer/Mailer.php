<?php

namespace Core\CommonBundle\Mailer;

use FOS\UserBundle\Mailer\TwigSwiftMailer as BaseMailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Mailer extends BaseMailer {

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig_Environment $twig, array $parameters) {
        parent::__construct($mailer, $router, $twig, $parameters);
    }

    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    public function sendMessage($templateName, $context, $fromEmail, $toEmail) {
        $message = \Swift_Message::newInstance();        

        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message
                ->setSubject($subject)
                ->setFrom($fromEmail)
                ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                    ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->mailer->send($message);
    }

}
