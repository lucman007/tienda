<?php

namespace sysfact\Exports;

use sysfact\Venta;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VentasPorMesExport implements FromView, ShouldAutoSize
{

    private $datos;

    public function __construct($datos)
    {
       $this->datos=$datos;
    }

    public function view(): View
    {
        return view('reportes.excel.reporte_por_mes', [
            'ventas' => $this->datos
        ]);
    }
}
