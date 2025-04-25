<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public $marca;
    public function __construct(Marca $marca)
    {
        $this->marca = $marca;
    }
    /*
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $marcas = array();
        
        if($request->has('atributos_modelos')){
            $atributos_modelos = $request->atributos_modelos;
            $marcas = $this->marca->with('modelos:id,'.$atributos_modelos);
        }else{
            $marcas = $this->marca->with('modelos');
        }
        
        if ($request->has('filtro')) {
            $filtros = explode(';', $request->filtro);
            foreach ($filtros as $key => $condicao) {
                $c = explode(':', $request->filtro);
                $marcas = $marcas->where($c[0], $c[1], $c[2] );
            }
        }
        if($request->has('atributos')){
            $atributos = $request->atributos;
            $marcas = $marcas->selectRaw($atributos)->get();
        }else{
            $marcas = $marcas->get();
        }
       
        //$marcas = $this->marca->with('modelos')->get();
        return response()->json($marcas, 200);
    }
    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->marca->rules(), $this->marca->feedback());
        //stateless
        $image = $request->file('imagem');
        $imagem_urn = $image->store('imagens', 'public');
        
        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $imagem_urn   
        ]);
        return response()->json($marca, 201);
    }

    /**
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);
        if($marca === null){
            return response()->json(['Erro' => 'Recurso Pesquisado não Existe'], 404);
        }
        return response()->json($marca, 200);
    }
    /*
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $marca = $this->marca->find($id);
        if($marca === null){
            return response()->json(['Erro' => 'Recurso solicidado não Existe'], 404);
        }
        //percorre todas a regras aplicadas pelo model
        if($request->method() === 'PATCH'){
            $regrasDinamicas = array();

            foreach ($marca->rules() as $input => $regras) {
                //coletar regras aplicaveis
                if (array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regras; 
                }

            }
            $request->validate($regrasDinamicas, $marca->feedback());
        }else{
            $request->validate($marca->rules(), $marca->feedback());
        }
        //remove o arquivo antigo caso um novo arquivo seja enviado no request
        if($request->file('imagem')){
            Storage::disk('public')->delete($marca->imagem);
        }

        $image = $request->file('imagem');
        $imagem_urn = $image->store('imagens', 'public');

        //request sobrepoe campos marcas 
        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;

        $marca->save();
/*
        $marca->update([
            'nome' => $request->nome,
            'imagem' => $imagem_urn   
        ]);*/
        return response()->json($marca, 200);
    }
    /*
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $marca = $this->marca->find($id);
        if($marca === null){
            return response()->json(['Erro' => 'Impossível realizar a exclusão. Recurso solicidado não Existe'], 404);
        }
        //remove o arquivo antigo caso um novo arquivo seja enviado no request
        Storage::disk('public')->delete($marca->imagem);
       
        $marca->delete();
        return response()->json('A marca foi removida com sucesso!', 200);
    }
}
