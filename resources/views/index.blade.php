<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ケアAIアシスタント Pro</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-name" content="{{ Auth::user()->name }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="background-color: #f0f4f8; font-family: sans-serif; color: #333; margin: 0;">

    <header style="padding: 10px 20px; background: #fff; border-bottom: 2px solid #007bff; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0; font-size: 1.4em; color: #007bff;">ケアAIアシスタント Pro</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span>担当: <strong>{{ Auth::user()->name }}</strong></span>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background: #f8d7da; color: #721c24; padding: 5px 12px; border-radius: 4px; border: 1px solid #f5c6cb; cursor: pointer;">ログアウト</button>
            </form>
        </div>
    </header>

    <main style="max-width: 1200px; margin: 20px auto; padding: 0 20px;">

        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-bottom: 30px;">
            <section style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd;">
                <h2 style="font-size: 1.1em; margin-top: 0;">💬 AIチャット相談</h2>
                <select id="client-select" style="width: 100%; padding: 10px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #ccc;">
                    <option value="">利用者を選択してください</option>
                </select>
                <div id="chat-window" style="height: 300px; overflow-y: auto; background: #fafafa; border: 1px solid #eee; padding: 15px; margin-bottom: 10px; border-radius: 6px;"></div>
                <form id="chat-form" style="display: flex; gap: 8px;">
                    <input type="text" id="user-input" placeholder="質問を入力..." required style="flex-grow: 1; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                    <button type="submit" style="background: #28a745; color: white; border: none; padding: 0 20px; border-radius: 6px; cursor: pointer;">送信</button>
                </form>
            </section>
    <section style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd;">
        <h2 style="font-size: 1.1em; margin-top: 0;">📊 バイタル分析</h2>
        
        <div style="margin-bottom: 10px; display: flex; gap: 5px;">
            <button type="button" class="quick-date-btn" data-range="week" style="font-size: 0.75em; padding: 4px 8px; cursor: pointer; border-radius: 4px; border: 1px solid #ccc; background: #fff;">直近1週間</button>
            <button type="button" class="quick-date-btn" data-range="month" style="font-size: 0.75em; padding: 4px 8px; cursor: pointer; border-radius: 4px; border: 1px solid #ccc; background: #fff;">今月</button>
        </div>

        <div style="margin-bottom: 10px;">
            <input type="date" id="search-start-date" style="width: 45%;"> 〜 <input type="date" id="search-end-date" style="width: 45%;">
        </div>
        <canvas id="vitalChart" style="max-height: 250px;"></canvas>
    </section>

        </div>

        <section style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="font-size: 1.1em; border-left: 5px solid #17a2b8; padding-left: 10px; margin: 0;">👤 利用者 登録/編集</h2>
                <button type="button" id="open-client-modal" style="background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 0.9em;">
                    🔍 登録済み利用者から選択
                </button>
            </div>
            
            <form id="client-register-form">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div><label style="font-size: 0.8em;">利用者ID</label><input type="text" id="reg-client-id" required style="width: 100%; padding: 8px; box-sizing: border-box;"></div>
                    <div><label style="font-size: 0.8em;">利用者氏名</label><input type="text" id="reg-client-name" required style="width: 100%; padding: 8px; box-sizing: border-box;"></div>
                    <div>
                        <label style="font-size: 0.8em;">郵便番号</label>
                        <div style="display: flex; gap: 5px;">
                            <input type="text" id="reg-zipcode" maxlength="7" style="flex-grow: 1; padding: 8px;">
                            <button type="button" id="search-zipcode" style="padding: 5px; font-size: 0.8em; cursor: pointer;">検索</button>
                        </div>
                    </div>
                    <div><label style="font-size: 0.8em;">住所</label><input type="text" id="reg-address" required style="width: 100%; padding: 8px; box-sizing: border-box;"></div>
                    <div><label style="font-size: 0.8em;">電話番号</label><input type="text" id="reg-contact-tel" required style="width: 100%; padding: 8px; box-sizing: border-box;"></div>
                    <div><label style="font-size: 0.8em;">被保険者番号</label><input type="text" id="reg-insurance" style="width: 100%; padding: 8px; box-sizing: border-box;"></div>
                    <div><label style="font-size: 0.8em;">認定有効開始日</label><input type="date" id="reg-start-date" style="width: 100%; padding: 8px; box-sizing: border-box;"></div>
                    <div><label style="font-size: 0.8em;">認定有効終了日</label><input type="date" id="reg-end-date" style="width: 100%; padding: 8px; box-sizing: border-box;"></div>
                    <div><label style="font-size: 0.8em;">担当ケアマネ</label><input type="text" id="reg-care-manager" required style="width: 100%; padding: 8px; box-sizing: border-box;"></div>
                    <div><label style="font-size: 0.8em;">ケアマネ連絡先</label><input type="text" id="reg-care-manager-tel" style="width: 100%; padding: 8px; box-sizing: border-box;"></div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" id="client-submit-btn" style="flex: 3; padding: 12px; background: #17a2b8; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">利用者を保存する</button>
                    <button type="button" id="form-reset-btn" style="flex: 1; padding: 12px; background: #e9ecef; color: #495057; border: 1px solid #ced4da; border-radius: 6px; cursor: pointer;">クリア</button>
                </div>
            </form>
        </section>

        <section style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="font-size: 1.1em; border-left: 5px solid #6c757d; padding-left: 10px; margin: 0;">📚 ケア記録・バイタル追加</h2>
                <button type="button" id="open-record-modal" style="background: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 0.9em;">
                    🔍 過去の記録を選択・編集
                </button>
            </div>
            
            <form id="record-add-form">
                <input type="hidden" id="edit-record-id">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <select id="record-client-select" required style="width: 100%; padding: 10px;">
                        <option value="">利用者を選択</option>
                    </select>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" id="record-date" required style="flex-grow: 1; padding: 8px;">
                        <input type="time" id="record-time" required style="flex-grow: 1; padding: 8px;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px; background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <div><label style="font-size: 0.8em;">体温(℃)</label><input type="number" step="0.1" id="record-temp" style="width: 100%; padding: 8px;"></div>
                    <div><label style="font-size: 0.8em;">血圧(上)</label><input type="number" id="record-bp-high" style="width: 100%; padding: 8px;"></div>
                    <div><label style="font-size: 0.8em;">血圧(下)</label><input type="number" id="record-bp-low" style="width: 100%; padding: 8px;"></div>
                    <div><label style="font-size: 0.8em;">水分量(ml)</label><input type="number" id="record-water" style="width: 100%; padding: 8px;"></div>
                </div>

                <textarea id="record-content" placeholder="ケア内容・特記事項を入力してください..." required style="width: 100%; height: 80px; padding: 10px; margin-bottom: 10px; box-sizing: border-box;"></textarea>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" id="record-submit-btn" style="flex: 3; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer;">記録を保存</button>
                    <button type="button" id="record-reset-btn" style="flex: 1; padding: 12px; background: #e9ecef; color: #495057; border: 1px solid #ced4da; border-radius: 6px; cursor: pointer; display: none;">解除</button>
                </div>
            </form>
        </section>

        <section style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd; margin-bottom: 30px;">
            <h2 style="font-size: 1.1em; border-left: 5px solid #28a745; padding-left: 10px;">🔍 利用者検索・事業所からの経路</h2>
            <form id="client-search-form" style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
                <input type="text" id="search-query" placeholder="氏名またはIDで検索" required style="flex-grow: 1; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                <div style="display: flex; gap: 15px; font-size: 0.9em; background: #f8f9fa; padding: 10px; border-radius: 6px; border: 1px solid #ddd;">
                    <label style="cursor: pointer;"><input type="radio" name="map-option" value="none" checked> 検索のみ</label>
                    <label style="cursor: pointer;"><input type="radio" name="map-option" value="route"> 事業所からの経路</label>
                </div>
                <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer;">実行</button>
            </form>
            <div id="search-results-area"></div>
            <div id="map-container" style="display: none; margin-top: 15px;">
                <div id="map" style="width: 100%; height: 400px; border-radius: 10px; border: 1px solid #ccc;"></div>
            </div>
        </section>


        <section style="background: #f1f3f5; padding: 20px; border-radius: 12px; border: 1px solid #ced4da; margin-bottom: 50px;">
            <h2 style="font-size: 1.1em; border-left: 5px solid #495057; padding-left: 10px;">🏢 自事業所の設定</h2>
            <select id="office-list-select" style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 6px;">
                <option value="">事業所を選択してください</option>
            </select>
            <form id="office-update-form" style="background: #fff; padding: 15px; border-radius: 8px;">
                <input type="hidden" id="off-id">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><label style="font-size: 0.8em;">事業所名</label><input type="text" id="off-name" required style="width: 100%; padding: 8px;"></div>
                    <div><label style="font-size: 0.8em;">電話番号</label><input type="text" id="off-tel" required style="width: 100%; padding: 8px;"></div>
                    <div><label style="font-size: 0.8em;">郵便番号</label><input type="text" id="off-postcode" required style="width: 100%; padding: 8px;"></div>
                    <div><label style="font-size: 0.8em;">住所</label><input type="text" id="off-address" required style="width: 100%; padding: 8px;"></div>
                </div>
                <button type="submit" style="margin-top: 15px; padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer;">事業所情報を更新</button>
            </form>
        </section>

    </main>

    <div id="client-modal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6);">
        <div style="background-color: #fff; margin: 5% auto; padding: 25px; border-radius: 12px; width: 85%; max-height: 85%; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
                <h3 style="margin: 0; color: #007bff;">登録済み利用者を選択</h3>
                <button type="button" id="close-client-modal" style="background: none; border: none; font-size: 1.8em; cursor: pointer;">&times;</button>
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.9em;">
                <thead style="background: #f8f9fa;">
                    <tr><th>ID</th><th>氏名</th><th>住所</th><th>操作</th></tr>
                </thead>
                <tbody id="modal-client-table-body"></tbody>
            </table>
        </div>
    </div>

    <div id="record-modal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6);">
        <div style="background-color: #fff; margin: 3% auto; padding: 25px; border-radius: 12px; width: 90%; max-height: 85%; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">
                <h3 style="margin: 0; color: #6c757d;">過去のケア記録一覧</h3>
                <button type="button" id="close-record-modal" style="background: none; border: none; font-size: 1.8em; cursor: pointer;">&times;</button>
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85em;">
                <thead style="background: #f1f3f5;">
                    <tr><th>日時</th><th>利用者ID</th><th>内容</th><th>バイタル</th><th>操作</th></tr>
                </thead>
                <tbody id="modal-record-table-body"></tbody>
            </table>
        </div>
    </div>

    <footer style="text-align: center; padding: 20px; color: #888; font-size: 0.8em;">&copy; 2025 Care AI Pro</footer>

    <script type="module" src="{{ asset('js/script.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_ACTUAL_API_KEY&callback=initMap" async defer></script>
</body>
</html>