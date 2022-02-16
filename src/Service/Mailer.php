<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class Mailer{

    private $mailer;

    private $mailerFrom;

    public function __construct(MailerInterface $mailer, string $mailerFrom)
    {
        $this->mailer = $mailer;

        $this->mailerFrom = $mailerFrom;
    }

    public function send(string $to, string $subject, string $html)
    {
        $email = (new Email())
        ->from($this->mailerFrom)
        ->to($to)
        ->subject($subject)
        ->html($html);
        $this->mailer->send($email);
    }

}
