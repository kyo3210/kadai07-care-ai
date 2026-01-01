// =======================================================
// 1. 初期設定・プロンプト（ペルソナ）設定
// =======================================================

axios.defaults.withCredentials = true;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

// AIペルソナ設定（ベテラン現場リーダー・主任クラス）
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
// 2. 音声読み上げ・入力機能
// =======================================================

function speakText(text) {
    if (!$('#voice-read-toggle').prop('checked')) return;

    let cleanText = text.replace(/<[^>]*>/g, '');
    cleanText = cleanText.replace(/[＊\*・■□▲△▼▽：｜｜]/g, ' ');

    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.lang = 'ja-JP';
    utterance.rate = 0.95; 
    utterance.pitch = 0.85; 
    window.speechSynthesis.speak(utterance);
}

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
const SpeechGrammarList = window.SpeechGrammarList || window.webkitSpeechGrammarList;
let recognition;

if (SpeechRecognition) {
    recognition = new SpeechRecognition();
    recognition.lang = 'ja-JP';
    if (SpeechGrammarList) {
        const words = ['バイタル', 'ケアプラン', 'ADL', '褥瘡', '要介護', '認知症', '清拭', '移乗'];
        const grammar = '#JSGF V1.0; grammar kaigo; public <word> = ' + words.join(' | ') + ' ;';
        const speechRecognitionList = new SpeechGrammarList();
        speechRecognitionList.addFromString(grammar, 1);
        recognition.grammars = speechRecognitionList;
    }
    recognition.onresult = (e) => { $('#user-input').val(e.results[0][0].transcript); };
    recognition.onend = () => { $('#voice-input-btn').css('background', '#007bff').text('🎤'); };
    recognition.onerror = () => { $('#voice-input-btn').css('background', '#007bff').text('🎤'); };
}

// =======================================================
// 3. バイタル分析グラフ機能
// =======================================================

let vitalChart = null;

function clearVitalChart() {
    if (vitalChart) {
        vitalChart.destroy();
        vitalChart = null;
    }
    const canvas = document.getElementById('vitalChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
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
        options: {
            responsive: true,
            scales: {
                'y-temp': { type: 'linear', position: 'left', min: 34, max: 40 },
                'y-bp': { type: 'linear', position: 'right', min: 40, max: 200 }
            }
        }
    });
}

// =======================================================
// 4. 利用者・データ取得関連
// =======================================================

async function fetchClients() {
    try {
        const response = await axios.get('/web-api/clients');
        ['#client-select', '#record-client-select'].forEach(id => {
            const $el = $(id);
            $el.empty().append('<option value="">利用者を選択してください</option>');
            response.data.forEach(c => $el.append(`<option value="${c.id}">${c.id}: ${c.client_name}</option>`));
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
            <td style="padding:10px; border-bottom:1px solid #eee; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${r.content}</td>
            <td style="padding:10px; border-bottom:1px solid #eee; text-align:center;">${r.body_temp}℃ / ${r.blood_pressure_high}/${r.blood_pressure_low}</td>
            <td style="padding:10px; border-bottom:1px solid #eee; text-align:center;">
                <button type="button" class="select-record-btn" data-record='${JSON.stringify(r)}' style="background:#6c757d; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">編集</button>
            </td>
        </tr>`;
    });
    $('#modal-record-table-body').html(html);
}

// =======================================================
// 5. イベントハンドラ
// =======================================================

$(document).ready(function() {
    fetchClients();

    // --- 利用者登録フォーム関連 ---
    
    $('#search-zipcode').on('click', async function() {
        const zip = $('#reg-zipcode').val().replace('-', '');
        if (zip.length !== 7) { alert("7桁で入力してください"); return; }
        try {
            const res = await axios.get(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${zip}`);
            if (res.data.results) {
                const r = res.data.results[0];
                $('#reg-address').val(r.address1 + r.address2 + r.address3);
            }
        } catch (e) { alert("検索失敗"); }
    });

    $('#form-reset-btn').on('click', function() {
        if(confirm('入力内容をクリアしますか？')) {
            $('#client-register-form')[0].reset();
            $('#reg-client-id').val('').attr('readonly', false).css('background', '#fff');
            $('#client-delete-btn').hide();
            $('#client-submit-btn').text('情報を保存する').css('background', '#28a745');
        }
    });

    $('#client-delete-btn').on('click', async function() {
        const cid = $('#reg-client-id').val();
        if (!cid) return;
        if (confirm(`利用者ID: ${cid} を削除しますか？`)) {
            try {
                await axios.delete(`/web-api/clients/${cid}`);
                alert("削除完了");
                $('#form-reset-btn').click();
                fetchClients();
            } catch (e) { alert("削除失敗。記録が存在する可能性があります。"); }
        }
    });

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
            alert("保存完了");
            fetchClients();
            $('#form-reset-btn').click();
        } catch (e) { alert("保存失敗"); }
    });

    // --- ケア記録・バイタル保存処理 ---

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
            const res = await axios.post('/web-api/records', data);
            if (res.data.status === 'success') {
                alert("記録を保存しました");
                $('#record-add-form')[0].reset();
                $('#edit-record-id').val('');
                $('#record-submit-btn').text('記録を保存').css('background', '#6c757d');
                $('#record-reset-btn').hide();
                clearVitalChart();
            }
        } catch (e) { alert("保存失敗"); }
    });

    $('#record-reset-btn').on('click', function() {
        $('#record-add-form')[0].reset();
        $('#edit-record-id').val('');
        $(this).hide();
        $('#record-submit-btn').text('記録を保存').css('background', '#6c757d');
    });

    // --- バイタル分析・グラフ関連 ---

    $('#search-start-date, #search-end-date, #client-select').on('change', function() {
        clearVitalChart();
    });

    $('#update-graph-btn').on('click', async function() {
        const cid = $('#client-select').val();
        if (!cid) { alert("利用者を選択してください"); return; }
        const $btn = $(this);
        $btn.text('...').prop('disabled', true);
        try {
            const res = await axios.post('/web-api/ask-ai', {
                clientId: cid, question: '', 
                startDate: $('#search-start-date').val(),
                endDate: $('#search-end-date').val(),
                systemPrompt: 'データ取得'
            });
            if (res.data.vitalData && res.data.vitalData.length > 0) {
                updateVitalChart(res.data.vitalData);
            } else { alert("データなし"); }
        } catch (e) { alert("エラー"); }
        finally { $btn.text('表示').prop('disabled', false); }
    });

    $('.quick-date-btn').on('click', function() {
        const range = $(this).data('range');
        const end = new Date();
        let start = new Date();
        if (range === 'week') start.setDate(end.getDate() - 7);
        else if (range === 'month') start.setDate(1);
        $('#search-start-date').val(start.toISOString().split('T')[0]);
        $('#search-end-date').val(end.toISOString().split('T')[0]);
        clearVitalChart();
    });

    // --- 音声・チャット関連 ---

    $('#voice-read-toggle').on('change', function() {
        const isChecked = $(this).prop('checked');
        if (isChecked) {
            $('#toggle-bg').css('background-color', '#28a745');
            $('#toggle-circle').css('transform', 'translateX(22px)');
        } else {
            $('#toggle-bg').css('background-color', '#ccc');
            $('#toggle-circle').css('transform', 'translateX(0px)');
            window.speechSynthesis.cancel();
        }
    });

    $('#voice-input-btn').on('click', function() {
        if (recognition) {
            recognition.start();
            $(this).css('background', '#dc3545').text('●'); 
        }
    });

    $('#chat-form').on('submit', async function(e) {
        e.preventDefault();
        const q = $('#user-input').val();
        const cid = $('#client-select').val();
        appendMessage('user', q);
        $('#user-input').val('');
        appendMessage('ai', '分析中...');
        try {
            const res = await axios.post('/web-api/ask-ai', {
                clientId: cid, question: q,
                startDate: $('#search-start-date').val(),
                endDate: $('#search-end-date').val(),
                systemPrompt: SYSTEM_PROMPT
            });
            $('#chat-window .ai-message').last().remove();
            appendMessage('ai', res.data.answer);
            speakText(res.data.answer);
            if(cid && res.data.vitalData) updateVitalChart(res.data.vitalData);
        } catch (e) { 
            $('#chat-window .ai-message').last().remove();
            appendMessage('ai', '通信エラー'); 
        }
    });

    $('#chat-clear-btn').on('click', function() {
        if(confirm('クリアしますか？')) {
            $('#chat-window').empty();
            window.speechSynthesis.cancel();
        }
    });

    // --- モーダル・その他 ---

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
        $('#client-delete-btn').show();
        $('#client-submit-btn').text('情報を更新する').css('background', '#e67e22');
        $('#client-modal').fadeOut(200);
        $('html, body').animate({ scrollTop: $("#client-register-section").offset().top - 50 }, 500);
    });

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

    $('#open-client-modal').on('click', () => { renderModalClientList(); $('#client-modal').fadeIn(200); });
    $('#open-record-modal').on('click', () => { renderRecordList(); $('#record-modal').fadeIn(200); });
    $('#close-client-modal, #close-record-modal').on('click', function() { $('#client-modal, #record-modal').fadeOut(200); });
});