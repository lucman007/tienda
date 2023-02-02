<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Matrix\Exception;
use Spipu\Html2Pdf\Html2Pdf;
use sysfact\Catalogo;
use sysfact\Emisor;
use sysfact\Http\Controllers\Helpers\MainHelper;

class CatalogoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request){
            try{
                $consulta=trim($request->get('textoBuscado'));

                $catalogos=Catalogo::where('eliminado',0)
                    ->where('titulo','like','%'.$consulta.'%')
                    ->orderby('idcatalogo','desc')
                    ->paginate(30);

                $catalogos->appends($_GET)->links();

                return view('catalogo.index',[
                    'catalogos'=>$catalogos,
                    'usuario'=>auth()->user()->persona,
                    'textoBuscado'=>$consulta,
                ]);

            } catch (\Exception $e){
                return $e->getMessage();
            }

        }
    }

    public function nuevo(){
        $config = json_decode(cache('config')['cotizacion'], true);
        $configuracion = Collect($config);

        //titulo
        setlocale(LC_ALL, "es_ES", 'Spanish_Spain', 'Spanish');
        $string = date('d/m/Y');
        $date = \DateTime::createFromFormat("d/m/Y", $string);
        $titulo = strftime("%B",$date->getTimestamp());

        return view('catalogo.nuevo',
            [
                'usuario'=>auth()->user()->persona,
                'config'=>$configuracion,
                'titulo'=>strtoupper($titulo)
            ]);
    }

    public function store(Request $request)
    {

        DB::beginTransaction();
        try{
            $request->footer = nl2br($request->footer);
            $data = $request->post();
            $save = Catalogo::create($data);
            $idcatalogo = $save->idcatalogo;

            $detalle=[];
            $items=json_decode($request->items, TRUE);
            $i=1;
            foreach ($items as $item){
                $detalle['num_item']=$i;
                $detalle['idproducto']=$item['idproducto'];
                $detalle['idcatalogo']=$idcatalogo;
                DB::table('catalogo_detalle')->insert($detalle);

                $i++;
            }

            DB::commit();

            return $idcatalogo;

        } catch (\Exception $e){
            DB::rollback();
            Log::error($e);
            return $e->getMessage();
        }


    }

    public function editar(Request $request, $id)
    {
        $catalogo=Catalogo::find($id);
        $productos = $catalogo->productos;

        return view('catalogo.editar',[
            'catalogo'=>$catalogo,
            'productos'=>json_encode($productos),
            'usuario'=>auth()->user()->persona
        ]);
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->footer = nl2br($request->footer);
            $data = $request->post();
            $catalogo = Catalogo::findOrfail($request->idcatalogo);
            $catalogo->update($data);

            $detalle = [];
            $items = json_decode($request->items, TRUE);
            $i = 1;

            DB::table('catalogo_detalle')->where('idcatalogo', '=', $request->idcatalogo)->delete();

            foreach ($items as $item){
                $detalle['num_item']=$i;
                $detalle['idproducto']=$item['idproducto'];
                $detalle['idcatalogo']=$request->idcatalogo;
                DB::table('catalogo_detalle')->insert($detalle);
                $i++;
            }

            DB::commit();

        } catch (Exception $e){
            DB::rollback();
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function generarPdf($id){
        try{
            $catalogo=Catalogo::find($id);
            $catalogo->productos;
            $emisor=new Emisor();
            $config = MainHelper::configuracion('mail_contact');
            $file=null;
            $i = 0;
            if($catalogo->imagen_portada){
                MainHelper::procesar_imagen($catalogo->imagen_portada,$emisor->ruc.'-catalogo-portada.jpg');
            }
            foreach ($catalogo->productos as $item){
                if($item->imagen){
                    MainHelper::procesar_imagen($item->imagen,$emisor->ruc.'-catalogo-'.$i.'.jpg');
                }
                $i++;
            }

            $view = view('catalogo/imprimir/modelo_1',['catalogo'=>$catalogo,'emisor'=>$emisor,'config'=>json_decode($config, true)]);
            $html=$view->render();
            $pdf=new Html2Pdf('P','A4','es', true, 'UTF-8');
            $pdf->pdf->SetTitle('CATALOGO-'.str_pad($catalogo->idcatalogo,5,'0',STR_PAD_LEFT));
            if(!file_exists(base_path('vendor/tecnickcom/tcpdf/fonts/impact.php'))){
                $fontname = \TCPDF_FONTS::addTTFfont(public_path('fonts/impact.ttf'), 'regular', '', 32);
            } else {
                $fontname = "impact";
            }
            $pdf->pdf->SetFont($fontname);
            $pdf->writeHTML($html);

            $files = glob(public_path('images/temporal/*'));
            foreach($files as $file){
                if(is_file($file) && strpos($file,$emisor->ruc.'-catalogo')!==false) {
                    unlink($file);
                }
            }

            return [
                'file'=>$pdf,
                'name'=>'CATALOGO-'.str_pad($catalogo->idcatalogo,5,'0',STR_PAD_LEFT).'.pdf'
            ];
        } catch (\Exception $e){
            Log::error($e);
            return $e->getMessage();
        }
    }

    public function imprimir($id)
    {
        $pdf=$this->generarPdf($id);
        $pdf['file']->output($pdf['name']);
    }

    public function descargar_catalogo($id){
        $pdf=$this->generarPdf($id);
        $pdf['file']->output($pdf['name'],'D');
    }

    public function destroy($id)    {
        $catalogo=Catalogo::findOrFail($id);
        $catalogo->eliminado=1;
        $catalogo->update();
    }

    public function duplicar($id){

        $catalogo=Catalogo::find($id);
        $duplicado = $catalogo->replicate();
        $duplicado->fecha = date('Y-m-d H:i:s');
        $duplicado->save();
        $idcatalogo=$duplicado->idcatalogo;

        $i=0;
        foreach ($catalogo->productos as $item){
            $detalle['num_item']=$i;
            $detalle['idproducto']=$item['idproducto'];
            $detalle['idcatalogo']=$idcatalogo;
            DB::table('catalogo_detalle')->insert($detalle);
            $i++;
        }

        return $i;

    }
    

}
