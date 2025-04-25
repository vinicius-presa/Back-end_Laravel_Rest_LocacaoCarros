<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository{

    public $model;

    public function __construct(Model $model){
       $this->model = $model;
    }

    public function selectAtributosRegistrosSelecionados($atributos){
        
        
        $this->model = $this->model->with($atributos);
    }

    public function filtro($filtros){
        $filtros = explode(';', $filtros);
            foreach ($filtros as $key => $condicao) {
                $c = explode(':', $condicao);
                $this->model = $this->model->where($c[0], $c[1], $c[2] );
            }
    }

    public function selectAtributos($atributos){
        //dd($atributos);
        $this->model = $this->model->selectRaw($atributos);
    }

    public function getResutado(){
        return $this->model->get();
    }



}
