<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    use HasFactory;
    protected $fillable = ['marca_id','nome', 'imagem', 'numero_portas' ,'lugares', 'air_bag', 'abs'];

    public function rules() {
        return [
            'marca_id' => 'exists:marcas,id',
            'nome' => 'required|unique:modelos,nome,'.$this->id.'|min:3',
            'imagem' => 'required|file|mimes:png',
            'numero_portas' => 'required|integer|digits_between:1,5',
            'lugares' => 'required|integer|digits_between:1,10', 
            'air_bag' => 'required|boolean', 
            'abs'  => 'required|boolean'  // true , false, 1, 2, "1", "2"
        ];
    }

    public function feedback(){
       /* return [
            'required' => 'O campo :attribute é obrigatorio',
            'imagem.mines' => 'O arquivo tem que ser PNG',
            'nome.unique' => 'O nome da marca ja existe',
            'nome.min' => 'O nome deve ter no minimo 3 caracteres'
        ];*/
    }

    public function marca(){
        //um modelo pertence a uma marca
        return $this->belongsTo('App\Models\Marca');
    }


}
