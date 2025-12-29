<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\CareRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CareController extends Controller
{
    /**
     * 利用者検索
     */
    public function search(Request $request)
    {
        $query = $request->query('query');
        $client = Client::where('client_name', 'LIKE', "%{$query}%")
            ->orWhere('id', $query)
            ->first();

        if ($client) {
            return response()->json(['status' => 'success', 'client' => $client]);
        }
        return response()->json(['status' => 'error', 'message' => '利用者が存在しません'], 404);
    }

    /**
     * モーダル表示用の全記録取得
     */
    public function getAllRecords()
    {
        try {
            return CareRecord::orderBy('recorded_at', 'desc')->take(100)->get();
        } catch (\Exception $e) {
            Log::error('全記録取得エラー: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ケア記録の保存（新規作成・上書き更新 両用）
     */
    public function storeRecord(Request $request)
    {
        try {
            $validated = $request->validate([
                'edit_record_id'      => 'nullable|exists:care_records,id',
                'client_id'           => 'required|exists:clients,id',
                'date'                => 'required|date',
                'time'                => 'required',
                'content'             => 'required|string',
                'body_temp'           => 'nullable|numeric',
                'blood_pressure_high' => 'nullable|integer',
                'blood_pressure_low'  => 'nullable|integer',
                'water_intake'        => 'nullable|integer',
                'recorded_by'         => 'required|string',
            ]);

            $recordData = [
                'client_id'           => $request->client_id,
                'content'             => $request->content,
                'body_temp'           => $request->body_temp,
                'blood_pressure_high' => $request->blood_pressure_high,
                'blood_pressure_low'  => $request->blood_pressure_low,
                'water_intake'        => $request->water_intake,
                'recorded_by'         => $request->recorded_by,
                'recorded_at'         => $request->date . ' ' . $request->time . ':00',
            ];

            // edit_record_id があれば更新、なければ新規作成
            $record = CareRecord::updateOrCreate(
                ['id' => $request->edit_record_id],
                $recordData
            );

            return response()->json([
                'status' => 'success',
                'message' => '保存が完了しました。',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            Log::error('ケア記録保存エラー: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'サーバーエラーが発生しました。',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * AI相談（Gemini連携）
     */
    public function askAI(Request $request)
    {
        try {
            $clientId = $request->input('clientId');
            $userQuestion = $request->input('question');
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');
            $systemPrompt = $request->input('systemPrompt');

            $query = CareRecord::where('client_id', $clientId);
            if ($startDate) $query->where('recorded_at', '>=', $startDate . ' 00:00:00');
            if ($endDate) $query->where('recorded_at', '<=', $endDate . ' 23:59:59');
            
            $records = $query->orderBy('recorded_at', 'asc')->get();

            $context = "以下は対象利用者のケア記録データです：\n";
            $vitalData = [];

            foreach ($records as $r) {
                $context .= "- 日時:{$r->recorded_at}: {$r->content} (体温:{$r->body_temp}℃, 血圧:{$r->blood_pressure_high}/{$r->blood_pressure_low}, 水分:{$r->water_intake}ml)\n";
                
                if ($r->body_temp) {
                    $vitalData[] = [
                        'date' => date('Y-m-d', strtotime($r->recorded_at)),
                        'temp' => (float)$r->body_temp,
                        'bp_high' => (int)$r->blood_pressure_high,
                        'bp_low' => (int)$r->blood_pressure_low 
                    ];
                }
            }

            $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
            
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [['text' => "{$systemPrompt}\n\n背景データ:\n{$context}\n\n質問: {$userQuestion}"]]
                        ]
                    ]
                ]);

            $result = $response->json();
            $answer = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'AIからの回答を取得できませんでした。';

            return response()->json([
                'answer' => $answer,
                'vitalData' => $vitalData
            ]);

        } catch (\Exception $e) {
            Log::error('AI相談エラー: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}