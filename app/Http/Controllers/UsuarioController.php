<?php

namespace App\Http\Controllers;

use App\Unidad;
use App\Usuario;
use App\UsuarioTieneRol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    // muestra la vista del buscador de personal
    public function mostrarBuscarPersonal(Unidad $unidad){

        return view('personal.buscarPersonal',[
            'nombreUnidad' => $unidad-> nombre,
            'facultad' => $unidad-> facultad,
        ]);
    }
    // devuelve la vista de todo el personal academico de la unidad correspondiente
    public function obtenerPersonal(Unidad $unidad)
    {
        $todos = Usuario::join('Usuario_pertenece_unidad', 'codSis', '=', 'usuario_codSis')
            ->where('unidad_id', '=', $unidad->id)
            ->select('Usuario.nombre', 'Usuario.codSis')
            ->get();
        foreach ($todos as $key => $usuario) {
            $usuario->roles = UsuarioTieneRol::where('usuario_codSis', '=', $usuario->codSis)
                ->where('rol_id', '>=', 1)
                ->where('rol_id', '<=', 3)
                ->select('rol_id')
                ->get();
        }

        $docentes = $this->obtenerUsuariosRol($unidad, 3);
        $auxiliaresDoc = $this->obtenerUsuariosRol($unidad, 2);
        $auxiliaresLabo = $this->obtenerUsuariosRol($unidad, 1);
        return view('personal.listaPersonal', [
            'unidad' => $unidad,
            'todos' => $todos,
            'docentes' => $docentes,
            'auxiliaresDoc' => $auxiliaresDoc,
            'auxiliaresLabo' => $auxiliaresLabo
        ]);
    }

    // busca coincidencias en los nombres del personal que pertenecen a cierta unidad academica
    public function buscarPersonal(Unidad $unidad)
    {
        $aux = Usuario::join('Usuario_pertenece_unidad', 'codSis', '=', 'usuario_codSis')
            ->where('unidad_id', '=', $unidad->id)
            ->select('nombre')
            ->get();
        $personal = [];
        foreach ($aux as $key => $usuario) {
            $personal[$usuario->codSis] = $usuario->nombre;
            array_push($personal, $usuario->nombre);
        }
        request()->session()->flash('info', 'Resultados de buscar ');
        return $personal;
    }

    // obtener usuarios con el rol indicado que pertenezcan a la unidad indicada
    private function obtenerUsuariosRol(Unidad $unidad, $rol)
    {
        return Usuario::join('Usuario_pertenece_unidad', 'codSis', '=', 'Usuario_pertenece_unidad.usuario_codSis')
            ->where('unidad_id', '=', $unidad->id)
            ->join('Usuario_tiene_rol', 'codSis', '=', 'Usuario_tiene_rol.usuario_codSis')
            ->where('rol_id', '=', $rol)
            ->select('Usuario.nombre', 'Usuario.codSis')
            ->get();
    }

    // devuelve codSis si el codSis es de un docente de la unidad_id
    public static function esDocente($codSis, $unidad_id)
    {
        return self::esDelRol($codSis, $unidad_id, 3);
    }

    // devuelve codSis si el codSis es de un docente de la unidad_id
    public static function esAuxDoc($codSis, $unidad_id)
    {
        return self::esDelRol($codSis, $unidad_id, 2);
    }

    // devuelve codSis si el codSis es de un docente de la unidad_id
    public static function esAuxLab($codSis, $unidad_id)
    {
        return self::esDelRol($codSis, $unidad_id, 1);
    }

    // devuelve codSis si el codSis tiene el rol de la unidad_id
    private static function esDelRol($codSis, $unidad_id, $rol)
    {
        return !UsuarioTieneRol::where('rol_id', '=', $rol)->where('Usuario_tiene_rol.usuario_codSis', '=', $codSis)->join('Usuario_pertenece_unidad', 'Usuario_pertenece_unidad.usuario_codSis', '=', 'Usuario_tiene_rol.usuario_codSis')->where('unidad_id', '=', $unidad_id)->get()->isEmpty();
    }
}