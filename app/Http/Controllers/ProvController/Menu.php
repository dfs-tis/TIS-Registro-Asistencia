<?php

namespace App\Http\Controllers\ProvController;

use App\Unidad;
use App\Usuario;
use App\UsuarioTieneRol;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Menu extends Controller
{
    //Para cada tipo de usuario 
    //se deben mostrar sus nombres completos
    //en un enlace

    public function docentes()
    {
        $docentes = UsuarioTieneRol::where('rol_id', '=', 3)
            ->join('public.Usuario', 'public.Usuario_tiene_rol.usuario_codSis', '=', 'public.Usuario.codSis')
            ->select('public.Usuario_tiene_rol.usuario_codSis', 'public.Usuario.nombre')->paginate(10);
        // return $docentes;
        return view('provicional.docentes', [
            'docentes' => $docentes
        ]);
    }
    public function docente(Usuario $usuario)
    {
        return view('provicional.docente', [
            'usuario' => $usuario
        ]);
    }
    public function auxiliarDoc(Usuario $usuario)
    {
        return view('provicional.auxiliarDoc', [
            'usuario' => $usuario
        ]);
    }
    public function auxiliarLabo(Usuario $usuario)
    {
        return view('provicional.auxiliarLabo', [
            'usuario' => $usuario
        ]);
    }
    public function auxiliaresDoc()
    {
        $auxiliaresDoc = UsuarioTieneRol::where('rol_id', '=', 2)
            ->join('public.Usuario', 'public.Usuario_tiene_rol.usuario_codSis', '=', 'public.Usuario.codSis')
            ->select('public.Usuario_tiene_rol.usuario_codSis', 'public.Usuario.nombre')->paginate(10);
        // return $docentes;
        return view('provicional.auxiliaresDoc', [
            'auxiliaresDoc' => $auxiliaresDoc
        ]);
    }
    public function auxiliaresLabo()
    {
        $auxiliaresLabo = UsuarioTieneRol::where('rol_id', '=', 1)
            ->join('public.Usuario', 'public.Usuario_tiene_rol.usuario_codSis', '=', 'public.Usuario.codSis')
            ->select('public.Usuario_tiene_rol.usuario_codSis', 'public.Usuario.nombre')->paginate(10);
        // return $docentes;
        return view('provicional.auxiliaresLabo', [
            'auxiliaresLabo' => $auxiliaresLabo
        ]);
    }
    public function departamentos()
    {
        $departamentos = Unidad::get();
        // return $docentes;
        return view('provicional.departamentos', [
            'departamentos' => $departamentos
        ]);
    }
    public function jefesDept()
    {
        return view('provicional.jefesDept');
    }
    public function encargadosAsist()
    {
        return view('provicional.encargadosAsist');
    }
}