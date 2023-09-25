<?php

namespace sysfact\Exports;

use sysfact\Venta;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CreditosExport implements FromView, ShouldAutoSize
{

    private $datos;
    private $totales;
    private $tipo_pago;
    private $fecha;

    public function __construct($datos)
    {
       $this->datos=$datos;
    }

    public function view(): View
    {
        return view('creditos.reporte', [
            'ventas' => $this->datos
        ]);
    }
}
