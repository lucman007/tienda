<?php

namespace sysfact\Exports;

use sysfact\Cliente;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ClientesExport implements FromView,ShouldAutoSize
{

	public function view(): View
	{
		return view('clientes.exportar', [
			'clientes' => Cliente::with('Persona')
			                     ->where('eliminado',0)
			                     ->get()
		]);
	}
}
