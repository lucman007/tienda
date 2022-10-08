<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Http\Controllers\Helpers\MainHelper;

class EnviarDocumentos extends Mailable
{
    use Queueable, SerializesModels;
    public $factura;
    public $guia;
    public $recibo;
    public $config;
    public $conCopia;

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
        $mail = $this->from($this->config['email'])->view('mail.docs');

        if($this->guia){
            $mail->subject('Envío de guia electrónica - ' . $this->config['remitente'])
                ->attach(storage_path() . '/app/sunat/pdf/' . $this->guia . '.pdf')
                ->attach(storage_path() . '/app/sunat/xml/' . $this->guia . '.xml');
        }

        if(file_exists(storage_path().'/app/sunat/cdr/R-' .$this->guia.'.xml')){
            $mail->attach(storage_path() . '/app/sunat/cdr/R-' . $this->guia . '.xml');
        }

        if($this->factura){
            $mail->subject('Envío de comprobantes electrónicos - ' . $this->config['remitente'])
                ->attach(storage_path() . '/app/sunat/pdf/' . $this->factura . '.pdf')
                ->attach(storage_path() . '/app/sunat/xml/' . $this->factura . '.xml');
        }

        if(file_exists(storage_path().'/app/sunat/cdr/R-' .$this->factura.'.xml')){
            $mail->attach(storage_path() . '/app/sunat/cdr/R-' . $this->factura . '.xml');
        }

        if($this->recibo){
            $mail->subject('Envío de nota de venta - ' . $this->config['remitente'])
                ->attach(storage_path() . '/app/sunat/pdf/' . $this->recibo . '.pdf');
        }

        if($this->conCopia){
            $mail->bcc($this->config['email']);
        }

        return $mail;

    }
}
