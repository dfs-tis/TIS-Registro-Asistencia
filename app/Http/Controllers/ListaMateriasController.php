<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Unidad;
use App\Materia;
class ListaMateriasController extends Controller
{
    public function mostrarMaterias($unidadId){
        $unidad = Unidad::where('id','=',$unidadId) -> select('nombre','facultad')->get();
        $materias = Materia::where('unidad_id', '=', $unidadId) 
                            ->where('es_materia', '=', true)
                            -> select('nombre', 'id') 
                            -> orderBy('nombre')
                            -> paginate(10);
        return view('informacion.listaMaterias',[
            'nombreUnidad' => $unidad[0] -> nombre,
            'facultad' => $unidad[0] -> facultad,
            'materias' => $materias
        ]);
    }

    public function mostrarCargosDeLaboratorio($unidadId) {
        $unidad = Unidad::where('id','=',$unidadId) -> select('nombre','facultad')->get();
        $materias = Materia::where('unidad_id', '=', $unidadId)
        ->where('es_materia', '=', false)
        -> select('nombre', 'id')
        -> orderBy('nombre')
        -> paginate(10);
        return view('informacion.listaCargos',[
        'nombreUnidad' => $unidad[0] -> nombre,
        'facultad' => $unidad[0] -> facultad,
        'cargos' => $materias
        ]);
    }
}
