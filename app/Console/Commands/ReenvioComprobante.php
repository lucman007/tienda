<?php

namespace sysfact\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use sysfact\Caja;
use sysfact\Emisor;
use sysfact\Http\Controllers\CajaController;
use sysfact\Http\Controllers\Cpe\CpeController;
use sysfact\Http\Controllers\CreditoController;
use sysfact\Venta;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReenvioComprobante extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reenviarComprobante';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando reenviará comprobantes con estado pendiente';

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
        $cierre_automatico = json_decode(cache('config')['interfaz'], true)['cierre_automatico']??true;
        if($cierre_automatico){
            $this->cerrar_caja();
        }
        $this->reenviar_comprobantes();
        $this->creditos();
        $this->disabledNotification();
    }

    public function creditos(){
        if(!Cache::get('notificationEnabled')){
            Cache::put('notificationEnabled','enabled',24*60);
        }
        $notification_enabled = Cache::get('notificationEnabled');
        if($notification_enabled == 'enabled'){
            $credito = new CreditoController();
            $credito->creditos_notificacion();
        } else {
            Log::info('not');
        }
    }

    public function disabledNotification(){
        //Desactivar las notificaciones por correo
        Log::info(Cache::get('notificationEnabled'));
        Cache::put('notificationEnabled','disabled',120);
        Log::info('Despues: '.Cache::get('notificationEnabled'));
    }

    public function cerrar_caja(){
        Log::info('Cerrar caja');
        try{
            if(date('H') >= 4 && date('H') <= 6){
                $caja=Caja::orderby('fecha_a','desc')
                    ->where('estado',0)
                    ->first();
                if($caja){
                    $cajaCon = new CajaController();
                    $cajaCon->cierre_automatico($caja->idcaja);
                }
            }
        } catch (\Exception $e){
            Log::error($e);
        }
    }

    public function reenviar_comprobantes(){
        try{
            $ventas=Venta::where('eliminado','=',0)
                ->orderby('idventa','desc')
                ->whereHas('facturacion', function($query){
                    $query->where('estado', 'PENDIENTE');
                })
                ->get();

            $pendientes = 0;

            foreach ($ventas as $venta){
                $cpe = new CpeController();
                $emisor=new Emisor();
                $nombre_fichero=$emisor->ruc.'-'.$venta->facturacion->codigo_tipo_documento.'-'.$venta->facturacion->serie.'-'.$venta->facturacion->correlativo;
                $respuesta = $cpe->reenviar($venta->idventa,$nombre_fichero,$venta->facturacion->num_doc_relacionado);

                if(is_string($respuesta[0])){
                    if(!(str_contains(strtolower($respuesta[0]),'aceptado') || str_contains(strtolower($respuesta[0]),'aceptada'))){
                        $pendientes++;
                    }
                }

            }

            if($pendientes>0){
                Mail::to('ces.des007@gmail.com')->send(new \sysfact\Mail\MailPendientes($pendientes));
                Log::info('Reenvío automático: Enviando mail pendientes...');
            }

        } catch(\Exception $e){
            $ventas=Venta::where('eliminado',0)
                ->orderby('idventa','desc')
                ->whereHas('facturacion', function($query){
                    $query->where('estado', 'PENDIENTE');
                })
                ->get();

            $pendientes = count($ventas);

            if($pendientes > 0){
                try{
                    Mail::to('ces.des007@gmail.com')->send(new \sysfact\Mail\MailPendientes($pendientes));
                    Log::info('Enviando mail pendientes...');
                } catch (\Swift_TransportException $e){
                    return $e;
                }
            }
            Log::error($e);
        }
    }
}
