<?php

namespace sysfact\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;

class UpdateUserSessionId
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function handle(Login $event)
    {
        $user = $event->user;

        // Obtén el ID de sesión actual
        $sessionId = Session::getId();

        // Actualiza el ID de sesión del usuario en la base de datos
        $user->session_id = $sessionId;
        $user->save();
    }
}
