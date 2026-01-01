<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-name" content="{{ Auth::user()->name ?? '担当スタッフ' }}">
    <title>ケアマネ業務支援システム</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* スライダースイッチの装飾 */
        .switch { position: relative; display: inline-block; width: 44px; height: 22px; }
        #toggle-bg { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 22px; }
        #toggle-circle { position: absolute; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        
        /* モーダル基本設定 */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 20px; width: 80%; max-height: 80%; overflow-y: auto; border-radius: 8px; }
    </style>
</head>
<body style="background: #f4f7f6; font-family: 'Helvetica Neue', Arial, sans-serif; color: #333; margin: 0; padding: 20px;">

    <header style="margin-bottom: 20px; border-bottom: 2px solid #0056b3; padding-bottom: 10px;">
        <h1 style="color: #0056b3; margin: 0;">CareSupport AI Pro</h1>
    </header>

    <main style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <section id="client-register-section" style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="font-size: 1.1em; margin: 0;">👤 利用者 登録/編集</h2>
                    <button type="button" id="open-client-modal" style="background: #6c757d; color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">利用者一覧から選択</button>
                </div>

                <form id="client-register-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="grid-column: span 2; border-bottom: 1px solid #eee; padding-bottom: 5px; font-weight: bold; color: #0056b3;">基本情報</div>
                        <div>
                            <label style="font-size: 0.85em;">利用者ID (新規は空欄)</label>
                            <input type="text" id="reg-client-id" placeholder="自動採番" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85em;">利用者氏名</label>
                            <input type="text" id="reg-client-name" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85em;">郵便番号</label>
                            <div style="display: flex; gap: 5px;">
                                <input type="text" id="reg-zipcode" placeholder="1234567" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <button type="button" id="search-zipcode" style="background: #f8f9fa; border: 1px solid #ccc; padding: 0 10px; border-radius: 4px; cursor: pointer; white-space: nowrap;">検索</button>
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 0.85em;">連絡先電話番号</label>
                            <input type="text" id="reg-contact-tel" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="font-size: 0.85em;">住所</label>
                            <input type="text" id="reg-address" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>

                        <div style="grid-column: span 2; border-bottom: 1px solid #eee; padding-bottom: 5px; font-weight: bold; color: #0056b3; margin-top: 10px;">介護・保険情報</div>
                        <div>
                            <label style="font-size: 0.85em;">介護保険番号</label>
                            <input type="text" id="reg-insurance" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85em;">ケアマネジャー名</label>
                            <input type="text" id="reg-care-manager" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85em;">認定有効開始日</label>
                            <input type="date" id="reg-start-date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85em;">認定有効終了日</label>
                            <input type="date" id="reg-end-date" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85em;">ケアマネ連絡先</label>
                            <input type="text" id="reg-care-manager-tel" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                        </div>
                    </div>

                    <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" id="client-delete-btn" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; display: none;">削除する</button>
                        <button type="button" id="form-reset-btn" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">クリア</button>
                        <button type="submit" id="client-submit-btn" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">情報を保存する</button>
                    </div>
                </form>
            </section>

            <section style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="font-size: 1.1em; margin: 0;">📝 ケア記録・バイタル入力</h2>
                    <button type="button" id="open-record-modal" style="background: #6c757d; color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">過去の記録を編集</button>
                </div>
                <form id="record-add-form">
                    <input type="hidden" id="edit-record-id">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <select id="record-client-select" required style="grid-column: span 2; padding: 8px; border-radius: 4px; border: 1px solid #ccc;"></select>
                        <input type="date" id="record-date" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                        <input type="time" id="record-time" required style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                        <input type="number" id="record-temp" step="0.1" placeholder="体温 ℃" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                        <input type="number" id="record-water" placeholder="水分 ml" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                        <input type="number" id="record-bp-high" placeholder="血圧(上)" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                        <input type="number" id="record-bp-low" placeholder="血圧(下)" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                        <textarea id="record-content" placeholder="ケア内容・特記事項を入力してください" style="grid-column: span 2; height: 80px; padding: 8px; border-radius: 4px; border: 1px solid #ccc;"></textarea>
                    </div>
                    <div style="margin-top: 10px; display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" id="record-reset-btn" style="display: none; background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px;">新規作成へ戻る</button>
                        <button type="submit" id="record-submit-btn" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">記録を保存</button>
                    </div>
                </form>
            </section>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <section style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd;">
                <h2 style="font-size: 1.1em; margin-bottom: 10px;">💬 AIチャット相談</h2>

                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; font-size: 0.85em; background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #eee;">
                    <span style="font-weight: bold; color: #555;">🔊 AI回答の音声読み上げ</span>
                    <label class="switch">
                        <input type="checkbox" id="voice-read-toggle" style="opacity: 0; width: 0; height: 0;">
                        <div id="toggle-bg">
                            <div id="toggle-circle"></div>
                        </div>
                    </label>
                </div>

                <select id="client-select" style="width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #ccc;">
                    <option value="">利用者を選択してください (一般相談モード)</option>
                </select>
                
                <div id="chat-window" style="height: 300px; overflow-y: auto; background: #fafafa; border: 1px solid #eee; padding: 15px; margin-bottom: 10px; border-radius: 6px;"></div>
                
                <form id="chat-form" style="display: flex; gap: 8px;">
                    <button type="button" id="voice-input-btn" style="background: #007bff; color: white; border: none; padding: 0 12px; border-radius: 6px; cursor: pointer;">🎤</button>
                    <input type="text" id="user-input" placeholder="ベテラン主任に相談..." required style="flex-grow: 1; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                    <button type="submit" style="background: #28a745; color: white; border: none; padding: 0 20px; border-radius: 6px; cursor: pointer;">送信</button>
                    <button type="button" id="chat-clear-btn" style="background: #dc3545; color: white; border: none; padding: 0 12px; border-radius: 6px; cursor: pointer;">クリア</button>
                </form>
            </section>

            <section style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd;">
                <h2 style="font-size: 1.1em; margin-top: 0;">📊 バイタル分析</h2>
                <div style="margin-bottom: 10px; display: flex; gap: 5px;">
                    <button type="button" class="quick-date-btn" data-range="week" style="font-size: 0.75em; padding: 4px 8px; border-radius: 4px; border: 1px solid #ccc; background: #fff; cursor: pointer;">直近1週間</button>
                    <button type="button" class="quick-date-btn" data-range="month" style="font-size: 0.75em; padding: 4px 8px; border-radius: 4px; border: 1px solid #ccc; background: #fff; cursor: pointer;">今月</button>
                </div>
                <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 5px;">
                    <input type="date" id="search-start-date" style="width: 38%; padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
                    <span>〜</span>
                    <input type="date" id="search-end-date" style="width: 38%; padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
                    <button type="button" id="update-graph-btn" style="background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;">表示</button>
                </div>
                <canvas id="vitalChart" style="max-height: 250px;"></canvas>
            </section>
        </div>
    </main>

    <div id="client-modal" class="modal">
        <div class="modal-content">
            <h3>利用者一覧</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">ID</th>
                        <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">氏名</th>
                        <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">住所</th>
                        <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">操作</th>
                    </tr>
                </thead>
                <tbody id="modal-client-table-body"></tbody>
            </table>
            <button id="close-client-modal" style="margin-top: 15px; padding: 8px 16px;">閉じる</button>
        </div>
    </div>

    <div id="record-modal" class="modal">
        <div class="modal-content">
            <h3>ケア記録一覧 (直近100件)</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">日時</th>
                        <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">利用者</th>
                        <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">内容</th>
                        <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">バイタル</th>
                        <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">操作</th>
                    </tr>
                </thead>
                <tbody id="modal-record-table-body"></tbody>
            </table>
            <button id="close-record-modal" style="margin-top: 15px; padding: 8px 16px;">閉じる</button>
        </div>
    </div>

    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>