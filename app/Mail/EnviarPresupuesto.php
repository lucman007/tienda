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

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mensaje,$cotizacion,$conCopia)
    {
        $this->mensaje =$mensaje;
        $this->cotizacion = $cotizacion;
        $this->conCopia = $conCopia;

        $conf = MainHelper::configuracion(['mail_send_from','mail_contact','cotizacion']);
        $this->mail_contact = json_decode($conf['mail_contact'], true);
        $this->mail_send_from = json_decode($conf['mail_send_from'], true);
        $cotizacion_config = json_decode($conf['cotizacion'], true);
        if(isset($cotizacion_config['texto'])){
            $this->cuerpo_mensaje = $cotizacion_config['texto'];
        } else {
            $this->cuerpo_mensaje = null;
        }
        if(isset($cotizacion_config['texto_saludo'])){
            $this->saludo_mensaje = $cotizacion_config['texto_saludo'];
        } else {
            $this->saludo_mensaje = null;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'contacto'=>$this->mensaje,
            'config'=>$this->mail_contact,
            'cuerpo_mensaje'=>$this->cuerpo_mensaje,
            'saludo_mensaje'=>$this->saludo_mensaje,
            'emisor'=>new Emisor()
        ];

        if($this->conCopia){
            return $this->from($this->mail_send_from['email'])
                ->bcc($this->mail_send_from['email'])
                ->subject('COTIZACION '.mb_strtoupper($this->mail_send_from['remitente']))
                ->view('mail.presupuesto',$data)
                ->attach(storage_path().'/app/'.$this->cotizacion);
        }
        return $this->from($this->mail_send_from['email'])
            ->subject('COTIZACION '.mb_strtoupper($this->mail_send_from['remitente']))
            ->view('mail.presupuesto',$data)
            ->attach(storage_path().'/app/'.$this->cotizacion);

    }
}
