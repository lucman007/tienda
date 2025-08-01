<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EstadoStock extends Mailable
{
    use Queueable, SerializesModels;

    public $mensaje;

    /**
     * Create a new message instance.
     *
     * @param mixed $mensaje
     */
    public function __construct($mensaje)
    {
        $this->mensaje = $mensaje;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $asunto = 'STOCK DE PRODUCTOS ' . date('d/m/Y') . ' - ' . mb_strtoupper($fromName);

        return $this
            ->from($fromAddress, $fromName)
            ->subject($asunto)
            ->view('mail.stock', ['mensajes' => $this->mensaje]);
    }
}
