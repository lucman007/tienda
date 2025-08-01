<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Emisor;
use sysfact\Http\Controllers\Helpers\MainHelper;

class EnviarPresupuesto extends Mailable
{
    use Queueable, SerializesModels;

    public $mensaje;
    public $cotizacion;
    public $conCopia;
    public $mail_contact = [];
    public $mail_send_from = [];
    public $cuerpo_mensaje = '';
    public $saludo_mensaje = '';
    public $fromAddress;
    public $fromName;

    /**
     * @param string $mensaje  contacto / mensaje
     * @param string $cotizacion  ruta del archivo de cotización (relativa a storage/app)
     * @param bool $conCopia
     */
    public function __construct($mensaje, $cotizacion, $conCopia, $from_address)
    {
        $this->mensaje = $mensaje;
        $this->cotizacion = $cotizacion;
        $this->conCopia = $conCopia;

        $conf = MainHelper::configuracion(['mail_send_from','mail_contact', 'cotizacion']);
        $this->mail_contact = json_decode($conf['mail_contact'], true);
        $this->mail_send_from = json_decode($conf['mail_send_from'], true);

        $cotizacion_config = json_decode($conf['cotizacion'], true);
        $this->cuerpo_mensaje = $cotizacion_config['texto'] ?? null;
        $this->saludo_mensaje = $cotizacion_config['texto_saludo'] ?? null;

        $this->fromAddress = $from_address;
        $this->fromName = $this->mail_send_from['remitente'];

    }

    public function build()
    {
        $data = [
            'contacto'        => $this->mensaje,
            'config'          => $this->mail_contact,
            'cuerpo_mensaje'  => $this->cuerpo_mensaje,
            'saludo_mensaje'  => $this->saludo_mensaje,
            'emisor'          => new Emisor()
        ];

        $subjectName = mb_strtoupper($this->fromName);

        $mail = $this
            ->from($this->fromAddress, $this->fromName)
            ->subject('COTIZACION ' . $subjectName)
            ->view('mail.presupuesto', $data)
            ->attach(storage_path("app/{$this->cotizacion}"));

        if ($this->conCopia) {
            $mail->bcc($this->fromAddress);
        }

        return $mail;
    }
}
