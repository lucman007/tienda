<?php

namespace sysfact\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use sysfact\Producto;

class ProductosExport implements FromView,ShouldAutoSize
{

	public function view(): View
	{

        $productos=Producto::with('inventario:idproducto,cantidad')
            ->where('eliminado','=',0)
            ->orderby('idproducto','desc')
            ->get();



        foreach ($productos as $inv){
            foreach ($inv->inventario as $kardex){
                $inv->cantidad+=$kardex->cantidad;
            }
        }


		return view('productos.exportar', [
            'productos'=>$productos
		]);
	}
}
