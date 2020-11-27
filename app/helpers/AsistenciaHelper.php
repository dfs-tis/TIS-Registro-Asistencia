<?php

namespace App\helpers;

use App\Unidad;
use App\Usuario;
use Carbon\Carbon;
use App\Asistencia;
use Illuminate\Support\Facades\DB;

class AsistenciaHelper
{
    // devuelve asistencias de la unidad dado rol y fechas con el orden establecido
    public static function obtenerAsistenciasRol(Unidad $unidad, $rol, $fechaInicio, $fechaFin)
    {
        return self::aux($unidad, $fechaInicio, $fechaFin)
            ->join('Horario_clase', 'horario_clase_id', '=', 'Horario_clase.id')
            ->where('Horario_clase.rol_id', '=', $rol)
            ->join('Usuario', 'usuario_codSis', '=', 'codSis')
            ->join('Materia', 'Asistencia.materia_id', '=', 'Materia.id')
            ->orderBy('Materia.nombre', 'ASC')
            ->orderBy('Horario_clase.grupo_id')
            ->orderBy('Usuario.nombre', 'ASC')
            ->orderBy('fecha', 'DESC')
            ->get();
    }

    // devuelve asistencias de la unidad dado rol, fechas de un determinado usuario
    public static function obtenerAsistenciasUsuarioRol(Unidad $unidad, $rol, $nivel, $fechaInicio, $fechaFin, Usuario $usuario)
    {
        return self::aux($unidad, $fechaInicio, $fechaFin)
            ->join('Horario_clase', 'horario_clase_id', '=', 'Horario_clase.id')
            ->where('Horario_clase.rol_id', '=', $rol)
            ->where('nivel', '=', $nivel)
            ->where('usuario_codSis', '=', $usuario->codSis)->get();
    }

    // funcion auxiliar
    private static function aux(Unidad $unidad, $fechaInicio, $fechaFin)
    {
        return Asistencia::where('Asistencia.unidad_id', '=', $unidad->id)
            ->where('fecha', '>=', $fechaInicio)
            ->where('fecha', '<=', $fechaFin)
            ->select('Asistencia.*');
    }

    public static function obtenerAsistenciasUnidad(Unidad $unidad, $fechaInicio, $fechaFin)
    {
        return self::aux($unidad, $fechaInicio, $fechaFin)
            ->get();
    }

    public static function obtenerAsistenciasUnidadUsuario(Unidad $unidad, Usuario $usuario, $fechaInicio, $fechaFin)
    {
        return self::aux($unidad, $fechaInicio, $fechaFin)
            ->join('Usuario', 'usuario_codSis', '=', 'codSis')
            ->where('codSis', '=', $usuario->codSis)
            ->get();
    }
}