<?php

namespace sysfact\Exports;

use sysfact\Venta;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VentasResumenExport implements FromView, ShouldAutoSize
{

    private $datos;
    private $totales;
    private $tipo_pago;
    private $fecha;

    public function __construct($datos, $tipo_pago, $totales, $fecha)
    {
       $this->datos=$datos;
       $this->totales=$totales;
       $this->tipo_pago=$tipo_pago;
       $this->fecha=$fecha;
    }

    public function view(): View
    {
        return view('reportes.excel.reporte_resumen_ventas', [
            'ventas' => $this->datos,
            'totales' => $this->totales,
            'tipo_pago' => $this->tipo_pago,
            'fecha' => $this->fecha
        ]);
    }
}
