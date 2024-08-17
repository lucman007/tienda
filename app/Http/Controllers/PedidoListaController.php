<?php

namespace sysfact\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use sysfact\Orden;

class PedidoListaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $desde=null,$hasta=null)
    {
        $esExportable = $request->get('export',false);
        $filtro = $request->filtro;
        $buscar = $request->buscar;
        $orderby = $request->get('orderby', 'idorden');
        $order = $request->get('order', 'desc');

        $inicio_de_mes = Carbon::now()->firstOfMonth()->toDateString();
        $fin_de_mes = Carbon::now()->endOfMonth()->toDateString();

        if(!$filtro){
            $filtro='estado';
            $buscar='EN COLA';
            $desde=$inicio_de_mes;
            $hasta=$fin_de_mes;
        }

        $filtros = [
            'desde' => $desde,
            'hasta' => $hasta,
            'filtro'=>$filtro,
            'buscar'=>$buscar,
        ];

        switch($filtro) {
            case 'estado':
                $ordenes= Orden::whereBetween('fecha', [$desde.' 00:00:00', $hasta.' 23:59:59'])
                    ->where('eliminado', 0)
                    ->where('estado', $buscar)
                    ->orderby($orderby,$order)
                    ->when($esExportable, function ($query) {
                        return $query->get();
                    }, function ($query) {
                        return $query->paginate(30);
                    });
                break;
        }

        foreach ($ordenes as $orden) {
            $datos = json_decode($orden->datos_entrega, true);
            $orden->alias = $datos['contacto'];
            $orden->nota = $datos['direccion'];

            switch ($orden->despacho){
                case '1':
                    $orden->despacho = 'EN PROCESO';
                    $orden->badge_class = 'badge-warning';
                    break;
                case '2':
                    $orden->despacho = 'ENTREGADO';
                    $orden->badge_class = 'badge-success';
                    break;
            }

        }

        if($esExportable == 'true'){
            //return Excel::download(new Compras($requerimiento), 'compras_'.date('d_m_Y').'.xlsx');
        } else {
            $ordenes->appends($_GET)->links();
        }

        return view('pedidos.lista', [
            'pedidos' => $ordenes,
            'filtros' => $filtros,
            'usuario' => auth()->user()->persona,
            'order'=>$order=='desc'?'asc':'desc',
            'orderby'=>$orderby,
            'order_icon'=>$order=='desc'?'<i class="fas fa-caret-square-up"></i>':'<i class="fas fa-caret-square-down"></i>',
        ]);
    }

    public function edit(Request $request, $id)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $orden = Orden::find($id);

        $datos = json_decode($orden->datos_entrega, true);
        $orden->nota = $datos['direccion'];

        return json_encode($orden);
    }

    public function update(Request $request){
        try{

            $pedido = Orden::find($request->idorden);
            $datos_entrega = json_decode($pedido->datos_entrega, TRUE);

            $data['direccion'] = mb_strtoupper(trim($request->nota));
            $data['contacto'] = $datos_entrega['contacto'];
            $data['referencia'] = $datos_entrega['referencia'];
            $data['idcontacto'] = $datos_entrega['idcontacto'];
            $data['telefono'] = "";
            $data['costo'] = "";
            $pedido->datos_entrega = json_encode($data);
            $pedido->fecha_entrega = $request->fecha_entrega;
            $pedido->despacho = $request->despacho;
            $success=$pedido->save();
            return $success?1:0;

        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function destroy($id)
    {
        $orden = Orden::find($id);
        $orden->update([
            'eliminado'=>1
        ]);

    }

}
