<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Obtener todas las categorías activas
     */
    public function index()
    {
        try {
            $categorias = Categoria::activas()
                ->orderBy('nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categorias
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías'
            ], 500);
        }
    }
}
