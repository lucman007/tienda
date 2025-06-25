<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReporteErroresVentas extends Mailable
{
    use Queueable, SerializesModels;

    public $errores;

    public function __construct($errores)
    {
        $this->errores = $errores;
    }

    public function build()
    {
        return $this->from('facsy@coditecdigital.com')
            ->subject('⚠️ Inconsistencias detectadas en ventas')
            ->view('mail.errores_ventas');
    }
}
