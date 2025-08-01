<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReporteResumenVentas extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $subject = 'REPORTE DE VENTAS - ' . mb_strtoupper($fromName);

        $mail = $this
            ->from($fromAddress, $fromName)
            ->subject($subject)
            ->view('mail.reporte_resumen_ventas');

        if (file_exists(public_path('pdf/reporte_resumen_ventas.pdf'))) {
            $mail->attach(public_path('pdf/reporte_resumen_ventas.pdf'));
        }

        return $mail;
    }
}
