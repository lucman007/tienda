<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Http\Controllers\Helpers\MainHelper;

class MovimientoCaja extends Mailable
{
    use Queueable, SerializesModels;

    public $caja;
    public $emisor;

    public function __construct($caja)
    {
        $config = MainHelper::configuracion('emisor');
        $this->emisor = json_decode($config, true)['razon_social'];
        $this->caja = $caja;
    }

    public function build()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $mail = $this
            ->from($fromAddress, $fromName)
            ->subject('MOVIMIENTO DE CAJA - ' . $this->emisor)
            ->view('mail.caja', ['caja' => $this->caja]);

        // Adjuntos: si los archivos se generan dinámicamente, considera moverlos a storage y usar storage_path()
        if (file_exists(public_path('pdf/resumen_ventas.pdf'))) {
            $mail->attach(public_path('pdf/resumen_ventas.pdf'));
        }

        if (file_exists(public_path('pdf/cierre_caja.pdf'))) {
            $mail->attach(public_path('pdf/cierre_caja.pdf'));
        }

        if (file_exists(public_path('pdf/reporte_productos.pdf'))) {
            $mail->attach(public_path('pdf/reporte_productos.pdf'));
        }

        return $mail;
    }
}
