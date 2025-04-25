<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Modelo;
use Illuminate\Http\Request;

class ModeloController extends Controller
{
    public $modelo;
    public function __construct(Modelo $modelo){
        $this->modelo = $modelo;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $modelos= array();

        if($request->has('atributos_marca')){
            $atributos_marca = $request->atributos_marca;
            $modelos = $this->modelo->with('marca:id,'.$atributos_marca);
        }else{
            $modelos = $this->modelo->with('marca');
        }

        if ($request->has('filtro')) {

            $filtros = explode(';', $request->filtro);
            foreach ($filtros as $key => $condicao) {
                $c = explode(':', $request->filtro);
                $modelos = $modelos->where($c[0], $c[1], $c[2] );
            }
            
        }

        if($request->has('atributos')){
            $atributos = $request->atributos;
            $modelos = $this->modelo->selectRaw($atributos)->get();
        }else{
            $modelos = $this->modelo->get();
        }
        return response()->json($modelos, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->modelo->rules());
        //stateless
        $image = $request->file('imagem');
        $imagem_urn = $image->store('imagens/modelos', 'public');
        
        $modelo = $this->modelo->create([
            'marca_id' => $request->marca_id,
            'nome' => $request-> nome,
            'imagem' => $imagem_urn ,
            'numero_portas' => $request->numero_portas ,
            'lugares' => $request->lugares ,
            'air_bag' => $request->air_bag ,
            'abs'  => $request->abs         
        ]);
        return response()->json($modelo, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {                            //relacionamento com marca   
        $modelo = $this->modelo->with('marca')->find($id);
        if($modelo === null){
            return response()->json(['Erro' => 'Recurso Pesquisado não Existe'], 404);
        }
        return response()->json($modelo, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function edit(Modelo $modelo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $modelo = $this->modelo->find($id);
        if($modelo === null){
            return response()->json(['Erro' => 'Recurso solicidado não Existe'], 404);
        }
        //percorre todas a regras aplicadas pelo model
        if($request->method() === 'PATCH'){
            $regrasDinamicas = array();

            foreach ($modelo->rules() as $input => $regras) {
                //coletar regras aplicaveis
                if (array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regras; 
                }

            }
            $request->validate($regrasDinamicas);
        }else{
            $request->validate($modelo->rules());
        }
        //remove o arquivo antigo caso um novo arquivo seja enviado no request
        if($request->file('imagem')){
            Storage::disk('public')->delete($modelo->imagem);
        }

        $image = $request->file('imagem');
        $imagem_urn = $image->store('imagens/modelos', 'public');

         //request sobrepoe campos marcas 
         $modelo->fill($request->all());
         $modelo->imagem = $imagem_urn;
 
         $modelo->save();
/*
        $modelo->update([
            'marca_id' => $request->marca_id,
            'nome' => $request-> nome,
            'imagem' => $imagem_urn ,
            'numero_portas' => $request->numero_portas ,
            'lugares' => $request->lugares ,
            'air_bag' => $request->air_bag ,
            'abs'  => $request->abs      
        ]);
 */       
        return response()->json($modelo, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $modelo = $this->modelo->find($id);
        if($modelo === null){
            return response()->json(['Erro' => 'Impossível realizar a exclusão. Recurso solicidado não Existe'], 404);
        }
        //remove o arquivo antigo caso um novo arquivo seja enviado no request
        Storage::disk('public')->delete($modelo->imagem);
       
        $modelo->delete();
        return response()->json('A modelo foi removida com sucesso!', 200);
    }
}
