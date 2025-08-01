<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Emisor;
use sysfact\Http\Controllers\Helpers\MainHelper;

class EnviarDocumentos extends Mailable
{
    use Queueable, SerializesModels;

    public $factura;
    public $guia;
    public $recibo;
    public $mail_send_from = [];
    public $conCopia;
    public $mail_contact = [];
    public $fromAddress;
    public $fromName;

    public function __construct($request, $from_address)
    {
        $this->factura = $request->factura ?? null;
        $this->guia = $request->guia ?? null;
        $this->recibo = $request->recibo ?? null;
        $this->conCopia = $request->conCopia ?? false;

        $conf = MainHelper::configuracion(['mail_send_from','mail_contact']);
        $this->mail_contact = json_decode($conf['mail_contact'], true);
        $this->mail_send_from = json_decode($conf['mail_send_from'], true);

        $this->fromAddress = $from_address;
        $this->fromName = $this->mail_send_from['remitente'];
    }

    public function build()
    {
        $data = [
            'config' => $this->mail_contact,
            'emisor' => new Emisor()
        ];

        // Desde .env toma el from automáticamente con config('mail.from')
        $mail = $this
            ->from($this->fromAddress, $this->fromName)
            ->view('mail.docs')
            ->with($data);

        // Guía electrónica
        if ($this->guia) {
            $mail->subject('Envío de guía electrónica - ' . mb_strtoupper($this->mail_send_from['remitente']))
                ->attach(storage_path("app/sunat/pdf/{$this->guia}.pdf"))
                ->attach(storage_path("app/sunat/xml/{$this->guia}.xml"));

            if (file_exists(storage_path("app/sunat/cdr/R-{$this->guia}.xml"))) {
                $mail->attach(storage_path("app/sunat/cdr/R-{$this->guia}.xml"));
            }
        }

        // Factura / comprobantes electrónicos
        if ($this->factura) {
            $mail->subject('Envío de comprobantes electrónicos - ' . mb_strtoupper($this->mail_send_from['remitente']))
                ->attach(storage_path("app/sunat/pdf/{$this->factura}.pdf"))
                ->attach(storage_path("app/sunat/xml/{$this->factura}.xml"));

            if (file_exists(storage_path("app/sunat/cdr/R-{$this->factura}.xml"))) {
                $mail->attach(storage_path("app/sunat/cdr/R-{$this->factura}.xml"));
            }
        }

        // Recibo / nota de venta
        if ($this->recibo) {
            $mail->subject('Envío de nota de venta - ' . mb_strtoupper($this->mail_send_from['remitente']))
                ->attach(storage_path("app/sunat/pdf/{$this->recibo}.pdf"));
        }

        // Con copia al remitente
        if ($this->conCopia) {
            $mail->bcc(config('mail.from.address'));
        }

        return $mail;
    }
}
