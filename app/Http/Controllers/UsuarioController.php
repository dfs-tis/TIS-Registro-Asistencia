<?php

namespace App\Http\Controllers;

use App\Unidad;
use App\Usuario;
use Carbon\Carbon;
use App\Asistencia;
use App\HorarioClase;
use App\UsuarioTieneRol;
use Illuminate\Http\Request;
use App\helpers\BuscadorHelper;
use App\UsuarioPerteneceUnidad;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class UsuarioController extends Controller
{
    // devuelve la vista de todo el personal academico de la unidad correspondiente
    public function obtenerPersonal(Unidad $unidad, $codigos = null)
    {
        $todos = Usuario::join('Usuario_pertenece_unidad', 'codSis', '=', 'usuario_codSis')
            ->where('unidad_id', '=', $unidad->id)->select(
                'Usuario.nombre',
                'Usuario.codSis'
            );
        if (is_array($codigos)) {
            $raw = 'case';
            foreach ($codigos as $key => $codSis) {
                $raw .= ' when "Usuario"."codSis"=' . $codSis . ' then ' . $key;
            }
            $raw .= ' end';
            $todos = $todos->whereIn('codSis', $codigos)
                ->orderByRaw($raw);
        } else
            $todos = $todos->orderBy('nombre', 'asc');
        $todos = $todos->paginate(10, ['*'], 'todos-pag');
        foreach ($todos as $key => $usuario) {
            $usuario->roles = UsuarioTieneRol::where('usuario_codSis', '=', $usuario->codSis)
                ->where('rol_id', '>=', 1)
                ->where('rol_id', '<=', 3)
                ->join('Rol', 'Rol.id', '=', 'rol_id')
                ->select('nombre')
                ->get();
        }
        $docentes = $this->obtenerUsuariosRol($unidad, 3, $codigos);
        $auxiliaresDoc = $this->obtenerUsuariosRol($unidad, 2, $codigos);
        $auxiliaresLabo = $this->obtenerUsuariosRol($unidad, 1, $codigos);
        return view('personal.listaPersonal', [
            'unidad' => $unidad,
            'todos' => $todos,
            'docentes' => $docentes,
            'auxiliaresDoc' => $auxiliaresDoc,
            'auxiliaresLabo' => $auxiliaresLabo
        ]);
    }

    // busca coincidencias en los nombres del personal que pertenecen a cierta unidad academica
    public function buscarPersonal(Unidad $unidad, $buscando = null)
    {
        if (request()->method() == 'POST') {
            $datos = $this->validarBuscado();
            return redirect()->route('personalAcademico.buscando', [
                'unidad' => $unidad,
                'buscando' => $datos['buscado']
            ]);
        }
        request()['buscado'] = $buscando;
        $datos = $this->validarBuscado();
        $buscando =  BuscadorHelper::separar(BuscadorHelper::normalizar($datos['buscado']));
        $aux = Usuario::join('Usuario_pertenece_unidad', 'codSis', '=', 'usuario_codSis')
            ->where('unidad_id', '=', $unidad->id)
            ->get();
        $personal = [];
        foreach ($aux as $usuario) {
            $coincidencias = BuscadorHelper::coincidencias(strtolower($usuario->nombre), $buscando);
            if ($coincidencias > 0.5) {
                $personal[$usuario->codSis] = $coincidencias;
            }
        }
        arsort($personal);
        $codigos = [];
        foreach ($personal as $key => $value) {
            array_push($codigos, $key);
        }
        request()->session()->flash('info', 'Resultados de la busqueda');
        return $this->obtenerPersonal($unidad, $codigos);
    }

    private function validarBuscado()
    {
        return request()->validate([
            'buscado' => ['required', 'regex:/^[a-zA-Z\s]*$/', 'max:50']
        ]);
    }

    // obtener usuarios con el rol indicado que pertenezcan a la unidad indicada
    private function obtenerUsuariosRol(Unidad $unidad, $rol, $codigos = null)
    {
        $usuarios = Usuario::join('Usuario_pertenece_unidad', 'codSis', '=', 'Usuario_pertenece_unidad.usuario_codSis')
            ->where('unidad_id', '=', $unidad->id)
            ->join('Usuario_tiene_rol', 'codSis', '=', 'Usuario_tiene_rol.usuario_codSis')
            ->where('rol_id', '=', $rol)
            ->select('Usuario.nombre', 'Usuario.codSis');
        if (is_array($codigos)) {
            $raw = 'case';
            foreach ($codigos as $key => $codSis) {
                $raw .= ' when "Usuario"."codSis"=' . $codSis . ' then ' . $key;
            }
            $raw .= ' end';
            $usuarios = $usuarios->whereIn('codSis', $codigos)
                ->orderByRaw($raw);
        } else
            $usuarios = $usuarios->orderBy('nombre', 'asc');
        return
            $usuarios->paginate(10, ['*'], 'usuario-' . $rol . '-pag');;
    }
    //devuelve los grupos en los que haya sido asignado el codsis, dependiendo si esta activo o si es materia
    private function buscarGruposAsignadosActuales($unidadId,$codSis,$esMateria){
        return  HorarioClase::  join('Usuario', 'Usuario.codSis', '=',"Horario_clase.asignado_codSis") 
                                ->join('Grupo', 'Grupo.id' ,'=', 'Horario_clase.grupo_id')
                                ->join('Materia', 'Materia.id', '=', 'Horario_clase.materia_id')
                                ->where('Grupo.unidad_id', '=', $unidadId)
                                ->where('asignado_codSis','=', $codSis)
                                ->where('Materia.es_materia',$esMateria)
                                ->distinct()
                                ->select('Horario_clase.grupo_id','Materia.nombre AS nombre_materia', 'Materia.id AS materia_id', 'Grupo.nombre AS nombre_grupo')->get();
                            }
    private function buscarGruposAsignadosPasados($unidadId,$codSis,$esMateria,$activos){
        return Asistencia::    join('Usuario', 'Usuario.codSis', '=',"Asistencia.usuario_codSis")   
                                ->join('Grupo', 'Grupo.id' ,'=', 'Asistencia.grupo_id')
                                ->join('Materia', 'Materia.id', '=', 'Asistencia.materia_id')
                                ->where('Grupo.unidad_id', '=', $unidadId)
                                ->whereNotIn('Asistencia.grupo_id',$activos)
                                ->where('usuario_codSis','=', $codSis)
                                ->where('Materia.es_materia',$esMateria)
                                ->distinct()
                                ->select('Asistencia.grupo_id','Materia.nombre')->get();
    }
    //devuelve la vista de la informacion del auxiliar
    public function informacionAuxiliar(Unidad $unidad, Usuario $usuario)
    {
        $this->validarUsuarioDeUnidad($unidad, $usuario, [1, 2]);
        $codSis = $usuario->codSis;
        $unidadId = $unidad->id;
        $gruposActuales = self::buscarGruposAsignadosActuales($unidadId,$codSis,'true');
        $gruposPasados = self::buscarGruposAsignadosPasados($unidadId,$codSis,'true',array_column($gruposActivos->toArray(),'grupo_id'));

        $itemsActuales = self::buscarGruposAsignadosActuales($unidadId,$codSis,'false');
        $itemsPasados = self::buscarGruposAsignadosPasados($unidadId,$codSis,'false',array_column($itemsActuales->toArray(),'grupo_id'));

        $asistencias = $this->asistenciasUsuarioUnidad($unidad, $usuario);

        return view('personal.informacionAuxiliar', [
            'asistencias' => $asistencias,
            'gruposActivos' => $gruposActuales,
            'gruposInactivos' => $gruposPasados,
            'itemsActuales' => $itemsActuales,
            'itemsPasados' => $itemsPasados
        ]);
    }

    // devuelve la vista de la informacion del docente
    public function informacionDocente(Unidad $unidad, Usuario $usuario)
    {
        $this->validarUsuarioDeUnidad($unidad, $usuario, [3]);
        $codSis = $usuario->codSis;
        $unidadId = $unidad->id;
        $gruposActivos = self::buscarGruposAsignadosActuales($unidadId,$codSis,'true');
        $gruposInactivos = self::buscarGruposAsignadosPasados($unidadId,$codSis,'true',array_column($gruposActivos->toArray(),'grupo_id'));


        $asistencias = $this->asistenciasUsuarioUnidad($unidad, $usuario);

        return view('personal.informacionDocente', [
            'asistencias' => $asistencias,
            'gruposInactivos' => $gruposInactivos,
            'gruposActivos' => $gruposActivos
        ]);
    }

    // obtiene asistencias del usuario en la unidad ordenadas por tiempo en orden decreciente
    private function asistenciasUsuarioUnidad(Unidad $unidad, Usuario $usuario)
    {
        $asistencias = Asistencia::where('usuario_codSis', '=', $usuario->codSis)
            ->where('unidad_id', '=', $unidad->id)
            ->get();
        $asistencias = $asistencias->sort(function (Asistencia $a, Asistencia $b) {
            $a1 = Carbon::createFromFormat('Y-m-d H:i:s',  $a->fecha . ' ' . $a->horarioClase->hora_inicio);
            $b1 = Carbon::createFromFormat('Y-m-d H:i:s',  $b->fecha . ' ' . $b->horarioClase->hora_inicio);
            return $a1->lt($b1) ? 1 : -1;
        });
        // esta en 1 para probar, luego cambiar a 10
        return paginate($asistencias, 1);
    }

    // validar que el usuario pertenezca a la unidad y tenga los roles debidos
    private function validarUsuarioDeUnidad(Unidad $unidad, Usuario $usuario, $roles)
    {
        if (UsuarioPerteneceUnidad::where('Usuario_pertenece_unidad.usuario_codSis', '=', $usuario->codSis)
            ->where('unidad_id', '=', $unidad->id)
            ->join('Usuario_tiene_rol', 'Usuario_tiene_rol.usuario_codSis', '=', 'Usuario_pertenece_unidad.usuario_codSis')
            ->whereIn('rol_id', $roles)
            ->count() == 0
        ) {
            $error = ValidationException::withMessages([
                'usuario' => ['usuario invalido']
            ]);
            throw $error;
        }
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