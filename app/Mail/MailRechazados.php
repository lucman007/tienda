<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Http\Controllers\Helpers\MainHelper;

class MailRechazados extends Mailable
{
    use Queueable, SerializesModels;

    public $emisor;
    public $num_comprobantes;

    public function __construct($num_comprobantes)
    {
        $config = MainHelper::configuracion('emisor');
        $this->emisor = json_decode($config, true)['razon_social'];
        $this->num_comprobantes = $num_comprobantes;
    }

    public function build()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        return $this
            ->from($fromAddress, $fromName)
            ->subject('COMPROBANTES RECHAZADOS')
            ->view('mail.estado_comprobantes', [
                'emisor' => $this->emisor,
                'num_comprobante' => $this->num_comprobantes,
                'domain' => app()->domain(),
                'esRechazados' => true
            ]);
    }
}
