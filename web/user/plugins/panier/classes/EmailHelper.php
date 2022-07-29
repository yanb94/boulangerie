<?php

namespace Grav\Plugin\Panier;

use Grav\Common\Twig\Twig;
use Grav\Plugin\Email\Email;
use Swift_Attachment;

class EmailHelper
{
    private Email $emailSender;
    private \Twig_Environment $twigRender;

    public function __construct(Email $email, \Twig_Environment $twig)
    {
        $this->emailSender = $email;
        $this->twigRender = $twig;
    }

    public function sendEmail(
        string $template,
        array $params,
        string $subject,
        string $from,
        string $to,
        array $attachs
    ): void
    {
        $content = $this->twigRender->render($template,$params);

        $message = $this->emailSender->message($subject,$content,'text/html')
            ->setFrom($from)
            ->setTo($to)
        ;

        foreach($attachs as $attach)
        {
            $message->attach(Swift_Attachment::fromPath($attach));
        }

        $this->emailSender->send($message);
    }
}