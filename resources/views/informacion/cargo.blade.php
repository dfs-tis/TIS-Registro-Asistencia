@extends('layouts.master')

@section('title', 'Cargo de Laboratorio')

@section('content')
    <div class="mx-3 my-4">
        <div class="container">
            <div class="row">
                <div class="col-8">
                    <h4 class="textoBlanco">{{ $cargo->unidad->facultad->nombre }}</h4>
                    <h1 class="textoBlanco">{{ $cargo->unidad->nombre }}</h1>
                    <br>
                    <h5 class="textoBlanco">{{ $cargo->nombre }}</h5>
                </div>
                <div class="col-4">
                    <h4 class="textoBlanco">Codigo de cargo de laboratorio: {{$cargo->id}}</h4>
                </div>
            </div>
        <ul class="list-group">
            @forelse ($items as $item)
                <li class="list-group-item"><a href="/item/{{$item->id}}">{{$item->nombre}}</a></li>
            @empty
                <h3 class="textoBlanco">Este cargo no tiene items asignados</h3>
            @endforelse
        </ul>
        <button type="button" class="btn boton my-3" >AÑADIR ITEM<svg width="2em" height="2em" viewBox="0 0 16 16" class="bi bi-plus-circle" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path fill-rule="evenodd" d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
        </svg></button>
    </div>
@endsection
