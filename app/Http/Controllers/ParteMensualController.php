<?php

namespace App\Http\Controllers;

use App\Rol;
use App\Unidad;
use App\Usuario;
use Carbon\Carbon;
use App\Asistencia;
use App\HorarioClase;
use App\ParteMensual;
use App\Facultad;
use Illuminate\Http\Request;
use App\Helpers\FechasPartesMensualesHelper;
use App\Helpers\AsistenciaHelper;
use Illuminate\Support\Facades\DB;
use PDF;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\UsuarioTieneRol;
use Illuminate\Validation\ValidationException;

class ParteMensualController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('auth');
    }

    //Generar parte auxiliares
    private function generarParteAuxiliares(Unidad $unidad, $fecha)
    {
        // obtener fechas inicio y fin del mes
        calcularFechasMes($fecha, $t, $fechaInicio, $fechaFin);

        // obtener usuarios con rol
        $auxLabo = $this->usuariosRolUnidad(1, $unidad);
        $auxDoc = $this->usuariosRolUnidad(2, $unidad);

        // inicializar horas pagables en 0
        $totPagables = 0;
        $totNoPagables = 0;

        // obtener partes 
        $parteLabo = $this->parteMensual($auxLabo, $unidad, 1, $fechaInicio, $fechaFin, $totPagables, $totNoPagables);
        $parteDoc = $this->parteMensual($auxDoc, $unidad, 2, $fechaInicio, $fechaFin, $totPagables, $totNoPagables);

        $parteCombinado = $this->combinar($parteLabo, $parteDoc);
        return  [
            'unidad' => $unidad,
            'fecha' => $fecha,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'gestion' => $t->year,
            'parteLabo' => $parteLabo,
            'parteDoc' => $parteDoc,
            'parteCombinado' => $parteCombinado,
            'totPagables' => $totPagables,
            'totNoPagables' => $totNoPagables
        ];
    }

    // dada unidad y fecha devuelve vista de parte de auxiliares
    public function obtenerParteAuxiliares(Unidad $unidad, $fecha)
    {
        // Verificamos que el usuario tiene los roles permitidos
        $accesoOtorgado = Auth::user()->usuario->tienePermisoNombre('ver partes mensuales')
            & Auth::user()->usuario->perteneceAUnidad($unidad->id);
        if (!$accesoOtorgado) {
            return view('provicional.noAutorizado');
        }
        $parteAuxiliares = $this->generarParteAuxiliares($unidad, $fecha);

        // devolver la vista de parte mensual de auxiliares
        return view('parteMensual.auxiliares', $parteAuxiliares);
    }
    //Generar parte docentes
    private function generarParteDocentes(Unidad $unidad, $fecha)
    {
        // obtener fechas inicio y fin del mes        
        calcularFechasMes($fecha, $t, $fechaInicio, $fechaFin);

        // obtener usuarios con rol docente
        $docentes = $this->usuariosRolUnidad(3, $unidad);

        // inicializar horas pagables en 0
        $totPagables = 0;
        $totNoPagables = 0;

        // obtener parte
        $parteDoc = $this->parteMensual($docentes, $unidad, 3, $fechaInicio, $fechaFin, $totPagables, $totNoPagables);
        return [
            'unidad' => $unidad,
            'fecha' => $fecha,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'gestion' => $t->year,
            'parteDoc' => $parteDoc,
            'totPagables' => $totPagables,
            'totNoPagables' => $totNoPagables
        ];
    }
    // dada unidad y fecha devuelve vista de parte de docentes
    public function obtenerParteDocentes(Unidad $unidad, $fecha)
    {
        // Verificamos que el usuario tiene los roles permitidos
        $accesoOtorgado = Auth::user()->usuario->tienePermisoNombre('ver partes mensuales')
            & Auth::user()->usuario->perteneceAUnidad($unidad->id);
        if (!$accesoOtorgado) {
            return view('provicional.noAutorizado');
        }
        $parteDocentes = $this->generarParteDocentes($unidad, $fecha);

        // devolver la vista de parte mensual de auxiliares
        return view('parteMensual.docentes', $parteDocentes);
    }


    // obtiene parte mensual (pagable y no pagable se van sumando y se recupera pase por referencia)
    private function parteMensual($usuarios, $unidad, $rol, $fechaInicio, $fechaFin, &$pagable, &$noPagable)
    {
        $parte = [];
        $periodo = $rol == 1 ? 60 : 45;
        foreach ($usuarios as $key => $usuario) {
            $cargaNominal = $this->nominalMes($usuario, $rol, $periodo, $unidad);
            if ($cargaNominal > 0) {
                $asistencias = AsistenciaHelper::obtenerAsistenciasUsuarioRol($unidad, $rol, 3, $fechaInicio, $fechaFin, $usuario);
                $reporte = [
                    'codSis' => $usuario->codSis,
                    'nombre' => $usuario->nombre(),
                    'cargaHorariaNominal' => $cargaNominal,
                    'cargaHorariaEfectiva' => 0.0,
                    'asistidas' => 0.0,
                    'falta' => 0.0,
                    'LICENCIA' => 0.0,
                    'BAJA_MEDICA' => 0.0,
                    'DECLARATORIA_EN_COMISION' => 0.0,
                    'pagable' => 0.0,
                    'noPagable' => 0.0
                ];
                foreach ($asistencias as $key => $asistencia) {
                    $inicio = $asistencia->horarioClase->hora_inicio;
                    $fin = $asistencia->horarioClase->hora_fin;
                    $horas = tiempoHora($inicio)->diffInMinutes(tiempoHora($fin)) / $periodo;
                    $reporte['cargaHorariaEfectiva'] += $horas;
                    if ($asistencia->asistencia) {
                        $reporte['pagable'] += $horas;
                        $reporte['asistidas'] += $horas;
                    } else {
                        if ($asistencia->permiso && $asistencia->permiso == 'DECLARATORIA_EN_COMISION')
                            $reporte['pagable'] += $horas;
                        else
                            $reporte['noPagable'] += $horas;
                        if ($asistencia->permiso)
                            $reporte[$asistencia->permiso] += $horas;
                        else
                            $reporte['falta'] += $horas;
                    }
                }
                $pagable += $reporte['pagable'];
                $noPagable += $reporte['noPagable'];
                $parte[$usuario->codSis] = $reporte;
            }
        }
        return $parte;
    }

    // combina las horas de 2 partes
    private function combinar($parte1, $parte2)
    {
        foreach ($parte2 as $key => $reporte) {
            if (array_key_exists($key, $parte1)) {
                foreach ($reporte as $key1 => $value)
                    if ($key1 != 'codSis' && $key1 != 'nombre')
                        $parte1[$key][$key1] += $value;
            } else
                $parte1[$key] = $reporte;
        }
        usort($parte1, function ($a, $b) {
            return $a['nombre'] < $b['nombre'] ? -1 : 1;
        });
        return $parte1;
    }

    // da usuarios de cierto rol que pertenecen a cierta unidad
    private function usuariosRolUnidad($rol, $unidad)
    {
        return Usuario::join('Usuario_tiene_rol', 'Usuario.codSis', '=', 'Usuario_tiene_rol.usuario_codSis')
            ->where('Usuario_tiene_rol.rol_id', '=', $rol)
            ->join('Usuario_pertenece_unidad', 'Usuario.codSis', '=', 'Usuario_pertenece_unidad.usuario_codSis')
            ->where('Usuario_pertenece_unidad.unidad_id', '=', $unidad->id)
            ->select('Usuario.codSis', 'Usuario.nombre')
            ->orderBy('Usuario.nombre')
            ->get();
    }

    // devuelve la carga horaria nominal semana * 4 de un usuario con el respectivo rol
    private function nominalMes(Usuario $usuario, $rol, $periodo, $unidad)
    {
        $cargaNominal = 0;
        $horarios = HorarioClase::where('activo', '=', true)
            ->where('asignado_codSis', '=', $usuario->codSis)
            ->where('rol_id', '=', $rol)
            ->where('unidad_id', '=', $unidad->id)
            ->get();
        foreach ($horarios as $key => $horario) {
            $inicio = $horario->hora_inicio;
            $fin = $horario->hora_fin;
            $horas = tiempoHora($inicio)->diffInMinutes(tiempoHora($fin)) / $periodo;
            $cargaNominal += $horas;
        }
        return 4 * $cargaNominal;
    }
    //Obtener PDF de parte mensual Docentes
    public function descargarPDFDocentes(Unidad $unidad, $fecha)
    {
        // Verificamos que el usuario tiene los roles permitidos
        $accesoOtorgado = Auth::user()->usuario->tienePermisoNombre('ver partes mensuales')
            & Auth::user()->usuario->perteneceAUnidad($unidad->id);
        if (!$accesoOtorgado) {
            return view('provicional.noAutorizado');
        }
        $respuesta = $this->generarParteDocentes($unidad, $fecha);
        return PDF::loadView('parteMensual.docentesPDF', $respuesta)
            ->setPaper('letter', 'landscape')
            ->stream('Parte Docentes-' . $unidad->nombre . '.pdf');
    }
    //Obtener PDF de parte mensual Auxiliares
    public function descargarPDFAuxiliares(Unidad $unidad, $fecha)
    {
        // Verificamos que el usuario tiene los roles permitidos
        $accesoOtorgado = Auth::user()->usuario->tienePermisoNombre('ver partes mensuales')
            & Auth::user()->usuario->perteneceAUnidad($unidad->id);
        if (!$accesoOtorgado) {
            return view('provicional.noAutorizado');
        }

        $respuesta = $this->generarParteAuxiliares($unidad, $fecha);
        return PDF::loadView('parteMensual.auxiliaresPDF', $respuesta)
            ->setPaper('letter', 'landscape')
            ->stream('Parte Auxiliares-' . $unidad->nombre . '.pdf');
    }
    //Aprueba el parte de acuerdo al rol
    public function aprobarPartePorRol(Request $request)
    {
        $acceso = Auth::user()->usuario->tienePermisoNombre('aprobar partes mensuales');
        if (!$acceso) {
            return view('provicional.noAutorizado');
        }

        $idParte = $request->parte_id;
        $rol = $request->rol;
        $parte = ParteMensual::where("id", "=", $idParte)->first();
        switch ($rol) {
            case 4:
                $parte = ParteMensual::where("id", "=", $idParte)->update(['jefe_dept' => true]);
                break;
            case 5:
                $parte = ParteMensual::where("id", "=", $idParte)->update(['encargado_fac' => true]);
                break;
            case 6:
                $parte = ParteMensual::where("id", "=", $idParte)->update(['decano' => true]);
                break;
            case 7:
                $parte = ParteMensual::where("id", "=", $idParte)->update(['dir_academico' => true]);
                break;
        }
        return back()->with('success', 'Aprobacion exitosa');
    }
    //Enviar los ultimos partes a DPA
    public function enviarDPA(Request $request)
    {
        $acceso = Auth::user()->usuario->tienePermisoNombre('enviar partes mensuales a dpa');
        if (!$acceso) {
            return view('provicional.noAutorizado');
        }

        $depts = Unidad::where('facultad_id', '=', $request->facultad_id)->get();
        foreach ($depts as $key => $dept) {
            $partes = ParteMensual::where('unidad_id', '=', $dept->id)
                ->where('fecha_ini', '=', $request->fechaIni)
                ->update(['aprobado' => true]);
        }
        return back()->with('success', 'Partes mensuales enviados a DPA correctamente.');
    }
    public function partesMesFacultad(Facultad $facultad, $fecha)
    {
        $fecha = explode("-", $fecha);
        $mes = $fecha[1] . '-' . $fecha[0];
        $fecha[1] = FechasPartesMensualesHelper::getMesNum($fecha[1]);
        $fecha = $fecha[0] . "-" . $fecha[1] . "-16";
        $partesMensuales = ParteMensual::where("fecha_ini", '=', $fecha);
        $departamentos = Unidad::where('Unidad.facultad_id', '=', $facultad->id)
            ->orderBy('Unidad.nombre')
            ->leftJoinSub($partesMensuales, 'partesMensuales', function ($join) {
                $join->on('Unidad.id', '=', 'partesMensuales.unidad_id');
            })
            ->select('Unidad.id', 'partesMensuales.id as parteID', 'Unidad.nombre', 'partesMensuales.aprobado', 'partesMensuales.fecha_ini', 'partesMensuales.fecha_fin', 'partesMensuales.encargado_fac', 'partesMensuales.dir_academico', 'partesMensuales.decano', 'partesMensuales.jefe_dept')
            ->get();


        $rolesPermitidos = [4, 5, 6, 7];
        $rolAceptado = UsuarioTieneRol::alMenosUnRol(Auth::user()->usuario->codSis, $rolesPermitidos);

        //Falta restringir acceso por facultades (los miembros de otra facultad distinta a la ingresada solo ven los
        //                                        partes aprobados)
        $usuarioPerteneceFacultad = PersonalAcademicoController::perteneceAFacultad(Auth::user()->usuario->codSis, $facultad->id);
        if ($rolAceptado && $usuarioPerteneceFacultad) {
            return view('parteMensual.partesMesFacultad', ['mes' => $mes, 'facultad' => $facultad, 'departamentos' => $departamentos]);
        } else {
            $rolesPermitidos = [1, 2, 3, 4, 5, 6, 7, 8];
            $rolAceptado = UsuarioTieneRol::alMenosUnRol(Auth::user()->usuario->codSis, $rolesPermitidos);
            if ($rolAceptado) {
                return view('parteMensual.partesMesFacultadDPA', ['mes' => $mes, 'facultad' => $facultad, 'departamentos' => $departamentos]);
            } else {
                return view('provicional.noAutorizado');
            }
        }
    }
}