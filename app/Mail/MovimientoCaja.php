<?php

namespace sysfact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use sysfact\Http\Controllers\Helpers\MainHelper;

class MovimientoCaja extends Mailable
{
    use Queueable, SerializesModels;
    public $caja;
    public $emisor;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($caja)
    {
        $config = MainHelper::configuracion('emisor');
        $this->emisor = json_decode($config, true)['razon_social'];
        $this->caja = $caja;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('facsy@facturacion.xyz')
            ->subject('MOVIMIENTO DE CAJA - '.$this->emisor)
            ->view('mail.caja',['caja'=>$this->caja]);
    }
}
