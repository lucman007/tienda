<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Http\Controllers\Helpers\MainHelper;

class EstadoStock extends Mailable
{
    use Queueable, SerializesModels;
    public $mensaje;
    public $config;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mensaje)
    {
        $this->mensaje = $mensaje;
        $mail = MainHelper::configuracion('mail_send_from');
        $this->config = json_decode($mail, true);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->from('facsy@coditecdigital.com')
            ->subject('STOCK DE PRODUCTOS '.date('d/m/Y').' - '.mb_strtoupper($this->config['remitente']))
            ->view('mail.stock',['mensajes'=>$this->mensaje]);

        return $mail;
    }
}
