<?php

namespace sysfact\Http\Controllers\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Swift_SmtpTransport;
use Swift_Mailer;

trait UsesSmtpOverride
{
    protected function sendWithOverride(string $destinatario, $mailable, array $override, array $cc = [])
    {
        // Guardar el mailer original para restaurar después
        $originalMailer = Mail::getSwiftMailer();

        // Construir transporte con override
        $transport = new Swift_SmtpTransport(
            config('mail.host'),
            config('mail.port'),
            config('mail.encryption')
        );

        $transport->setUsername($override['username']);
        $transport->setPassword($override['password']);
        $customMailer = new Swift_Mailer($transport);

        // Reemplazar temporalmente
        Mail::setSwiftMailer($customMailer);

        // Enviar
        $to = Mail::to($destinatario);
        if (!empty($cc)) {
            $to = $to->cc($cc);
        }
        $to->send($mailable);

        // Restaurar
        Mail::setSwiftMailer($originalMailer);
    }

    protected function getOverrideConfig(string $key = 'alt_mail'): array
    {
        return config("mail.{$key}", []);
    }
}
