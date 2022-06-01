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
    }

    public function crearTenant(Request $request) {
        $artisan = Artisan::call("domain:add {$request->dominio}");
        $output = Artisan::output();
        return Redirect::back()->with('mensaje_crear',$output);
    }

    public function eliminarTenant(Request $request) {
        $artisan = Artisan::call("domain:remove {$request->dominio}");
        $output = Artisan::output();
        return Redirect::back()->with('mensaje_eliminar',$output);
    }

    public function mostrarTenants() {
        $artisan = Artisan::call("domain:list");
        $output = Artisan::output();
        $list = explode('-',$output);
        return Redirect::back()->with('tenants_list',$list);
    }

    public function guardarConfigTenant(Request $request) {
        $artisan = Artisan::call("config:cache --domain={$request->dominio}");
        $output = Artisan::output();
        return Redirect::back()->with('mensaje_config',$output);
    }

}
