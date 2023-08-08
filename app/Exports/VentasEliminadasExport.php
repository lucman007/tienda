<?php

namespace sysfact\Exports;

use sysfact\Venta;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VentasEliminadasExport implements FromView, ShouldAutoSize
{

    private $datos;

    public function __construct($datos)
    {
       $this->datos=$datos;
    }

    public function view(): View
    {
        return view('reportes.excel.reporte_ventas_eliminadas', [
            'ventas' => $this->datos
        ]);
    }
}
