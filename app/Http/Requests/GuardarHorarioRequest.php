<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarHorarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'unidad_id' => 'required',
            'materia_id' => 'required',
            'grupo_id' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'dia' => 'required',
            'rol_id' => 'required'
        ];
    }
}