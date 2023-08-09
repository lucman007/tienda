<?php

namespace sysfact\Exports;

use sysfact\Venta;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductosResumenDiarioExport implements FromView, ShouldAutoSize
{

    private $datos;
    private $desde;
    private $hasta;

    public function __construct($datos, $desde, $hasta)
    {
       $this->datos=$datos;
       $this->desde=$desde;
       $this->hasta=$hasta;
    }

    public function view(): View
    {
        return view('reportes.excel.productos_resumen_diario', [
            'productos' => $this->datos,
            'desde' => $this->desde,
            'hasta' => $this->hasta
        ]);
    }
}
