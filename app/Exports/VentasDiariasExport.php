<?php

namespace sysfact\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VentasDiariasExport implements FromView, ShouldAutoSize
{

    private $datos;
    private $moneda;

    public function __construct($datos, $moneda)
    {
       $this->datos=$datos[0];
       $this->moneda=$moneda;
    }

    public function view(): View
    {
        return view('reportes.excel.reporte_ventas_diario', [
            'ventas' => $this->datos,
            'moneda' => $this->moneda
        ]);
    }
}
