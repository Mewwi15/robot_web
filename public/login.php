<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Robot Control — Login</title>

  <style>
    :root{
      --bg0:#070A12;
      --bg1:#0B1020;
      --card: rgba(255,255,255,.08);
      --stroke: rgba(255,255,255,.14);
      --text: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.66);
      --shadow: 0 22px 70px rgba(0,0,0,.55);
      --radius: 22px;
      --accent1: #7C3AED; /* violet */
      --accent2: #06B6D4; /* cyan */
      --accent3: #22C55E; /* green */
      --danger: #FF4D6D;
      --warn: #FBBF24;
      --ok: #22C55E;
    }
    *{ box-sizing: border-box; }
    html,body{ height:100%; }
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Noto Sans Thai", "Helvetica Neue", Arial;
      color: var(--text);
      background:
        radial-gradient(1200px 700px at 20% 10%, rgba(124,58,237,.35), transparent 60%),
        radial-gradient(900px 600px at 80% 25%, rgba(6,182,212,.28), transparent 55%),
        radial-gradient(900px 600px at 55% 90%, rgba(34,197,94,.18), transparent 55%),
        linear-gradient(180deg, var(--bg0), var(--bg1));
      overflow-x:hidden;
    }

    .grid{
      min-height:100%;
      display:grid;
      grid-template-columns: 1.05fr .95fr;
    }

    /* Left side */
    .brand{
      padding: clamp(22px, 4vw, 44px);
      position:relative;
      display:flex;
      flex-direction:column;
      justify-content:space-between;
      gap: 24px;
    }
    .brand::before{
      content:"";
      position:absolute;
      inset: 16px;
      border-radius: 28px;
      background: linear-gradient(135deg, rgba(255,255,255,.07), rgba(255,255,255,.02));
      border:1px solid rgba(255,255,255,.08);
      pointer-events:none;
    }
    .brand > *{ position:relative; z-index:1; }

    .logo{
      display:flex;
      align-items:center;
      gap: 12px;
      user-select:none;
    }
    .mark{
      width:42px; height:42px;
      border-radius: 14px;
      background:
        radial-gradient(18px 18px at 30% 30%, rgba(255,255,255,.35), transparent 65%),
        linear-gradient(135deg, var(--accent1), var(--accent2));
      box-shadow: 0 12px 40px rgba(124,58,237,.25);
      position:relative;
      overflow:hidden;
    }
    .mark::after{
      content:"";
      position:absolute;
      inset:-30%;
      background: conic-gradient(from 180deg, rgba(255,255,255,.0), rgba(255,255,255,.35), rgba(255,255,255,.0));
      animation: spin 6s linear infinite;
      opacity:.35;
    }
    @keyframes spin{ to{ transform: rotate(360deg); } }

    .logo strong{ font-size: 18px; letter-spacing: .3px; }
    .logo span{ display:block; color: var(--muted); font-size: 12.5px; margin-top: 2px; }

    .hero h1{
      margin: 4px 0 8px;
      font-size: clamp(28px, 3.2vw, 44px);
      line-height: 1.05;
      letter-spacing: -0.8px;
    }
    .hero p{
      margin: 0;
      color: var(--muted);
      max-width: 52ch;
      line-height: 1.6;
      font-size: 14.5px;
    }
    .chips{ display:flex; flex-wrap:wrap; gap: 10px; margin-top: 16px; }
    .chip{
      border:1px solid rgba(255,255,255,.12);
      background: rgba(255,255,255,.06);
      padding: 8px 10px;
      border-radius: 999px;
      color: rgba(255,255,255,.78);
      font-size: 12.5px;
      backdrop-filter: blur(10px);
    }
    .footer{
      color: rgba(255,255,255,.50);
      font-size: 12px;
      line-height: 1.4;
    }

    /* Right side (form) */
    .panel{
      padding: clamp(18px, 3vw, 38px);
      display:flex;
      align-items:center;
      justify-content:center;
    }
    .card{
      width:min(460px, 100%);
      border-radius: var(--radius);
      background: linear-gradient(180deg, rgba(255,255,255,.10), rgba(255,255,255,.06));
      border:1px solid rgba(255,255,255,.12);
      box-shadow: var(--shadow);
      backdrop-filter: blur(14px);
      overflow:hidden;
      position:relative;
    }
    .card::before{
      content:"";
      position:absolute;
      inset:-120px -120px auto auto;
      width: 240px; height: 240px;
      background: radial-gradient(circle at 30% 30%, rgba(6,182,212,.30), transparent 60%);
      pointer-events:none;
    }
    .card::after{
      content:"";
      position:absolute;
      inset:auto auto -140px -140px;
      width: 280px; height: 280px;
      background: radial-gradient(circle at 40% 40%, rgba(124,58,237,.28), transparent 60%);
      pointer-events:none;
    }

    .card-header{ padding: 26px 26px 14px; position:relative; z-index:1; }
    .title{ margin:0; font-size: 22px; letter-spacing: -0.3px; }
    .subtitle{ margin: 8px 0 0; color: var(--muted); font-size: 13.5px; line-height:1.5; }

    form{ padding: 16px 26px 24px; position:relative; z-index:1; }
    .field{ margin-top: 14px; }
    label{
      display:flex;
      align-items:center;
      justify-content:space-between;
      font-size: 12.5px;
      color: rgba(255,255,255,.78);
      margin-bottom: 8px;
    }
    .hint{ color: rgba(255,255,255,.46); font-size: 12px; }

    .input{
      width:100%;
      display:flex;
      gap: 10px;
      align-items:center;
      padding: 12px 12px;
      border-radius: 14px;
      background: rgba(5,7,15,.55);
      border: 1px solid rgba(255,255,255,.12);
      transition: border-color .15s ease, transform .12s ease, box-shadow .15s ease;
    }
    .input:focus-within{
      border-color: rgba(6,182,212,.45);
      box-shadow: 0 0 0 4px rgba(6,182,212,.12);
      transform: translateY(-1px);
    }
    .input svg{ flex:0 0 auto; opacity:.80; }
    input{
      width:100%;
      border:0;
      outline: none;
      background: transparent;
      color: var(--text);
      font-size: 15px;
    }
    input::placeholder{ color: rgba(255,255,255,.40); }

    .icon-btn{
      border: 1px solid rgba(255,255,255,.14);
      background: rgba(255,255,255,.06);
      color: rgba(255,255,255,.85);
      padding: 7px 10px;
      border-radius: 12px;
      cursor:pointer;
      transition: transform .12s ease, filter .12s ease;
      flex: 0 0 auto;
      font-size: 12.5px;
      user-select:none;
    }
    .icon-btn:hover{ filter: brightness(1.05); transform: translateY(-1px); }
    .icon-btn:active{ transform: translateY(0px); }

    .row{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 12px;
      margin-top: 14px;
    }
    .check{
      display:flex;
      align-items:center;
      gap: 10px;
      user-select:none;
      color: rgba(255,255,255,.76);
      font-size: 13px;
    }
    .check input{ width: 16px; height: 16px; }

    .link{
      color: rgba(255,255,255,.75);
      text-decoration: none;
      font-size: 13px;
      border-bottom: 1px dashed rgba(255,255,255,.22);
    }
    .link:hover{ color: rgba(255,255,255,.92); }

    .btn{
      margin-top: 16px;
      width:100%;
      border:0;
      cursor:pointer;
      border-radius: 16px;
      padding: 12px 14px;
      font-weight: 700;
      letter-spacing: .2px;
      font-size: 15px;
      color: rgba(255,255,255,.94);
      background: linear-gradient(135deg, var(--accent1), var(--accent2));
      box-shadow: 0 16px 45px rgba(6,182,212,.18);
      transition: transform .12s ease, filter .12s ease;
    }
    .btn:hover{ filter: brightness(1.06); transform: translateY(-1px); }
    .btn:active{ transform: translateY(0px); }
    .btn[disabled]{ opacity:.7; cursor:not-allowed; transform:none; }

    .msg{
      margin-top: 14px;
      border-radius: 14px;
      padding: 10px 12px;
      font-size: 13px;
      line-height: 1.35;
      border:1px solid rgba(255,255,255,.12);
      background: rgba(255,255,255,.06);
      color: rgba(255,255,255,.84);
      white-space: pre-wrap;
      display:none;
    }
    .msg.show{ display:block; }
    .msg.ok{
      border-color: rgba(34,197,94,.35);
      background: rgba(34,197,94,.12);
    }
    .msg.err{
      border-color: rgba(255,77,109,.35);
      background: rgba(255,77,109,.12);
    }

    .tiny{
      margin-top: 14px;
      color: rgba(255,255,255,.52);
      font-size: 12px;
      line-height: 1.4;
    }

    .divider{
      margin-top: 16px;
      height: 1px;
      background: rgba(255,255,255,.10);
    }

    .statusbar{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      margin-top: 12px;
      color: rgba(255,255,255,.62);
      font-size: 12.5px;
    }
    .dot{
      width:8px; height:8px; border-radius:999px;
      background: rgba(255,255,255,.25);
      box-shadow: 0 0 0 3px rgba(255,255,255,.06);
      flex: 0 0 auto;
    }
    .dot.ok{ background: rgba(34,197,94,.9); box-shadow: 0 0 0 3px rgba(34,197,94,.12); }
    .dot.err{ background: rgba(255,77,109,.9); box-shadow: 0 0 0 3px rgba(255,77,109,.12); }

    @media (max-width: 960px){
      .grid{ grid-template-columns: 1fr; }
      .brand{ display:none; }
      .panel{ padding: 18px; }
    }
  </style>
</head>

<body>
  <div class="grid">

    <section class="brand">
      <div class="logo">
        <div class="mark" aria-hidden="true"></div>
        <div>
          <strong>Robot Control</strong>
          <span>Delivery Robot Dashboard</span>
        </div>
      </div>

      <div class="hero">
        <h1>เข้าสู่ระบบเพื่อควบคุมหุ่นยนต์</h1>
        <p>
          ระบบควบคุมหุ่นยนต์ส่งของแบบเรียลไทม์: ตรวจสอบสถานะ, ส่งคำสั่ง, และจัดการผู้ใช้งานอย่างปลอดภัย
          ผ่าน Session + API.
        </p>
        <div class="chips">
          <div class="chip">Secure Session</div>
          <div class="chip">Role-based Access</div>
          <div class="chip">ESP32 Ready</div>
          <div class="chip">MySQL + PHP</div>
        </div>
      </div>

      <div class="footer">
        <div>Tip: หากยังไม่ได้สร้างบัญชี ให้ใส่ user ในตาราง `users` ก่อน</div>
        <div style="margin-top:6px;">© <?php echo date('Y'); ?> Robot Web</div>
      </div>
    </section>

    <main class="panel">
      <div class="card">
        <div class="card-header">
          <h2 class="title">Login</h2>
          <p class="subtitle">กรอกชื่อผู้ใช้และรหัสผ่านเพื่อเข้าสู่ระบบ</p>
        </div>

        <form id="form" autocomplete="on" novalidate>
          <div class="field">
            <label for="username">Username <span class="hint">เช่น admin</span></label>
            <div class="input">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M20 21a8 8 0 10-16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
              </svg>
              <input id="username" name="username" autocomplete="username" placeholder="กรอก username" required />
            </div>
          </div>

          <div class="field">
            <label for="password">Password <span class="hint">อย่างน้อย 4 ตัวอักษร</span></label>
            <div class="input">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M7 10V8a5 5 0 0110 0v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
              </svg>
              <input id="password" name="password" type="password" autocomplete="current-password" placeholder="กรอกรหัสผ่าน" required minlength="4" />
              <button class="icon-btn" id="togglePw" type="button" aria-label="แสดง/ซ่อนรหัสผ่าน">Show</button>
            </div>
          </div>

          <div class="row">
            <label class="check">
              <input id="remember" type="checkbox" />
              จำฉันไว้ในเครื่องนี้
            </label>
            <a class="link" href="#" onclick="return false;" title="สามารถทำหน้าลืมรหัสผ่านเพิ่มทีหลังได้">ลืมรหัสผ่าน?</a>
          </div>

          <button class="btn" id="btn" type="submit">เข้าสู่ระบบ</button>

          <div id="msg" class="msg" role="status" aria-live="polite"></div>

          <div class="divider"></div>

          <div class="statusbar">
            <div style="display:flex; align-items:center; gap:10px;">
              <div id="apiDot" class="dot"></div>
              <div id="apiText">กำลังตรวจสอบระบบ…</div>
            </div>
            <a class="link" href="./" title="กลับหน้าแรก">หน้าแรก</a>
          </div>

          <div class="tiny">
            หากเข้าสู่ระบบไม่ได้ ให้ตรวจสอบว่า MySQL ทำงานอยู่ และ API ตอบกลับเป็น JSON
          </div>
        </form>
      </div>
    </main>

  </div>

  <script>
    // =========================
    // Config
    // =========================
    const API_BASE = './api';                // ถ้า API อยู่ที่อื่น เปลี่ยนตรงนี้
    const LOGIN_URL = `${API_BASE}/auth_login.php`;
    const ME_URL    = `${API_BASE}/auth_me.php`;

    // เป้าหมายหลัง login สำเร็จ (แก้เป็นหน้า dashboard ของคุณ)
    const REDIRECT_TO = './dashboard.php';

    // =========================
    // Helpers
    // =========================
    const $ = (sel) => document.querySelector(sel);

    function setMsg(type, text) {
      const el = $('#msg');
      el.classList.remove('ok','err','show');
      if (!text) return;
      el.textContent = text;
      el.classList.add('show', type === 'ok' ? 'ok' : 'err');
    }

    function setBusy(isBusy) {
      const btn = $('#btn');
      btn.disabled = isBusy;
      btn.textContent = isBusy ? 'กำลังเข้าสู่ระบบ…' : 'เข้าสู่ระบบ';
    }

    function setApiStatus(ok, text) {
      const dot = $('#apiDot');
      dot.classList.remove('ok','err');
      if (ok === true) dot.classList.add('ok');
      if (ok === false) dot.classList.add('err');
      $('#apiText').textContent = text || '';
    }

    async function safeJson(res) {
      const ct = (res.headers.get('content-type') || '').toLowerCase();
      const txt = await res.text();
      if (!ct.includes('application/json')) {
        return { __not_json: true, __raw: txt, __status: res.status };
      }
      try { return JSON.parse(txt); }
      catch { return { __bad_json: true, __raw: txt, __status: res.status }; }
    }

    function saveRememberedUsername(username, remember) {
      try {
        if (remember) localStorage.setItem('robot_remember_user', username);
        else localStorage.removeItem('robot_remember_user');
      } catch {}
    }

    function loadRememberedUsername() {
      try { return localStorage.getItem('robot_remember_user') || ''; }
      catch { return ''; }
    }

    // =========================
    // UI interactions
    // =========================
    $('#togglePw').addEventListener('click', () => {
      const pw = $('#password');
      const isPw = pw.type === 'password';
      pw.type = isPw ? 'text' : 'password';
      $('#togglePw').textContent = isPw ? 'Hide' : 'Show';
      pw.focus();
    });

    // Prefill remembered username
    const remembered = loadRememberedUsername();
    if (remembered) {
      $('#username').value = remembered;
      $('#remember').checked = true;
      $('#password').focus();
    } else {
      $('#username').focus();
    }

    // =========================
    // Check API/session on load
    // =========================
    (async () => {
      try {
        const res = await fetch(ME_URL, { credentials: 'include' });
        const data = await safeJson(res);

        if (data && data.ok) {
          setApiStatus(true, `เข้าสู่ระบบอยู่แล้ว (${data.user?.username || 'user'})`);
          setMsg('ok', 'คุณเข้าสู่ระบบอยู่แล้ว (จะไม่ redirect อัตโนมัติ)');
          // ไม่ redirect
          return;
        }


        if (data && (data.__not_json || data.__bad_json)) {
          setApiStatus(false, 'API ตอบกลับไม่ใช่ JSON');
          return;
        }

        setApiStatus(true, 'พร้อมให้เข้าสู่ระบบ');
      } catch (e) {
        setApiStatus(false, 'เชื่อมต่อ API ไม่ได้');
      }
    })();

    // =========================
    // Submit login
    // =========================
    $('#form').addEventListener('submit', async (ev) => {
      ev.preventDefault();
      setMsg('', '');

      const username = ($('#username').value || '').trim();
      const password = ($('#password').value || '').trim();
      const remember = $('#remember').checked;

      if (!username || !password) {
        setMsg('err', 'กรุณากรอก Username และ Password');
        return;
      }

      setBusy(true);

      try {
        // ส่งแบบ JSON ก่อน
        let res = await fetch(LOGIN_URL, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include',
          body: JSON.stringify({ username, password })
        });

        let data = await safeJson(res);

        // ถ้า API ฝั่งคุณรองรับแค่ form-encoded ให้ fallback
        if (data && (data.__not_json || data.__bad_json)) {
          const form = new URLSearchParams();
          form.set('username', username);
          form.set('password', password);

          res = await fetch(LOGIN_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            credentials: 'include',
            body: form.toString()
          });

          data = await safeJson(res);
        }

        if (data && data.ok) {
          saveRememberedUsername(username, remember);
          setMsg('ok', 'เข้าสู่ระบบสำเร็จ กำลังพาไปหน้าแดชบอร์ด…');
          setTimeout(() => window.location.href = REDIRECT_TO, 650);
          return;
        }

        // Error cases
        if (data && data.__not_json) {
          setMsg('err', `ไม่ใช่ JSON (HTTP ${data.__status})\n\n${(data.__raw || '').slice(0, 600)}`);
          return;
        }
        if (data && data.__bad_json) {
          setMsg('err', `JSON ไม่ถูกต้อง (HTTP ${data.__status})\n\n${(data.__raw || '').slice(0, 600)}`);
          return;
        }

        const errText =
          (data && (data.error || data.message)) ||
          `เข้าสู่ระบบไม่สำเร็จ (HTTP ${res.status})`;

        setMsg('err', errText);
      } catch (e) {
        setMsg('err', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้ กรุณาลองใหม่');
      } finally {
        setBusy(false);
      }
    });
  </script>
</body>
</html>
