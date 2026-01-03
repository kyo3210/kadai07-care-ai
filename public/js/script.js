// =======================================================
// 1. 初期設定・プロンプト（ペルソナ）設定
// =======================================================
axios.defaults.withCredentials = true;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

const SYSTEM_PROMPT = [
    "あなたは介護現場の第一線で活躍し、後輩の指導やご家族対応も担当する『ベテランの介護現場リーダー（主任クラス）』です。",
    "提示された期間指定とバイタル数値の変化、およびケア内容に基づき、現場を支える責任者の視点で簡潔に回答を行ってください。",
    "【回答の指針】",
    "1. 現場視点の要約：客観的なデータに基づき、現場で今何が起きているのか、実務的な視点で簡潔に要約してください。",
    "2. ご家族への配慮：ご家族への報告や説明時に配慮すべき点（安心感を与える伝え方や注意点など）は、質問者から求められた場合に限り、具体的に提案してください。",
    "3. 実務的な助言：具体的な対応方針や後輩スタッフへの指導、リスク回避のヒントは、質問者から明確に求められた場合にのみ回答してください。",
    "4. 整形ルール：回答はHTMLの<br>タグのみを使用して整形してください。読み上げの妨げになる「＊」「■」「・」などの記号は一切使用しないでください。",
    "5. 口調：現場を共に守る仲間として、信頼感と温かみがあり、かつプロとしての鋭さも兼ね備えた落ち着いた口調で回答してください。"
].join('\n');

function getCurrentUserName() {
    return document.querySelector('meta[name="user-name"]')?.getAttribute('content') || "担当スタッフ";
}

function appendMessage(sender, message) {
    const chatWindow = $('#chat-window');
    const messageClass = sender === 'user' ? 'user-message' : 'ai-message';
    let html = '';
    if (sender === 'ai') {
        html = `<div class="${messageClass}" style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 15px;"> 
                <img src="/images/AI.gif" alt="AI" style="height: 35px; width: 35px; border-radius: 50%;">
                <div style="background: #eef4ff; padding: 12px; border-radius: 12px; color: #0056b3; line-height: 1.6; border: 1px solid #d1e3f8;">${message}</div>
            </div>`;
    } else {
        html = `<div class="${messageClass}" style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-bottom: 10px;">
                <div style="background: #f0f0f0; padding: 10px; border-radius: 10px; color: #333;">${message}</div>
                <img src="/images/Q.png" alt="Q" style="height: 25px; width: 25px;">
            </div>`;
    }
    chatWindow.append(html);
    chatWindow.scrollTop(chatWindow[0].scrollHeight);
}

// =======================================================
// 2. 音声読み上げ・入力機能
// =======================================================
function speakText(text) {
    if (!$('#voice-read-toggle').prop('checked')) return;
    let cleanText = text.replace(/<[^>]*>/g, '').replace(/[＊\*・■□▲△▼▽：｜｜]/g, ' ');
    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.lang = 'ja-JP'; utterance.rate = 0.95; utterance.pitch = 0.85; 
    window.speechSynthesis.speak(utterance);
}

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
let recognition;
if (SpeechRecognition) {
    recognition = new SpeechRecognition();
    recognition.lang = 'ja-JP';
    recognition.onresult = (e) => { $('#user-input').val(e.results[0][0].transcript); };
    recognition.onend = () => { $('#voice-input-btn').css('background', '#007bff').text('🎤'); };
}

// =======================================================
// 3. バイタル分析グラフ機能
// =======================================================
let vitalChart = null;
function clearVitalChart() {
    if (vitalChart) { vitalChart.destroy(); vitalChart = null; }
    const canvas = document.getElementById('vitalChart');
    if (canvas) { canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height); }
}

function updateVitalChart(vitalData) {
    const ctx = document.getElementById('vitalChart').getContext('2d');
    if (vitalChart) { vitalChart.destroy(); }
    vitalData.sort((a, b) => new Date(a.date) - new Date(b.date));
    vitalChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: vitalData.map(d => d.date),
            datasets: [
                { label: '体温(℃)', data: vitalData.map(d => d.temp), borderColor: '#ff6384', backgroundColor: 'rgba(255, 99, 132, 0.2)', yAxisID: 'y-temp', tension: 0.3 },
                { label: '血圧(上)', data: vitalData.map(d => d.bp_high), borderColor: '#36a2eb', backgroundColor: 'rgba(54, 162, 235, 0.2)', yAxisID: 'y-bp', tension: 0.3 },
                { label: '血圧(下)', data: vitalData.map(d => d.bp_low), borderColor: '#4bc0c0', backgroundColor: 'rgba(75, 192, 192, 0.2)', yAxisID: 'y-bp', tension: 0.3 }
            ]
        },
        options: { responsive: true, scales: { 'y-temp': { min: 34, max: 40 }, 'y-bp': { min: 40, max: 200 } } }
    });
}

// =======================================================
// 4. データ取得・表示関連
// =======================================================
async function fetchClients() {
    try {
        const response = await axios.get('/web-api/clients');
        ['#client-select', '#record-client-select'].forEach(id => {
            const $el = $(id); $el.empty().append('<option value="">利用者を選択してください</option>');
            response.data.forEach(c => $el.append(`<option value="${c.id}">${c.id}: ${c.client_name}</option>`));
        });
    } catch (e) { console.error(e); }
}

async function fetchOfficeInfo() {
    try {
        const response = await axios.get('/web-api/offices');
        if (response.data.length > 0) {
            const office = response.data[0];
            $('#prov-id').val(office.id); $('#prov-name').val(office.name); $('#prov-postcode').val(office.postcode); $('#prov-tel').val(office.tel); $('#prov-address').val(office.address);
            $('#target-office-id').val(office.id);
        }
    } catch (e) { console.error("事業所情報取得エラー:", e); }
}

async function fetchStaffList() {
    try {
        const res = await axios.get('/web-api/staff');
        const $list = $('#staff-list'); 
        if (res.data.length === 0) {
            $list.html('<p style="padding: 10px; color: #999; font-size: 0.9em;">登録された職員はいません</p>');
            return;
        }
        const html = res.data.map(s => `
            <div style="padding: 8px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: bold; color: #444;">${s.name}</span>
                <span style="font-size: 0.85em; color: #777;">${s.email}</span>
            </div>`).join('');
        $list.html(html);
    } catch (e) { console.error(e); }
}

async function renderModalClientList() {
    const res = await axios.get('/web-api/clients');
    let html = res.data.map(c => `<tr><td>${c.id}</td><td>${c.client_name}</td><td>${c.address}</td><td style="text-align:center;"><button type="button" class="select-client-btn" data-client='${JSON.stringify(c)}' style="background:#007bff; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">選択</button></td></tr>`).join('');
    $('#modal-client-table-body').html(html);
}

// 【改善：①ID+氏名表示、②表示量アップ、③スタッフ選択肢生成】
async function renderRecordList() {
    try {
        const [res, clientRes, staffRes] = await Promise.all([
            axios.get('/web-api/all-records'),
            axios.get('/web-api/clients'),
            axios.get('/web-api/staff')
        ]);
        const clientMap = {};
        clientRes.data.forEach(c => clientMap[c.id] = c.client_name);

        // ③ 記録者の選択肢を生成
        const $staffSelect = $('#filter-staff-select');
        $staffSelect.find('option:not(:first)').remove();
        staffRes.data.forEach(s => $staffSelect.append(`<option value="${s.name}">${s.name}</option>`));

        let html = res.data.map(r => {
            const bp = (r.blood_pressure_high && r.blood_pressure_low) ? `${r.blood_pressure_high}/${r.blood_pressure_low}` : '-';
            const clientName = clientMap[r.client_id] || "不明";
            return `<tr>
                <td style="font-size:0.85em; border:1px solid #ddd; padding:8px;">${r.recorded_at.substring(0, 16)}</td>
                <td style="border:1px solid #ddd; padding:8px;">${r.client_id}: ${clientName}</td>
                <td style="border:1px solid #ddd; padding:8px;">${r.recorded_by || '-'}</td>
                <td style="text-align:center; border:1px solid #ddd; padding:8px;">${r.body_temp || '-'}</td>
                <td style="text-align:center; border:1px solid #ddd; padding:8px;">${r.blood_pressure_high || '-'}</td>
                <td style="text-align:center; border:1px solid #ddd; padding:8px;">${r.water_intake || '-'}</td>
                <td style="max-width:350px; border:1px solid #ddd; padding:8px; font-size:0.85em; line-height:1.4; word-break:break-all;">${r.content}</td>
                <td style="text-align:center; border:1px solid #ddd; padding:8px;">
                    <button type="button" class="select-record-btn" data-record='${JSON.stringify(r)}' style="background:#6c757d; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">編集</button>
                </td>
            </tr>`;
        }).join('');
        $('#modal-record-table-body').html(html);
    } catch (e) { console.error("過去記録取得エラー:", e); }
}

// =======================================================
// 5. イベントハンドラ
// =======================================================
$(document).ready(function() {
    fetchClients(); fetchOfficeInfo(); fetchStaffList();

    // 【改善：①日付範囲フィルタ、③記録者セレクトフィルタ】
    $(document).on('input change', '.record-filter, .range-filter', function() {
        const start = $('#filter-date-start').val();
        const end = $('#filter-date-end').val();
        const filters = $('.record-filter').map(function() {
            return { col: $(this).data('col'), val: $(this).val().toLowerCase() };
        }).get();

        $('#modal-record-table-body tr').each(function() {
            const row = $(this);
            const tds = row.find('td');
            const rowDate = tds.eq(0).text().substring(0, 10);
            let show = true;

            if (start && rowDate < start) show = false;
            if (end && rowDate > end) show = false;

            filters.forEach(f => {
                const cellText = tds.eq(f.col).text().toLowerCase();
                if (f.val && cellText.indexOf(f.val) === -1) show = false;
            });
            show ? row.show() : row.hide();
        });
    });

    // 【改善：②昇順・降順ソート】
    let sortOrder = 1;
    $(document).on('click', '.sort-btn', function() {
        const col = $(this).data('col');
        const type = $(this).data('type');
        const tbody = $('#modal-record-table-body');
        const rows = tbody.find('tr').toArray();
        sortOrder *= -1;
        rows.sort((a, b) => {
            let vA = $(a).find('td').eq(col).text();
            let vB = $(b).find('td').eq(col).text();
            if (type === 'number') {
                vA = parseFloat(vA) || 0; vB = parseFloat(vB) || 0;
            }
            return vA > vB ? sortOrder : -sortOrder;
        });
        tbody.append(rows);
    });

    // グラフ：直近1週間・今月ボタン
    $('.quick-date-btn').on('click', function() {
        const range = $(this).data('range');
        const end = new Date(); let start = new Date();
        if (range === 'week') start.setDate(end.getDate() - 7);
        if (range === 'month') start.setDate(1);
        $('#search-start-date').val(start.toISOString().substring(0, 10));
        $('#search-end-date').val(end.toISOString().substring(0, 10));
    });

    // グラフ：表示ボタン
    $('#update-graph-btn').on('click', async function() {
        const cid = $('#client-select').val();
        if (!cid) { alert("利用者を選択してください"); return; }
        const $btn = $(this); $btn.text('...').prop('disabled', true);
        try {
            const res = await axios.post('/web-api/ask-ai', {
                clientId: cid, question: '', 
                startDate: $('#search-start-date').val(),
                endDate: $('#search-end-date').val(),
                systemPrompt: '取得'
            });
            if (res.data.vitalData) updateVitalChart(res.data.vitalData);
        } catch (e) { alert("エラー"); } finally { $btn.text('表示').prop('disabled', false); }
    });

    // グラフ：リセットボタン
    $(document).on('click', '#chart-clear-btn', function() {
        clearVitalChart(); console.log("グラフをクリアしました");
    });

    // 事業者情報更新
    $('#provider-register-form').on('submit', async function(e) {
        e.preventDefault();
        const data = { id: $('#prov-id').val(), name: $('#prov-name').val(), postcode: $('#prov-postcode').val(), tel: $('#prov-tel').val(), address: $('#prov-address').val() };
        try {
            const res = await axios.post('/web-api/offices/update', data);
            if (res.data.status === 'success') { alert(res.data.message); fetchOfficeInfo(); }
        } catch (e) { alert("更新失敗"); }
    });

    // パスワード表示切替
    $(document).on('click', '#toggle-staff-password', function() {
        const input = $('#staff-password');
        const isPass = input.attr('type') === 'password';
        input.attr('type', isPass ? 'text' : 'password');
        $(this).text(isPass ? '非表示' : '表示').css('background', isPass ? '#e0e0e0' : '#f0f0f0');
    });

    // 職員登録
    $('#staff-register-form').on('submit', async function(e) {
        e.preventDefault();
        const officeId = $('#target-office-id').val();
        if (!officeId) { alert("エラー：事業所情報が読み込まれていません。"); return; }
        const data = { name: $('#staff-name').val(), email: $('#staff-email').val(), password: $('#staff-password').val(), office_id: officeId };
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('登録中...');
        try {
            const res = await axios.post('/web-api/staff', data);
            if (res.data.status === 'success') { alert("新しい職員を登録しました。"); $('#staff-register-form')[0].reset(); fetchStaffList(); }
        } catch (e) { alert("登録失敗。"); } finally { $btn.prop('disabled', false).text('職員を登録する'); }
    });

    // 住所検索
    $('#search-zipcode').on('click', async function() {
        const zip = $('#reg-zipcode').val().replace(/[^0-9]/g, ''); 
        if (zip.length !== 7) { alert("7桁で入力してください"); return; }
        const $btn = $(this); $btn.text('...').prop('disabled', true);
        try {
            const res = await axios.get(`/web-api/zipcode/${zip}`);
            if (res.data.status === 200 && res.data.results) {
                const r = res.data.results[0]; $('#reg-address').val(r.address1 + r.address2 + r.address3);
            } else { alert("住所が見つかりませんでした"); }
        } catch (e) { alert("検索失敗"); } finally { $btn.text('検索').prop('disabled', false); }
    });

    // 利用者保存
    $('#client-register-form').on('submit', async function(e) {
        e.preventDefault();
        const data = { id: $('#reg-client-id').val(), client_name: $('#reg-client-name').val(), postcode: $('#reg-zipcode').val(), address: $('#reg-address').val(), contact_tel: $('#reg-contact-tel').val(), insurace_number: $('#reg-insurance').val(), care_start_date: $('#reg-start-date').val(), care_end_date: $('#reg-end-date').val(), care_manager: $('#reg-care-manager').val(), care_manager_tel: $('#reg-care-manager-tel').val() };
        try { await axios.post('/web-api/clients', data); alert("保存完了"); fetchClients(); $('#form-reset-btn').click(); } catch (e) { alert("保存失敗"); }
    });

    // ケア記録保存
    $('#record-add-form').on('submit', async function(e) {
        e.preventDefault();
        const data = { edit_record_id: $('#edit-record-id').val(), client_id: $('#record-client-select').val(), date: $('#record-date').val(), time: $('#record-time').val(), content: $('#record-content').val(), body_temp: $('#record-temp').val(), blood_pressure_high: $('#record-bp-high').val(), blood_pressure_low: $('#record-bp-low').val(), water_intake: $('#record-water').val(), recorded_by: getCurrentUserName() };
        try { const res = await axios.post('/web-api/records', data); if (res.data.status === 'success') { alert("記録完了"); $('#record-add-form')[0].reset(); clearVitalChart(); } } catch (e) { alert("保存失敗"); }
    });

    // 過去記録参照モーダル
    $('#open-record-modal').on('click', () => { renderRecordList(); $('#record-modal').fadeIn(200); });

    // AIチャット
    $('#chat-form').on('submit', async function(e) {
        e.preventDefault();
        const q = $('#user-input').val(); const cid = $('#client-select').val();
        appendMessage('user', q); $('#user-input').val(''); appendMessage('ai', '分析中...');
        try {
            const res = await axios.post('/web-api/ask-ai', { clientId: cid, question: q, startDate: $('#search-start-date').val(), endDate: $('#search-end-date').val(), systemPrompt: SYSTEM_PROMPT });
            $('.ai-message').last().remove(); appendMessage('ai', res.data.answer); speakText(res.data.answer);
            if(cid && res.data.vitalData) updateVitalChart(res.data.vitalData);
        } catch (e) { $('.ai-message').last().remove(); appendMessage('ai', '通信エラー'); }
    });
    $(document).on('click', '#chat-clear-btn', function() { $('#chat-window').empty(); });

    // その他
    $('#form-reset-btn').on('click', function() { if(confirm('クリアしますか？')) { $('#client-register-form')[0].reset(); $('#reg-client-id').val('').attr('readonly', false); $('#client-delete-btn').hide(); } });
    $('#voice-read-toggle').on('change', function() { const ok = $(this).prop('checked'); $('#toggle-bg').css('background-color', ok ? '#28a745' : '#ccc'); $('#toggle-circle').css('transform', ok ? 'translateX(22px)' : 'translateX(0px)'); if (!ok) window.speechSynthesis.cancel(); });
    $('#voice-input-btn').on('click', function() { recognition.start(); $(this).css('background', '#dc3545').text('●'); });
    $(document).on('click', '.select-client-btn', function() { 
        const c = $(this).data('client'); $('#reg-client-id').val(c.id).attr('readonly', true); $('#reg-client-name').val(c.client_name); $('#reg-zipcode').val(c.postcode); $('#reg-address').val(c.address); $('#reg-contact-tel').val(c.contact_tel); $('#reg-insurance').val(c.insurace_number); $('#reg-start-date').val(c.care_start_date); $('#reg-end-date').val(c.care_end_date); $('#reg-care-manager').val(c.care_manager); $('#reg-care-manager-tel').val(c.care_manager_tel); $('#client-delete-btn').show(); $('#client-modal').fadeOut(200); 
    });
    $(document).on('click', '.select-record-btn', function() {
        const r = $(this).data('record'); const [d, t] = r.recorded_at.split(' ');
        $('#edit-record-id').val(r.id); $('#record-client-select').val(r.client_id); $('#record-date').val(d); $('#record-time').val(t.substring(0,5));
        $('#record-temp').val(r.body_temp); $('#record-bp-high').val(r.blood_pressure_high); $('#record-bp-low').val(r.blood_pressure_low); $('#record-water').val(r.water_intake); $('#record-content').val(r.content);
        $('#record-modal').fadeOut(200);
    });
    $('#open-client-modal').on('click', () => { renderModalClientList(); $('#client-modal').fadeIn(200); });
    $('#close-client-modal, #close-record-modal').on('click', () => $('.modal').fadeOut(200));
});