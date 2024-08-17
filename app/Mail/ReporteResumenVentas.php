<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Http\Controllers\Helpers\MainHelper;

class ReporteResumenVentas extends Mailable
{
    use Queueable, SerializesModels;
    private $config;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        $mail = $this->from('facsy@facturacion.xyz')
            ->subject('REPORTE DE VENTAS - '.$this->config['remitente'])
            ->view('mail.reporte_resumen_ventas');

        if(file_exists(public_path() . '/pdf/reporte_resumen_ventas.pdf')){
            $mail->attach(public_path() . '/pdf/reporte_resumen_ventas.pdf');
        }

        return $mail;
    }
}
