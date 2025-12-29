// =======================================================
// 1. 初期設定・共通関数
// =======================================================

axios.defaults.withCredentials = true;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

// AIペルソナ設定（維持）
const SYSTEM_PROMPT = [
    "あなたはベテランのケアマネジャー兼訪問介護スタッフです。",
    "提示された期間指定とバイタル数値の変化とケア内容に基づき回答を行ってください。",
    "1. 回答は簡潔に要約し、HTMLの<br>タグやリストを使って整形してください。", 
    "2. 注意点や傾向分析、対応方針の提示を求められた場合は、アドバイスや注意点を指摘してください。"
].join('\n');

function getCurrentUserName() {
    return document.querySelector('meta[name="user-name"]')?.getAttribute('content') || "担当スタッフ";
}

// チャット画面へのメッセージ追加
function appendMessage(sender, message) {
    const chatWindow = $('#chat-window');
    const messageClass = sender === 'user' ? 'user-message' : 'ai-message';
    let html = '';
    if (sender === 'ai') {
        html = `
            <div class="${messageClass}" style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 15px;"> 
                <img src="/images/AI.gif" alt="AI" style="height: 35px; width: 35px; border-radius: 50%;">
                <div style="background: #eef4ff; padding: 12px; border-radius: 12px; color: #0056b3; line-height: 1.6; border: 1px solid #d1e3f8;">${message}</div>
            </div>
        `;
    } else {
        html = `
            <div class="${messageClass}" style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-bottom: 10px;">
                <div style="background: #f0f0f0; padding: 10px; border-radius: 10px; color: #333;">${message}</div>
                <img src="/images/Q.png" alt="Q" style="height: 25px; width: 25px;">
            </div>
        `;
    }
    chatWindow.append(html);
    chatWindow.scrollTop(chatWindow[0].scrollHeight);
}

// =======================================================
// 2. バイタル分析グラフ機能 (3本線対応)
// =======================================================

let vitalChart = null;

function updateVitalChart(vitalData) {
    const ctx = document.getElementById('vitalChart').getContext('2d');
    if (vitalChart) { vitalChart.destroy(); }

    vitalData.sort((a, b) => new Date(a.date) - new Date(b.date));

    vitalChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: vitalData.map(d => d.date),
            datasets: [
                {
                    label: '体温(℃)',
                    data: vitalData.map(d => d.temp),
                    borderColor: '#ff6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    yAxisID: 'y-temp',
                    tension: 0.3
                },
                {
                    label: '血圧(上)',
                    data: vitalData.map(d => d.bp_high),
                    borderColor: '#36a2eb',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    yAxisID: 'y-bp',
                    tension: 0.3
                },
                {
                    label: '血圧(下)',
                    data: vitalData.map(d => d.bp_low),
                    borderColor: '#4bc0c0',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    yAxisID: 'y-bp',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                'y-temp': { type: 'linear', position: 'left', min: 34, max: 40, title: { display: true, text: '体温(℃)' } },
                'y-bp': { type: 'linear', position: 'right', min: 40, max: 200, title: { display: true, text: '血圧(mmHg)' } }
            }
        }
    });
}

// =======================================================
// 3. データ取得・モーダル表示
// =======================================================

async function fetchClients() {
    try {
        const response = await axios.get('/web-api/clients');
        const clients = response.data;
        ['#client-select', '#record-client-select'].forEach(id => {
            const $el = $(id);
            $el.empty().append('<option value="">利用者を選択してください</option>');
            clients.forEach(c => $el.append(`<option value="${c.id}">${c.id}: ${c.client_name}</option>`));
        });
    } catch (e) { console.error(e); }
}

async function renderModalClientList() {
    const res = await axios.get('/web-api/clients');
    let html = '';
    res.data.forEach(c => {
        html += `<tr>
            <td style="padding:10px; border-bottom:1px solid #eee;">${c.id}</td>
            <td style="padding:10px; border-bottom:1px solid #eee;">${c.client_name}</td>
            <td style="padding:10px; border-bottom:1px solid #eee;">${c.address}</td>
            <td style="padding:10px; border-bottom:1px solid #eee; text-align:center;">
                <button type="button" class="select-client-btn" data-client='${JSON.stringify(c)}' style="background:#007bff; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">選択</button>
            </td>
        </tr>`;
    });
    $('#modal-client-table-body').html(html);
}

async function renderRecordList() {
    const res = await axios.get('/web-api/all-records');
    let html = '';
    res.data.forEach(r => {
        const dt = r.recorded_at.substring(0, 16);
        html += `<tr>
            <td style="padding:10px; border-bottom:1px solid #eee;">${dt}</td>
            <td style="padding:10px; border-bottom:1px solid #eee;">${r.client_id}</td>
            <td style="padding:10px; border-bottom:1px solid #eee;">${r.content.substring(0,20)}...</td>
            <td style="padding:10px; border-bottom:1px solid #eee; text-align:center;">${r.body_temp}℃ / ${r.blood_pressure_high}/${r.blood_pressure_low}</td>
            <td style="padding:10px; border-bottom:1px solid #eee; text-align:center;">
                <button type="button" class="select-record-btn" data-record='${JSON.stringify(r)}' style="background:#6c757d; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">編集</button>
            </td>
        </tr>`;
    });
    $('#modal-record-table-body').html(html);
}

// =======================================================
// 4. イベントハンドラ
// =======================================================

$(document).ready(function() {
    fetchClients();

    // クイック期間選択ボタン
    $('.quick-date-btn').on('click', function() {
        const range = $(this).data('range');
        const end = new Date();
        let start = new Date();
        if (range === 'week') start.setDate(end.getDate() - 7);
        else if (range === 'month') start.setDate(1);
        const formatDate = (date) => date.toISOString().split('T')[0];
        $('#search-start-date').val(formatDate(start));
        $('#search-end-date').val(formatDate(end));
        $('.quick-date-btn').css('background', '#fff');
        $(this).css('background', '#eef4ff');
    });

    // 郵便番号検索
    $('#search-zipcode').on('click', async function() {
        const zip = $('#reg-zipcode').val().replace('-', '');
        try {
            const res = await axios.get(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${zip}`);
            if (res.data.results) {
                const r = res.data.results[0];
                $('#reg-address').val(r.address1 + r.address2 + r.address3);
            }
        } catch (e) { alert("住所検索に失敗しました"); }
    });

    // 利用者モーダル操作
    $('#open-client-modal').on('click', () => { renderModalClientList(); $('#client-modal').fadeIn(200); });
    $('#close-client-modal').on('click', () => $('#client-modal').fadeOut(200));
    $(document).on('click', '.select-client-btn', function() {
        const c = $(this).data('client');
        $('#reg-client-id').val(c.id).attr('readonly', true).css('background', '#f0f0f0');
        $('#reg-client-name').val(c.client_name);
        $('#reg-zipcode').val(c.postcode);
        $('#reg-address').val(c.address);
        $('#reg-contact-tel').val(c.contact_tel);
        $('#reg-insurance').val(c.insurace_number);
        $('#reg-start-date').val(c.care_start_date);
        $('#reg-end-date').val(c.care_end_date);
        $('#reg-care-manager').val(c.care_manager);
        $('#reg-care-manager-tel').val(c.care_manager_tel);
        $('#client-modal').fadeOut(200);
        $('html, body').animate({ scrollTop: $("#client-register-form").offset().top - 100 }, 500);
    });

    // ケア記録モーダル操作
    $('#open-record-modal').on('click', () => { renderRecordList(); $('#record-modal').fadeIn(200); });
    $('#close-record-modal').on('click', () => $('#record-modal').fadeOut(200));
    $(document).on('click', '.select-record-btn', function() {
        const r = $(this).data('record');
        const [date, timeFull] = r.recorded_at.split(' ');
        $('#edit-record-id').val(r.id);
        $('#record-client-select').val(r.client_id);
        $('#record-date').val(date);
        $('#record-time').val(timeFull.substring(0, 5));
        $('#record-temp').val(r.body_temp);
        $('#record-bp-high').val(r.blood_pressure_high);
        $('#record-bp-low').val(r.blood_pressure_low);
        $('#record-water').val(r.water_intake);
        $('#record-content').val(r.content);
        $('#record-submit-btn').text('記録を更新する').css('background', '#e67e22');
        $('#record-reset-btn').show();
        $('#record-modal').fadeOut(200);
    });

    $('#record-reset-btn').on('click', function() {
        $('#record-add-form')[0].reset();
        $('#edit-record-id').val('');
        $('#record-submit-btn').text('記録を保存').css('background', '#6c757d');
        $(this).hide();
    });

    // 記録保存
    $('#record-add-form').on('submit', async function(e) {
        e.preventDefault();
        const data = {
            edit_record_id: $('#edit-record-id').val(),
            client_id: $('#record-client-select').val(),
            date: $('#record-date').val(),
            time: $('#record-time').val(),
            content: $('#record-content').val(),
            body_temp: $('#record-temp').val(),
            blood_pressure_high: $('#record-bp-high').val(),
            blood_pressure_low: $('#record-bp-low').val(),
            water_intake: $('#record-water').val(),
            recorded_by: getCurrentUserName()
        };
        try {
            await axios.post('/web-api/records', data);
            alert("保存しました");
            $('#record-reset-btn').click();
        } catch (e) { alert("保存に失敗しました"); }
    });

    // AIチャット & グラフ連動
    $('#chat-form').on('submit', async function(e) {
        e.preventDefault();
        const q = $('#user-input').val();
        const cid = $('#client-select').val();
        if(!cid){ alert("利用者を選択してください"); return; }
        appendMessage('user', q);
        $('#user-input').val('');
        appendMessage('ai', '思考中...');
        try {
            const res = await axios.post('/web-api/ask-ai', {
                clientId: cid, question: q,
                startDate: $('#search-start-date').val(),
                endDate: $('#search-end-date').val(),
                systemPrompt: SYSTEM_PROMPT
            });
            $('#chat-window .ai-message').last().remove();
            appendMessage('ai', res.data.answer);
            if(res.data.vitalData) updateVitalChart(res.data.vitalData);
        } catch (e) { 
            $('#chat-window .ai-message').last().remove();
            appendMessage('ai', 'エラーが発生しました。'); 
        }
    });

    // 利用者保存
    $('#client-register-form').on('submit', async function(e) {
        e.preventDefault();
        const data = {
            id: $('#reg-client-id').val(),
            client_name: $('#reg-client-name').val(),
            postcode: $('#reg-zipcode').val(),
            address: $('#reg-address').val(),
            contact_tel: $('#reg-contact-tel').val(),
            insurace_number: $('#reg-insurance').val(),
            care_start_date: $('#reg-start-date').val(),
            care_end_date: $('#reg-end-date').val(),
            care_manager: $('#reg-care-manager').val(),
            care_manager_tel: $('#reg-care-manager-tel').val()
        };
        try {
            await axios.post('/web-api/clients', data);
            alert("利用者情報を保存しました");
            fetchClients();
            $('#reg-client-id').attr('readonly', false).css('background', '#fff');
            this.reset();
        } catch (e) { alert("保存失敗。ID重複を確認してください。"); }
    });

    $('#form-reset-btn').on('click', function() {
        $('#client-register-form')[0].reset();
        $('#reg-client-id').attr('readonly', false).css('background', '#fff');
    });
});