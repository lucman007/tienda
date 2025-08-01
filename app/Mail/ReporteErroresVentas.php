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
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        return $this
            ->from($fromAddress, $fromName)
            ->subject('⚠️ Inconsistencias detectadas en ventas')
            ->view('mail.errores_ventas', [
                'errores' => $this->errores
            ]);
    }
}
