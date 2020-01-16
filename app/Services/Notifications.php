<?php
namespace App\Services;

class Notifications
{
    private $mailer;
    private $domen;

    public function __construct(Mail $mailer)
    {
        $this->mailer = $mailer;
        $this->domen = config('domen');
    }

    public function emailWasChanged($email, $selector, $token)
    {
        // 
        $message = $this->domen.'verify_email?selector=' . \urlencode($selector) . '&token=' . \urlencode($token);
        $this->mailer->send($email, $message); 
    }

    public function passwordReset($email, $selector, $token)
    {
        $message = $this->domen.'password-recovery/form?selector=' . \urlencode($selector) . '&token=' . \urlencode($token);
        $this->mailer->send($email, $message);
    }


}