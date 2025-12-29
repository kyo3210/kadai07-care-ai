<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;

class OfficeController extends Controller
{
    // 全事業所のリストを取得 (改善①用)
    public function index()
    {
        return response()->json(Office::all());
    }

    // 特定の事業所情報を更新 (改善②用)
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id'       => 'required|exists:offices,id',
            'name'     => 'required|string|max:255',
            'postcode' => 'required|string|max:7',
            'address'  => 'required|string|max:255',
            'tel'      => 'required|string|max:20',
        ]);

        $office = Office::find($validated['id']);
        $office->update($validated);

        return response()->json(['status' => 'success', 'message' => '事業所情報を更新しました']);
    }
}