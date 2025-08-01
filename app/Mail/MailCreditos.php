<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Http\Controllers\Helpers\MainHelper;

class MailCreditos extends Mailable
{
    use Queueable, SerializesModels;

    public $ventas;
    public $emisor;

    public function __construct($ventas)
    {
        $config = MainHelper::configuracion('emisor');
        $this->emisor = json_decode($config, true)['razon_social'];
        $this->ventas = $ventas;
    }

    public function build()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $subject = 'VENTAS A CRÉDITO POR VENCER - ' . $this->emisor;

        return $this
            ->from($fromAddress, $fromName)
            ->subject($subject)
            ->view('mail.creditos', ['ventas' => $this->ventas]);
    }
}
