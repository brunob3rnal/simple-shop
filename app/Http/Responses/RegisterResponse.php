<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Fortify inicia sesión al crear la cuenta; esta Story pide que la persona
     * inicie sesión ella misma, así que se cierra y se la lleva al login.
     *
     * logout() no invalida la sesión: el carrito, que vive en ella, se conserva.
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        Auth::guard(config('fortify.guard'))->logout();

        return redirect()->route('login')->with('status', 'Cuenta creada');
    }
}
