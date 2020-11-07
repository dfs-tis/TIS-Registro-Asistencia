<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Editar grupo</title>
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    
  <script src="https://code.jquery.com/jquery-3.5.0.js"></script>
    <link rel="stylesheet" href="/css/estiloGeneral.css">
    <link rel="stylesheet" href="/css/estiloEditarGrupo.css">
</head>


<body >
    <div class="mx-3 my-4">
        <div class="container">
            <div class="row">
                <div class="col-8">
                    <h2 class="textoBlanco" >{{ $grupo->unidad->facultad }}</h4>
                        <h2 class="textoBlanco">{{ $grupo->unidad->nombre }}</h1>
                            <h4 class="textoBlanco">{{ $grupo->materia->nombre }}</h4>
                            <br>
                            <h4 class="textoBlanco">{{ $grupo->nombre }}</h4>
                </div>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="textoBlanco border border-dark" scope="col">HORARIO</th>
                        <th class="textoBlanco border border-dark" scope="col">CARGO</th>
                        <th class="textoBlanco border border-dark" scope="col">OPCIONES</th>
                    </tr>
                </thead>
                <tbody id="cuerpo-tabla">
                    @forelse($horarios as $key => $horario)
                        <tr>
                            <td id={{"horario".$horario->id}} class="border border-dark">
                                <p>{{ $horario->dia }} {{ $horario->hora_inicio }} - {{ $horario->hora_fin }}</p>
                            </td>
                            <td id={{"cargo".$horario->id}} class="border border-dark">
                                <p>
                                    @if ($horario->rol_id === 3)
                                        DOCENCIA
                                    @else
                                        AUXILIATURA
                                    @endif
                                </p>
                            </td>
                            <td id={{"opciones".$horario->id}} class="border border-dark">
                                <input 
                                    id = {{"botonEditar".$horario->id}}
                                    width="30rem"
                                    height="30rem"
                                    type="image"
                                    name="botonEditar" 
                                    src="/icons/editar.png" 
                                    alt="Editar"
                                    onclick="camposEdicionHorarioDeGrupo({{$horario->id}}, {{$horario}}); desactivar()"
                                >
                                <input 
                                    id = {{"botonEliminar".$horario->id}}
                                    width="30rem" height="30rem" 
                                    type="image" name="botonEliminar" 
                                    src="/icons/eliminar.png" alt="Eliminar"
                                    onclick="confirmarEliminarHorario({{ $horario->id }})">
                                <form id="editar-horario{{ $horario->id }}" class="d-none" method="POST"
                                    action="{{ route('horarioClase.actualizar', $horario) }}">
                                    @csrf @method('PATCH')
                                    <input type="number" name="unidad_id" value="{{ $grupo->unidad->id }}">
                                    <input type="number" name="materia_id" value="{{ $grupo->materia->id }}">
                                    <input type="number" name="grupo_id" value="{{ $grupo->id }}">
                                    <input id="horaInicioForm{{ $horario->id }}" type="time" name="hora_inicio">
                                    <input id="horaFinForm{{ $horario->id }}" type="time" name="hora_fin">
                                    <input id="diaForm{{ $horario->id }}" type="text" name="dia">
                                    <input id="rolIdForm{{ $horario->id }}" type="number" name="rol_id">
                                </form>
                                <form id="eliminar-horario{{ $horario->id }}" class="d-none" method="POST"
                                    action="{{ route('horarioClase.eliminar', $horario) }}">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <h5 class="textoBlanco">NO HAY HORARIOS</h5>
                    @endforelse
                </tbody>
            </table>

            <button class="btn boton" id="añadirHorario" onclick="añadirHorario(); desactivar()">AÑADIR HORARIO</button>
            <div class="row rounded-lg" id="personalAcademico">

                <div class="col-12">
                    @if ($horarios != null && $horarios->where('rol_id', '=', 3)->count() > 0)
                        @if ($docente != null)
                            <h4>
                                Docente: {{ $docente->nombre }}
                                <input width="30rem" height="30rem" type="image" 
                                    name="botonDesasignar" id="desasignarDocente"
                                    src="/icons/eliminar.png" alt="Desasignar"
                                    onclick="confirmarDesasignarDocente('{{ $docente->nombre }}')">
                                <form id="desasignar-docente" class="d-none" method="POST"
                                    action="{{ route('grupo.desasignar.docente', $grupo) }}">
                                    @csrf @method('PATCH')
                                </form>
                            </h4>
                            <h4>Carga horaria docente: {{ $cargaHorariaDocente }}</h4>

                        @else
                            <h4>Docente: <button class="btn boton" id="asignarDocente"
                                    onclick="botonAsignar('asignarDocente','botonBuscador1','buscador1','cancelar1','msgObsDocente',true); desactivar()">ASIGNAR
                                    DOCENTE</button>
                                <form method="POST" action="{{ route('grupo.asignar.docente') }}" class="form-inline my-2 my-lg-0 d-inline" onsubmit="return validarBusquedaAsignar('buscador1','msgObsDocente')" action="">
                                    @csrf @method('PATCH')
                                    <input id="buscador1" class=" oculto" type="search" placeholder="codSis docente" aria-label="Search" name='codSis'>
                                    <button id="botonBuscador1" class="btn boton my-2 my-sm-0 oculto" type="submit">
                                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-search"
                                            fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M10.442 10.442a1 1 0 0 1 1.415 0l3.85 3.85a1 1 0 0 1-1.414 1.415l-3.85-3.85a1 1 0 0 1 0-1.415z" />
                                            <path fill-rule="evenodd"
                                                d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11zM13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z" />
                                        </svg>
                                    </button>
                                    <input type="hidden" name = 'grupo_id' value = '{{$grupo->id}}'>
                                </form>
                                <button id="cancelar1" class="btn btn-danger oculto"
                                    onclick="botonAsignar('asignarDocente','botonBuscador1','buscador1','cancelar1','msgObsDocente',false); activar()">
                                    <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-x"
                                        fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                    </svg>
                                </button>
                                <label class="text-danger" id="msgObsDocente" for="buscador1"></label>
                            </h4>
                        @endif
                    @endif
                    @if ($horarios != null && $horarios->where('rol_id', '<=', 2)->count() > 0)
                        @if ($auxiliar != null)
                            <h4>Auxiliar: {{ $auxiliar->nombre }}
                                <input width="30rem" height="30rem" type="image"
                                    name="botonEliminar" id="desasignarAuxiliar"
                                    src="/icons/eliminar.png" alt="Eliminar"></h4>
                            <h4>Carga horaria auxilliar: {{ $cargaHorariaAuxiliar }} </h4>

                        @else
                            <h4>Auxiliar: <button class="btn boton" id="asignarAuxiliar"
                                    onclick="botonAsignar('asignarAuxiliar','botonBuscador2','buscador2','cancelar2','msgObsAuxiliar',true), desactivar()">ASIGNAR
                                    AUXILIAR</button>
                                <form method="POST" action="{{ route('grupo.asignar.auxDoc') }}" class="form-inline my-2 my-lg-0 d-inline" onsubmit="return validarBusquedaAsignar('buscador2','msgObsAuxiliar')">
                                    @csrf @method('PATCH')
                                    <input id="buscador2" class="oculto " type="search" placeholder="codSis auxiliar"
                                        aria-label="Search" name="codSis">
                                    <button id="botonBuscador2" class="btn boton my-2 my-sm-0 oculto" type="submit"><svg
                                            width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-search"
                                            fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M10.442 10.442a1 1 0 0 1 1.415 0l3.85 3.85a1 1 0 0 1-1.414 1.415l-3.85-3.85a1 1 0 0 1 0-1.415z" />
                                            <path fill-rule="evenodd"
                                                d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11zM13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z" />
                                        </svg>
                                    </button>
                                    <input type="hidden" name = 'grupo_id' value = '{{$grupo->id}}'>
                                </form>
                                <button id="cancelar2" class="btn btn-danger oculto"
                                    onclick="botonAsignar('asignarAuxiliar','botonBuscador2','buscador2','cancelar2','msgObsAuxiliar',false); activar()">
                                    <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-x"
                                        fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                    </svg>
                                </button>
                                <label class="text-danger" id="msgObsAuxiliar" for="buscador2"></label>
                            </h4>
                            <div id="prueba">

                            </div>
                        @endif
                    @endif
                </div>
            </div>
            <button class="btn boton float-right" id="regresar" onclick="vistaGrupo({{ $grupo->id }})">REGRESAR</button>
        </div>
    </div>
    <form id="guardar-horario" class="d-none" method="POST"
        action="{{ route('horarioClase.guardar') }}">
        @csrf  
        <input id="unidadId" type="hidden" name="unidad_id" value="{{$grupo->unidad->id}}">
        <input id="materiaId" type="hidden" name="materia_id" value="{{$grupo->materia->id}}">
        <input id="grupoId" type="hidden" name="grupo_id" value="{{$grupo->id}}">
        <input id="horaInicioS" type="hidden" name="hora_inicio">
        <input id="horaFinS" type="hidden" name="hora_fin">
        <input id="diaS" type="hidden" name="dia">
        <input id="rolId" type="hidden" name="rol_id">
    </form>
</body>

<script>
    function confirmarGuardarHorario() {
        document.getElementById("horaFinS").value = document.getElementById("horaFin").value+":00";
        document.getElementById("horaInicioS").value = document.getElementById("horaInicio").value+":00";
        document.getElementById("diaS").value = document.getElementById("dia").value;
        document.getElementById("rolId").value =document.getElementById("tipoAcademico").value;
        if (confirm("¿Estás seguro de guardar esta porción de horario?"))
            document.getElementById("guardar-horario").submit();
    }
    let numFilas = document.getElementById('cuerpo-tabla').rows.length;
    
    function vistaGrupo(id) {
        location.href = "/grupo/" + id;
    }
    function añadirHorario(){
        $("#cuerpo-tabla").append( `<tr id="`+numFilas+`">
            <td class="border border-dark">
                
                <select name ="dia" id= "dia"> 
                    <option value="LUNES">LUNES</option>
                    <option value="MARTES">MARTES</option>
                    <option value="MIERCOLES">MIERCOLES</option>
                    <option value="JUEVES">JUEVES</option>
                    <option value="VIERNES">VIERNES</option>
                    <option value="SABADO">SABADO</option>
                    </select>
                    <input type="time" name="hora_inicio" id="horaInicio" onchange="setHoraFin()" required>
                    <input type="time" name="hora_fin" id="horaFin" disabled>
                    <input type="number" id="periodo" min="1" max="12" value="1" onchange="setHoraFin()">
                    </td>
                    <td class="border border-dark">
                        <select id="tipoAcademico" name="rol_id">
                            <option value="3">DOCENCIA</option>
                            <option value="2">AUXILIATURA</option>
                            </select>
                            </td>
                            <td class="border border-dark">
                                <input width="30rem" height="30rem" type="image" src="/icons/aceptar.png" alt="Aceptar" onclick="confirmarGuardarHorario()">
                                
                                <input width="30rem" height="30rem" type="image" name="botonCancelar" src="/icons/cancelar.png" alt="Cancelar" onclick = "cancelarFila(`+numFilas+`)">
                                </td>
                                </tr>`);
                                numFilas++;
                            }
    function cancelarFila(id) {

        fila = document.getElementById(id);
        padre = fila.parentNode;
        padre.removeChild(fila);
    }
    function setHoraFin(horarioId = ""){
        var timeInicio = document.getElementById("horaInicio" + horarioId).value;
        var periodo = 45;
        var numPeriodos = parseInt(document.getElementById("periodo"+horarioId).value);
        var splitTimeInicio = timeInicio.split(":");
        var horaFin = parseInt(splitTimeInicio[0]);
        var minutosFin = parseInt(splitTimeInicio[1]);

        if( timeInicio != ""){
            for(var i = numPeriodos; i > 0; i--){
                if(minutosFin+periodo>=60){
                    minutosFin = minutosFin+periodo-60;
                    horaFin = horaFin+1;
                }
                else{                    
                    minutosFin = minutosFin+periodo;
                }
            }
            if(horaFin<10){                    
                horaFin= "0"+horaFin;
            }
            if(minutosFin<10){    
                minutosFin = "0"+minutosFin;           
            }            
            document.getElementById("horaFin"+horarioId).value = horaFin + ":" +minutosFin;
        }
    }

    function desactivar()
    {
        @foreach ($horarios as $key => $horario)
            eliminar = document.getElementById("botonEliminar" + {{$horario->id}});
            eliminar.disabled = true;
            eliminar.src="/icons/eliminarDis.png";
            
            editar = document.getElementById("botonEditar" + {{$horario->id}});
            editar.disabled = true;
            editar.src="/icons/editarDis.png";
        @endforeach
        
        document.getElementById("añadirHorario").disabled = true;

        nuevo = document.getElementById("asignarDocente");
        if(nuevo)
            nuevo.disabled = true
        nuevo = document.getElementById("asignarAuxiliar");
        if(nuevo)
            nuevo.disabled = true

        eliminar = document.getElementById("desasignarDocente");
        if(eliminar)
        {
            eliminar.disabled = true;
            eliminar.src="/icons/eliminarDis.png";
        }
        
        eliminar = document.getElementById("desasignarAuxiliar");
        if(eliminar)
        {
            eliminar.disabled = true;
            eliminar.src="/icons/eliminarDis.png";
        }
    }

    function activar()
    {
        @foreach ($horarios as $key => $horario)
            eliminar = document.getElementById("botonEliminar" + {{$horario->id}});
            eliminar.disabled = false;
            eliminar.src="/icons/eliminar.png";
            
            editar = document.getElementById("botonEditar" + {{$horario->id}});
            editar.disabled = false;
            editar.src="/icons/editar.png";
        @endforeach
        
        document.getElementById("añadirHorario").disabled = false;

        nuevo = document.getElementById("asignarDocente");
        if(nuevo)
            nuevo.disabled = false;
        nuevo = document.getElementById("asignarAuxiliar");
        if(nuevo)
            nuevo.disabled = false;

        eliminar = document.getElementById("desasignarDocente");
        if(eliminar)
        {
            eliminar.disabled = false;
            eliminar.src="/icons/eliminar.png";
        }
        
        eliminar = document.getElementById("desasignarAuxiliar");
        if(eliminar)
        {
            eliminar.disabled = false;
            eliminar.src="/icons/eliminar.png";
        }
    }
</script>
<!-- jQuery and JS bundle w/ Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/main.js"></script>
</html>
