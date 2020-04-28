<?php

namespace App\Http\Controllers\Measurement;

use App\Http\Controllers\ApiController;
use App\Measure;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MeasureController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $measures = Measure::where('user_id', Auth::id())->get();
        return $this->showAll($measures);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $reglas = [
            'peso' => 'required|numeric|between:0,199.9',
            'estatura' => 'required|numeric|between:0,230.9',
            'cadera' => 'required|numeric|between:0,230.9',
            'cintura' => 'required|numeric|between:0,230.9',
            'pecho' => 'required|numeric|between:0,230.9',
            'brazo' => 'required|numeric|between:0,230.9',
            'pierna' => 'required|numeric|between:0,230.9',
            'cuello' => 'required|numeric|between:0,230.9',
        ];

        //Validamos los datos del request 
        $this->validate($request, $reglas);

        $fields = $request->all();
        $fields['user_id'] = Auth::id();
        $newMeasure = Measure::create($fields);
        return $this->showOne($newMeasure);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Measure $measure)
    {
        // TODO 
        // Policies
        return $this->ShowOne($measure);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Measure $measure)
    {
        $measure->fill($request->only(['peso', 'estatura', 'cintura', 'cadera', 'pecho', 'brazo', 'pierna', 'cuello']));
        if ($measure->isDirty()) {
            $measure->save();
            return $this->successResponse("Registro actualizado", 201);
        } else {
            return $this->errorResponse("No se ha modificado ningún atributo", 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($measure)
    {
        // TODO policy
        $measure->delete();
        return $this->successResponse("Registro eliminado correctamente", 200);
    }
}
