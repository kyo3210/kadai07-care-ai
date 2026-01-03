<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-name" content="{{ Auth::user()->name ?? '担当スタッフ' }}">
    <title>CareSupport AI</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .switch { position: relative; display: inline-block; width: 44px; height: 22px; }
        #toggle-bg { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 22px; }
        #toggle-circle { position: absolute; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 20px; width: 80%; max-height: 80%; overflow-y: auto; border-radius: 8px; }
        label { font-size: 0.85em; font-weight: bold; color: #555; display: block; margin-bottom: 3px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        section { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd; margin-bottom: 20px; }
    </style>
</head>
<body style="background: #f4f7f6; font-family: 'Helvetica Neue', Arial, sans-serif; color: #333; margin: 0; padding: 20px;">

    <header style="margin-bottom: 20px; border-bottom: 2px solid #0056b3; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="color: #0056b3; margin: 0;">CareSupport AI</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span style="font-weight: bold; color: #555; font-size: 0.95em;">ログイン👤: {{ Auth::user()->name }} </span>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background: #6c757d; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85em;">ログアウト</button>
            </form>
        </div>
    </header>

    <main style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

        <div style="display: flex; flex-direction: column;">
            
            <section id="client-register-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="font-size: 1.1em; margin: 0;">👤 利用者 登録/編集</h2>
                    <button type="button" id="open-client-modal" style="background: #6c757d; color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">一覧から選択</button>
                </div>

                <form id="client-register-form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div style="grid-column: span 2; border-bottom: 1px solid #eee; padding-bottom: 5px; font-weight: bold; color: #0056b3;">基本情報</div>
                        <div>
                            <label>利用者ID (新規は空欄)</label>
                            <input type="text" id="reg-client-id" placeholder="自動採番">
                        </div>
                        <div>
                            <label>利用者氏名</label>
                            <input type="text" id="reg-client-name" required>
                        </div>
                        <div>
                            <label>郵便番号</label>
                            <div style="display: flex; gap: 5px;">
                                <input type="text" id="reg-zipcode" placeholder="1234567">
                                <button type="button" id="search-zipcode" style="background: #f8f9fa; border: 1px solid #ccc; padding: 0 10px; border-radius: 4px; cursor: pointer; white-space: nowrap;">検索</button>
                            </div>
                        </div>
                        <div>
                            <label>連絡先電話番号</label>
                            <input type="text" id="reg-contact-tel">
                        </div>
                        <div style="grid-column: span 2;">
                            <label>住所</label>
                            <input type="text" id="reg-address">
                        </div>
                        <div style="grid-column: span 2; border-bottom: 1px solid #eee; padding-bottom: 5px; font-weight: bold; color: #0056b3; margin-top: 10px;">介護・保険情報</div>
                        <div><label>介護保険番号</label><input type="text" id="reg-insurance"></div>
                        <div><label>ケアマネジャー名</label><input type="text" id="reg-care-manager"></div>
                        <div><label>認定有効開始日</label><input type="date" id="reg-start-date"></div>
                        <div><label>認定有効終了日</label><input type="date" id="reg-end-date"></div>
                        <div><label>ケアマネ連絡先</label><input type="text" id="reg-care-manager-tel"></div>
                    </div>
                    <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" id="client-delete-btn" style="background: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; display: none;">削除する</button>
                        <button type="button" id="form-reset-btn" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">クリア</button>
                        <button type="submit" id="client-submit-btn" style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">情報を保存する</button>
                    </div>
                </form>
            </section>

            <section id="record-register-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h2 style="font-size: 1.1em; margin: 0;">📝 ケア記録・バイタル入力</h2>
                    <button type="button" id="open-record-modal" style="background: #6c757d; color: white; border: none; padding: 5px 15px; border-radius: 4px; cursor: pointer;">過去記録編集</button>
                </div>
                <form id="record-add-form">
                    <input type="hidden" id="edit-record-id">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <select id="record-client-select" required style="grid-column: span 2;"></select>
                        <input type="date" id="record-date" required>
                        <input type="time" id="record-time" required>
                        <input type="number" id="record-temp" step="0.1" placeholder="体温 ℃">
                        <input type="number" id="record-water" placeholder="水分 ml">
                        <input type="number" id="record-bp-high" placeholder="血圧(上)">
                        <input type="number" id="record-bp-low" placeholder="血圧(下)">
                        <textarea id="record-content" placeholder="ケア内容・特記事項を入力してください" style="grid-column: span 2; height: 80px;"></textarea>
                    </div>
                    <div style="margin-top: 10px; display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" id="record-reset-btn" style="display: none; background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px;">新規作成へ戻る</button>
                        <button type="submit" id="record-submit-btn" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">記録を保存</button>
                    </div>
                </form>
            </section>

            <section id="provider-register-section">
                <h2 style="font-size: 1.1em; margin-top: 0; margin-bottom: 15px; color: #333;">🏢 自事業者（自社）情報</h2>
                <form id="provider-register-form">
                    <input type="hidden" id="prov-id" value="1">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="grid-column: span 2;"><label>事業者名</label><input type="text" id="prov-name" name="name" required></div>
                        <div><label>郵便番号</label><input type="text" id="prov-postcode" name="postcode" maxlength="7" required></div>
                        <div><label>代表電話番号</label><input type="text" id="prov-tel" name="tel" required></div>
                        <div style="grid-column: span 2;"><label>住所</label><input type="text" id="prov-address" name="address" required></div>
                    </div>
                    <div style="margin-top: 10px; text-align: right;">
                        <button type="submit" style="background: #0056b3; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">事業者情報を更新</button>
                    </div>
                </form>

                <div style="margin-top: 25px; border-top: 2px dashed #eee; padding-top: 20px;">
                    <h3 style="font-size: 1em; color: #555; margin-bottom: 15px;">👥 職員アカウント作成</h3>
                    <form id="staff-register-form">
                        <input type="hidden" id="target-office-id">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                            <div><label>職員氏名</label><input type="text" id="staff-name" placeholder="例: 山田 太郎" required></div>
                            <div><label>メールアドレス</label><input type="email" id="staff-email" placeholder="staff@example.com" required></div>
                <div>
                    <label>初期パスワード</label>
                    <div style="position: relative;">
                        <input type="password" id="staff-password" placeholder="8文字以上" required 
                            style="width: 100%; padding-right: 65px;">
                        
                        <span class="password-toggle-icon" data-target="#staff-password" id="toggle-staff-password" 
                            style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); 
                                    cursor: pointer; user-select: none; font-size: 11px; 
                                    background: #f0f0f0; padding: 4px 8px; border: 1px solid #ccc; 
                                    border-radius: 4px; color: #666; font-weight: bold; line-height: 1;">
                            表示
                        </span>
                    </div>
                </div>                        </div>
                         <div style="margin-top: 10px; text-align: right;">
                            <button type="submit" style="background: #17a2b8; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">職員を登録する</button>
                        </div>
                    </form>
                </div>
                <div style="margin-top: 20px;">
                    <label>👨‍⚕️ 所属職員一覧</label>
                    <div id="staff-list" style="background: #f9f9f9; border: 1px solid #eee; border-radius: 4px; margin-top: 5px; max-height: 150px; overflow-y: auto;">
                        <p style="padding: 10px; color: #999; font-size: 0.9em;">読み込み中...</p>
                    </div>
                </div>
            </section>
        </div>

        <div style="display: flex; flex-direction: column;">
            <section>
                <h2 style="font-size: 1.1em; margin-bottom: 10px;">💬 AIチャット相談</h2>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; font-size: 0.85em; background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #eee;">
                    <span style="font-weight: bold; color: #555;">🔊 AI回答の音声読み上げ</span>
                    <label class="switch">
                        <input type="checkbox" id="voice-read-toggle" style="display: none;">
                        <div id="toggle-bg"><div id="toggle-circle"></div></div>
                    </label>
                </div>
                <select id="client-select" style="margin-bottom: 10px;"><option value="">利用者を選択してください</option></select>
                <div id="chat-window" style="height: 400px; overflow-y: auto; background: #fafafa; border: 1px solid #eee; padding: 15px; margin-bottom: 10px; border-radius: 6px;"></div>
                <form id="chat-form" style="display: flex; gap: 8px;">
                    <button type="button" id="voice-input-btn" style="background: #007bff; color: white; border: none; padding: 0 12px; border-radius: 6px;">🎤</button>
                    <input type="text" id="user-input" placeholder="主任に相談..." required style="flex-grow: 1;">
                    <button type="submit" style="background: #28a745; color: white; border: none; padding: 0 20px; border-radius: 6px;">送信</button>
                    <button type="button" id="chat-clear-btn" style="background: #dc3545; color: white; border: none; padding: 0 12px; border-radius: 6px;">クリア</button>
                </form>
            </section>

            <section>
                <h2 style="font-size: 1.1em; margin-top: 0;">📊 バイタル分析</h2>
                <div style="margin-bottom: 10px; display: flex; gap: 5px;">
                    <button type="button" class="quick-date-btn" data-range="week" style="font-size: 0.75em; padding: 4px 8px; background: white; border: 1px solid #ccc; border-radius: 4px;">直近1週間</button>
                    <button type="button" class="quick-date-btn" data-range="month" style="font-size: 0.75em; padding: 4px 8px; background: white; border: 1px solid #ccc; border-radius: 4px;">今月</button>
                </div>
                <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 5px;">
                    <input type="date" id="search-start-date" style="width: 32%;">
                    <span>〜</span>
                    <input type="date" id="search-end-date" style="width: 32%;">
                    <button type="button" id="update-graph-btn" style="background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px;">表示</button>
                    
                    <button type="button" id="chart-clear-btn" style="background: #6c757d; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size: 0.8em;" title="グラフをリセット">リセット</button>
                </div>
                <canvas id="vitalChart" style="max-height: 250px;"></canvas>
            </section>
        </div>
    </main>

    <div id="client-modal" class="modal"><div class="modal-content"><h3>利用者一覧</h3><table style="width: 100%; border-collapse: collapse;"><thead><tr style="background: #f8f9fa;"><th style="padding: 10px; text-align: left;">ID</th><th style="padding: 10px; text-align: left;">氏名</th><th style="padding: 10px; text-align: left;">住所</th><th style="padding: 10px; text-align: center;">操作</th></tr></thead><tbody id="modal-client-table-body"></tbody></table><button id="close-client-modal" style="margin-top: 15px; padding: 8px 16px;">閉じる</button></div></div>
    <div id="record-modal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000;">
        <div style="background:white; width:95%; max-width:1000px; margin:2% auto; padding:20px; border-radius:8px; max-height:90vh; overflow-y:auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:2px solid #6c757d; padding-bottom:10px;">
                <h2 style="margin:0; font-size:1.2em;">📋 過去記録・バイタル一覧</h2>
                <button type="button" id="close-record-modal" style="background:none; border:none; font-size:1.5em; cursor:pointer;">&times;</button>
            </div>

    <table style="width:100%; border-collapse:collapse; font-size:0.9em;">
        <thead style="background:#f8f9fa; position:sticky; top:0; z-index:10;">
            <tr>
                <th class="sort-btn" data-type="date" data-col="0" style="border:1px solid #ddd; padding:10px; width:120px; cursor:pointer;">日時 ↕</th>
                <th style="border:1px solid #ddd; padding:10px; width:150px;">利用者</th>
                <th style="border:1px solid #ddd; padding:10px; width:100px;">記録者</th>
                <th class="sort-btn" data-type="number" data-col="3" style="border:1px solid #ddd; padding:10px; width:70px; cursor:pointer;">体温 ↕</th>
                <th class="sort-btn" data-type="number" data-col="4" style="border:1px solid #ddd; padding:10px; width:90px; cursor:pointer;">血圧(上) ↕</th>
                <th class="sort-btn" data-type="number" data-col="5" style="border:1px solid #ddd; padding:10px; width:70px; cursor:pointer;">水分 ↕</th>
                <th style="border:1px solid #ddd; padding:10px;">内容</th>
                <th style="border:1px solid #ddd; padding:10px; width:70px;">操作</th>
            </tr>
            <tr style="background:#eee;">
                <th style="padding:5px; font-size:0.7em;">
                    <input type="date" id="filter-date-start" class="range-filter" style="width:100%; margin-bottom:2px;"><br>
                    <input type="date" id="filter-date-end" class="range-filter" style="width:100%;">
                </th>
                <th style="padding:5px;"><input type="text" class="record-filter" data-col="1" placeholder="氏名..." style="width:100%;"></th>
                <th style="padding:5px;"><select id="filter-staff-select" class="record-filter" data-col="2" style="width:100%;"><option value="">全員</option></select></th>
                <th style="padding:5px;"><input type="text" class="record-filter" data-col="3" placeholder="体温..." style="width:100%;"></th>
                <th style="padding:5px;"><input type="text" class="record-filter" data-col="4" placeholder="血圧..." style="width:100%;"></th>
                <th style="padding:5px;"><input type="text" class="record-filter" data-col="5" placeholder="水分..." style="width:100%;"></th>
                <th style="padding:5px;"><input type="text" class="record-filter" data-col="6" placeholder="内容..." style="width:100%;"></th>
                <th style="background:#ddd;"></th>
            </tr>
        </thead>
        <tbody id="modal-record-table-body"></tbody>
    </table>
    </div>
    </div>
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>