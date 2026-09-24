<?php

namespace PXP\Auth\Lib;

use Exception;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

readonly class Mail
{
    public function __construct(
        private string $subject,
        private string $body,
        private bool $html = false,
    ) {}

    private function mailer(): PHPMailer
    {
        $config = config('mail');

        $mailer = new PHPMailer(true);

        $mailer->isSMTP();
        $mailer->Host = $config->host;
        $mailer->SMTPAuth = true;
        $mailer->Username = $config->user;
        $mailer->Password = $config->pass;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = $config->port;
        $mailer->CharSet = PHPMailer::CHARSET_UTF8;

        $mailer->setFrom($config->user);

        return $mailer;
    }

    public function send(string $address, string $name): void
    {
        $mailer = $this->mailer();

        $mailer->addAddress($address, $name);

        $mailer->isHTML($this->html);
        $mailer->Subject = $this->subject;
        $mailer->Body = $this->body;

        try {
            $mailer->send();

            info("Sent mail '$this->subject' to '$address'");
        } catch (PHPMailerException) {
            info("E-Mail to '$address' could not be sent. Mailer Error: {$mailer->ErrorInfo}");

            throw new Exception("E-Mail to '$address' could not be sent");
        }
    }
}
