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

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->factura = $request->factura;
        $this->guia = $request->guia;
        $this->recibo = $request->recibo;
        $this->conCopia = $request->conCopia;

        $conf = MainHelper::configuracion(['mail_send_from','mail_contact']);
        $this->mail_contact = json_decode($conf['mail_contact'], true);
        $this->mail_send_from = json_decode($conf['mail_send_from'], true);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $data = [
            'config'=>$this->mail_contact,
            'emisor'=>new Emisor()
        ];

        $mail = $this->from($this->mail_send_from['email'])->view('mail.docs', $data);

        if($this->guia){
            $mail->subject('Envío de guia electrónica - ' . mb_strtoupper($this->mail_send_from['remitente']))
                ->attach(storage_path() . '/app/sunat/pdf/' . $this->guia . '.pdf')
                ->attach(storage_path() . '/app/sunat/xml/' . $this->guia . '.xml');
        }

        if(file_exists(storage_path().'/app/sunat/cdr/R-' .$this->guia.'.xml')){
            $mail->attach(storage_path() . '/app/sunat/cdr/R-' . $this->guia . '.xml');
        }

        if($this->factura){
            $mail->subject('Envío de comprobantes electrónicos - ' . mb_strtoupper($this->mail_send_from['remitente']))
                ->attach(storage_path() . '/app/sunat/pdf/' . $this->factura . '.pdf')
                ->attach(storage_path() . '/app/sunat/xml/' . $this->factura . '.xml');
        }

        if(file_exists(storage_path().'/app/sunat/cdr/R-' .$this->factura.'.xml')){
            $mail->attach(storage_path() . '/app/sunat/cdr/R-' . $this->factura . '.xml');
        }

        if($this->recibo){
            $mail->subject('Envío de nota de venta - ' . mb_strtoupper($this->mail_send_from['remitente']))
                ->attach(storage_path() . '/app/sunat/pdf/' . $this->recibo . '.pdf');
        }

        if($this->conCopia){
            $mail->bcc($this->mail_send_from['email']);
        }

        return $mail;

    }
}
