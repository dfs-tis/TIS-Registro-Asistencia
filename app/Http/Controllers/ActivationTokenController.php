<?php

namespace App\Http\Controllers;

use App\ActivationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ActivationTokenController extends Controller
{
    public function activate($token)
    {
        // $token->user->update(['active' => true]);

        // Auth::login($token->user);

        // $token->delete();

        if (!ActivationToken::where('token', $token)->get()->isEmpty()) {
            ActivationToken::where('token', $token)->first()->user->activate();
        } else {
            return redirect('/login')->with('error', 'El enlace que seguiste ya fue utilizado o ya expiró.');
        }


        return redirect('/')->with('success', 'Tu cuenta ha sido activada exitosamente, ya puedes iniciar sesión');
    }
}
