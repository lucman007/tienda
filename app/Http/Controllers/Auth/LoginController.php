<?php

namespace sysfact\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use sysfact\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

	public function username()
	{
		return 'usuario';
	}

	public function authenticate(Request $request)
	{
		$usuario = $request->usuario;
		$password = $request->password;
		$remember = ($request->input('remember')) ? true : false;

		if (Auth::attempt(['usuario'=>$usuario,'password'=>$password,'eliminado'=>0], $remember)) {
			// Authentication passed...
            $user = auth()->user();
            Auth::login($user,true);
            if(auth()->user()->hasRole('Contabilidad')){
                return redirect('/reportes/comprobantes');
            }
            return redirect('/pedidos');
		} else{
            return redirect('login')
                ->withInput()
                ->withErrors(['mensaje' => 'Las credenciales de acceso no son correctas']);
        }
	}

	public function logout() {
		Auth::logout();
		return redirect('/');
	}
}
