<?php

namespace sysfact\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PagosExport implements FromView, ShouldAutoSize
{
    private $datos;

    public function __construct($datos)
    {
        $this->datos=$datos;
    }

    public function view(): View
    {
        return view('trabajadores.exportar', [
            'gastos' => $this->datos['gastos']
        ]);
    }
}
