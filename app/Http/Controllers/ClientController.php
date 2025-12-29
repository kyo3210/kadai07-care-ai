<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * 利用者一覧の取得
     * 500エラーを回避するため、詳細な例外処理を組み込んでいます。
     */
    public function index()
    {
        try {
            // 現在のログインユーザーを取得
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => '認証されていません。再ログインしてください。'], 401);
            }

            // 全利用者を最新順に取得
            // ※ 特定の事業所のみに絞り込む場合は ->where('office_id', $user->office_id) を追加
            $clients = Client::orderBy('created_at', 'desc')->get();

            return response()->json($clients);

        } catch (\Exception $e) {
            // エラーが発生した場合、laravel.logに詳細を書き出し、500エラーを返します
            Log::error('ClientController@index Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'サーバーエラーが発生しました。',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 利用者の新規登録
     * 追加された詳細項目（被保険者番号、認定期間など）の保存に対応
     */
    public function store(Request $request)
    {
        try {
            // 入力値のバリデーション（チェック）
            // マイグレーションで追加した新しいカラム名と一致させています
            $validated = $request->validate([
                'id'               => 'required|string|unique:clients,id',
                'client_name'      => 'required|string|max:255',
                'postcode'         => 'nullable|string|max:7',
                'address'          => 'required|string|max:255',
                'contact_tel'      => 'required|string|max:20',
                'insurace_number'  => 'nullable|string|max:255', // 被保険者番号
                'care_start_date'  => 'nullable|date',           // 認定開始日
                'care_end_date'    => 'nullable|date',           // 認定終了日
                'care_manager'     => 'required|string|max:255',
                'care_manager_tel' => 'nullable|string|max:20',  // ケアマネ連絡先
            ]);

            // ログインユーザーの事業所IDを自動セット
            $validated['office_id'] = Auth::user()->office_id;

            // データベースへ保存
            $client = Client::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => '利用者を登録しました。',
                'data' => $client
            ]);

        } catch (\Illuminate\Validation\ValidationException $v) {
            // バリデーションエラー（ID重複など）
            return response()->json([
                'status' => 'error',
                'message' => '入力内容に不備があります。',
                'errors' => $v->errors()
            ], 422);

        } catch (\Exception $e) {
            // その他の予期せぬエラー
            Log::error('ClientController@store Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => '登録中にエラーが発生しました。',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}