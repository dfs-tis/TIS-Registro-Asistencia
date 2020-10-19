<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Auxiliar laboratorio</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <style>
        table, th, td {
         border: 1px solid black;
        }
        input{
            text-align: center;
        }
    </style>
</head>


<body>
    <div class="container">
        <div class="row">
            <div class="col-8">
                <h5>PLANILLA SEMANAL DE ASISTENCIA</h5>
                @if(!$horarios->isEmpty())
                    <p>NOMBRE AUXILIAR DOCENCIA: {{ $horarios[0]->asignado->nombre }}</p>
                    <p>CODSIS: {{ $horarios[0]->asignado->codSis }} </p>                
                @endif              
            </div>
            <div class="col-4">
                <p>DESDE: {{ $fechaInicio }} </p>
                <p>HASTA: {{ $fechaFinal }} </p>
            </div>
        </div>
            <table class="table">
                <thead class="thead-dark">
                    <tr>
                    <th scope="col">FECHA</th>
                    <th scope="col">HORARIO</th>
                    <th scope="col">GRUPO</th>
                    <th scope="col">MATERIA</th>
                    <th scope="col">ACTIVIDAD REALIZADA</th>
                    <th scope="col">OBSERVACIONES</th>
                    <th scope="col">ASISTENCIA</th>
                    <th scope="col">PERMISO</th>
                    </tr>
                </thead>
                <form method="POST"  action="{{ route('planillas.semanal') }}">
                    @csrf
                    <tbody>
                        @forelse ($horarios as $key => $horario)
                            <tr>
                                <td>{{ $horario->dia }}<br>{{ $fechasDeSemana[$horario->dia]}}</td>
                                <td>{{ $horario->hora_inicio }} - {{ $horario->hora_fin }}</td>
                                <td>{{ $horario->grupo->nombre }}</td>
                                <td>{{ $horario->materia->nombre }}</td>                    
                                <td><input  class="form-control"  type="text" name="asistencias[{{ $key }}][actividad_realizada]" id="actividad"/></td> 
                                <td><input  class="form-control"  type="text" name="asistencias[{{ $key }}][observaciones]" id="observacion"/></td>                     
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input onchange="habilitarPermiso('{!! $horario->materia->nombre !!}','{!! $horario->grupo->nombre !!}')" type="checkbox" name="asistencias[{{ $key }}][asistencia]" 
                                        class="custom-control-input" id="asistencia-{{ $horario->materia->nombre }}-{{ $horario->grupo->nombre }}"checked/>

                                        <label class="custom-control-label" for="asistencia-{{ $horario->materia->nombre }}-{{ $horario->grupo->nombre }}"></label>
                                    </div> 
                                </td>  
                                <td >
                                    <select id="columnaPermiso-{{ $horario->materia->nombre }}-{{ $horario->grupo->nombre }}" name="asistencias[{{ $key }}][permiso]" disabled>
                                        <option value="LICENCIA">Licencia</option>
                                        <option value="BAJA_MEDICA">Baja medica</option>
                                        <option value="DECLARATORIA_EN_COMISION">Declaratoria en comision</option>
                                    </select>
                                </td>  
                            </tr>
                            <input type="text" name="asistencias[{{ $key }}][horario_clase_id]" value="{{ $horario->id }}" style="display: none;">
                            
                            <input type="text" name="asistencias[{{ $key }}][horario_clase_id]" value="{{ $horario->id }}" style="display: none;">
                        @empty
                            <p>NO HAY HORARIOS</p>
                        @endforelse
                    </tbody>
                    </table>  
                    <button class="btn btn-success">SUBIR</button>        
                </form>

    </div>
</body>
<!-- jQuery and JS bundle w/ Popper.js -->

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" ></script>
<script>
    function habilitarPermiso(a, b) {
        var checkBox = document.getElementById("asistencia-"+a+"-"+b);
        var columna = document.getElementById("columnaPermiso-"+a+"-"+b);
        if (checkBox.checked == false){
            columna.disabled = false;
        } else {
            columna.disabled = true;
            // columna.value = "LICENCIA";
        }
    }
</script>
</html>