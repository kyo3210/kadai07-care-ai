<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;
use App\Models\User; // 追加
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // 追加

class OfficeController extends Controller
{
    public function index()
    {
        return response()->json(Office::all());
    }

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

    // --- 追加：所属職員の一覧取得 ---
    public function indexStaff()
    {
        // ログインユーザーと同じ事業所に属する職員のみ取得
        $officeId = Auth::user()->office_id;
        $staff = User::where('office_id', $officeId)->get();
        return response()->json($staff);
    }

    // --- 追加：職員の新規登録（紐付け含む） ---
public function storeStaff(Request $request)
{
    $validated = $request->validate([
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users,email',
        'password'  => 'required|string|min:8',
        'office_id' => 'required|exists:offices,id', // 必須にする
    ]);

    User::create([
        'name'      => $validated['name'],
        'email'     => $validated['email'],
        'password'  => Hash::make($validated['password']),
        'office_id' => $validated['office_id'], // 送られてきたIDをセット
    ]);

    return response()->json(['status' => 'success', 'message' => '職員を登録し、事業所に紐付けました']);
}
}