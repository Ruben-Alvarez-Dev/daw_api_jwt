<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::with('zone.restaurant')->get();
        return response()->json($tables);
    }

    public function store(Request $request)
    {
        $request->validate([
            'table_zone_id' => 'required|exists:zones,zone_id',
            'table_capacity' => 'required|integer|min:1',
            'table_name' => 'required|string|max:255'
        ]);

        $table = Table::create($request->all());
        return response()->json($table, 201);
    }

    public function show(Table $table)
    {
        return response()->json($table->load('zone.restaurant'));
    }

    public function update(Request $request, Table $table)
    {
        $request->validate([
            'table_zone_id' => 'sometimes|exists:zones,zone_id',
            'table_capacity' => 'sometimes|integer|min:1',
            'table_name' => 'sometimes|string|max:255'
        ]);

        $table->update($request->all());
        return response()->json($table);
    }

    public function destroy(Table $table)
    {
        $table->delete();
        return response()->json(null, 204);
    }
}
