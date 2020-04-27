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

        $campos = $request->all();
        $campos['user_id'] = Auth::id();
        $newMeasure = Measure::create($campos);
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
    public function update(Request $request, $measure)
    {
        //
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
