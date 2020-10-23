<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'public.Materia';

    public function unidad() {
        return $this->belongsTo('App\Unidad');
    }
}