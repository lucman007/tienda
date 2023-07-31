<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('superuser');
    }

    public function crearTenant(Request $request) {
        $artisan = Artisan::call("domain:add {$request->dominio}");
        $output = Artisan::output();

        $ruta = str_replace('.','_',$request->dominio);
        mkdir(storage_path($ruta.'/app/sunat/certificados'), 0777, true);
        mkdir(storage_path($ruta.'/app/sunat/cdr'), 0777, true);
        mkdir(storage_path($ruta.'/app/sunat/pdf'), 0777, true);
        mkdir(storage_path($ruta.'/app/sunat/xml'), 0777, true);
        mkdir(storage_path($ruta.'/app/sunat/zip'), 0777, true);
        return Redirect::back()->with('mensaje_crear',$output);
    }

    public function eliminarTenant(Request $request) {
        $artisan = Artisan::call("domain:remove {$request->dominio}");
        $output = Artisan::output();
        return Redirect::back()->with('mensaje_eliminar',$output);
    }

    public function mostrarTenants() {
        Artisan::call("domain:list");
        $output = Artisan::output();
        $patron = '/Domain: ([^\s]+)/';
        preg_match_all($patron, $output, $matches);
        $dominios = $matches[1];

        return Redirect::back()->with('tenants_list',$dominios);
    }

    public function guardarConfigTenant(Request $request) {
        $artisan = Artisan::call("config:cache --domain={$request->dominio}");
        $output = Artisan::output();
        return Redirect::back()->with('mensaje_config',$output);
    }

}
