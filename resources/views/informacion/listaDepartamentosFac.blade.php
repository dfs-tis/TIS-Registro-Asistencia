@extends('layouts.master')

@section('title', 'Lista de Departamentos')

@section('css')
    <link rel="stylesheet" href="/css/estiloListaMaterias.css">
@endsection

@section('content')
    <div class="container">
        <div class="mx-3 my-4">
                <h3 class="textoBlanco">FACULTAD: {{ $facultad->nombre }}</h3>
                <h3 class="textoBlanco">DECANO: {{ $facultad->decano->nombre() }} </h3>
                <h3 class="textoBlanco">DIRECTOR ACADEMICO: {{$facultad->directorAcademico->nombre()}}  </h3>  
                <h3 class="textoBlanco">ENCARGADO FACULTATIVO: {{$facultad->encargado->nombre()}}  </h3>  
            <div class="container mt-4">
                <table class="table ">
                        <tr>                            
                            <th class="textoBlanco border border-dark" scope="col">DEPARTAMENTO</th>
                            <th class="textoBlanco border border-dark" scope="col">ULTIMO PARTE MENSUAL</th>
                            <th class="textoBlanco border border-dark" scope="col">APROBADO</th>
                        </tr>
                        @forelse ($departamentos as $departamento)
                            <tr class="">
                                <td class="border border-dark align-middle"><a
                                    href="/departamento/{{ $departamento->id }}">{{ $departamento->nombre }}</a></td>
                                <td class="border border-dark align-middle"><strong>{{$departamento->mes}} </strong> <a href="" class="col">Ver parte docentes</a> <a href="" class="col">Ver parte auxiliares</a></td>
                                <td class="border border-dark ">
                                    <label for="{{$departamento->id}}encargadoFac">Encargado facultativo</label>
                                    <input id="{{$departamento->id}}encargadoFac"type="checkbox"><br>
                                    <label for="{{$departamento->id}}dirAcademico">Director academico</label>
                                    <input id="{{$departamento->id}}dirAcademico"type="checkbox"><br>
                                    <label for="{{$departamento->id}}decano">Decano</label>
                                    <input id="{{$departamento->id}}decano"type="checkbox"><br>
                                    <label for="{{$departamento->id}}jefeDept">Jefe de departamento</label>
                                    <input id="{{$departamento->id}}jefeDept"type="checkbox">
                                </td>
                            </tr>
                        @empty
                            <h3 class="textoBlanco">ESTA FACULTAD NO TIENE REGISTRADO NINGUN DEPARTAMENTO</h3>
                        @endforelse
                    </table>
                    <div class="mt-3">
                        {{ $departamentos->links() }}
                    </div>
                <button class="boton btn btn-success textoNegro">ENVIAR A DPA</button>
            </div>
        </div>
    </div>
@endsection
@section('script-footer')
 <script>
     var depts=@json($departamentos).data;
     depts.forEach(dept => {
        document.getElementById(dept.id+'encargadoFac').checked = dept.encargado_fac;
        document.getElementById(dept.id+'encargadoFac').disabled = true;
        document.getElementById(dept.id+'dirAcademico').checked = dept.dir_academico;
        document.getElementById(dept.id+'dirAcademico').disabled = true;
        document.getElementById(dept.id+'decano').checked = dept.decano;
        document.getElementById(dept.id+'decano').disabled = true;
        document.getElementById(dept.id+'jefeDept').checked = dept.jefe_dept;
        document.getElementById(dept.id+'jefeDept').disabled = true;
     });
 </script>
@endsection
