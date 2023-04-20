<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 19/04/2023
 * Time: 20:38
 */

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Http\Controllers\Helpers\MainHelper;


class MailCreditos extends Mailable
{
    use Queueable, SerializesModels;
    public $ventas;
    public $emisor;

    public function __construct($ventas)
    {
        $config = MainHelper::configuracion('emisor');
        $this->emisor = json_decode($config, true)['razon_social'];
        $this->ventas = $ventas;
    }

    public function build()
    {
        $mail = $this->from('facsy@facturacion.xyz')
            ->subject('VENTAS A CRÉDITO POR VENCER - '.$this->emisor)
            ->view('mail.creditos',['ventas'=>$this->ventas]);

        return $mail;

    }

}