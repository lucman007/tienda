<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Http\Controllers\Helpers\MainHelper;

class MailPendientes extends Mailable
{
    use Queueable, SerializesModels;
    public $emisor;
    public $num_comprobantes;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($num_comprobantes)
    {
        $config = MainHelper::configuracion('emisor');
        $this->emisor = json_decode($config, true)['razon_social'];
        $this->num_comprobantes = $num_comprobantes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('facsy@facturacion.xyz')
            ->subject('COMPROBANTES PENDIENTES DE ENVÍO...')
            ->view('mail.pendientes',['emisor'=>$this->emisor,'num_comprobante'=>$this->num_comprobantes]);
    }
}
