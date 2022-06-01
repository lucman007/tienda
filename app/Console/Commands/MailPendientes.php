<?php

namespace sysfact\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use sysfact\Venta;

class MailPendientes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailPendientes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar un correo informando los comprobantes pendientes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ventas=Venta::where('eliminado',0)
            ->orderby('idventa','desc')
            ->whereHas('facturacion', function($query){
                $query->where('estado', 'PENDIENTE');
            })
            ->get();

        $num_comprobantes = count($ventas);

        if($num_comprobantes > 0){
            try{
                Mail::to('ces.des007@gmail.com')->send(new \sysfact\Mail\MailPendientes($num_comprobantes));
                Log::info('Enviando mail pendientes...');
            } catch (\Swift_TransportException $e){
                return $e;
            }
        }

    }
}
