<?php

namespace sysfact\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use sysfact\Http\Controllers\Helpers\MainHelper;
use sysfact\User;

class NotificacionesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $notifications = User::whereHas('roles', function ($query) {
            $query->where('id', 5);
        })->first()->notifications()->paginate(30);
        return view('notificaciones.index', ['notifications'=>$notifications,'usuario'=>auth()->user()->persona]);
    }

    public function countNotificaciones(){
        $notificaciones = DB::table('notifications')->whereNull('read_at')->get();
        return $notificaciones->count();
    }

    public function countComprobantes(){

        return MainHelper::disabledVentas();

    }

    public function obtenerNotificaciones()
    {
        $notifications = User::whereHas('roles', function ($query) {
            $query->where('id', 5);
        })->first()->notifications->take(10);

        foreach ($notifications as $notification){
            $notification->fecha = date('d/m/Y H:i:s', strtotime($notification->created_at));
            $data = json_decode(json_encode($notification->data), true);

            if($data){
                switch ($data['tipo_notificacion']){
                    case 1:
                        $notification->titulo = "El comprobante <strong>".$data['comprobante']."</strong> ha sido <span class='badge badge-danger'>".$data['estado']."</span> <br>";
                        $notification->extracto = "<strong>Motivo:</strong> ".preg_replace('/((\w+\W*){'.(15-1).'}(\w+))(.*)/', '${1}', $data['mensaje'])."...";
                        break;
                    case 2:
                        $notification->titulo = "<strong>Control de stock</strong><br>";
                        $notification->extracto = $data['mensaje']."...";
                        break;
                }
            }

        }

        return response()->json($notifications);
    }

    public function marcarComoLeido($id){
        $notification = User::whereHas('roles', function ($query) {
            $query->where('id', 5);
        })->first()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return redirect('/notificaciones');
        }
    }

    public function marcarTodoComoLeido(Request $request){
        $notifications = User::whereHas('roles', function ($query) {
            $query->where('id', 5);
        })->first()->unreadNotifications->markAsRead();

        if($request->axios){
           return $this->obtenerNotificaciones();
        }
        return redirect('/notificaciones');
    }
}
