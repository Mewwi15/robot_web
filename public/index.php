<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Robot Control</title>
  <style>
    body{font-family:system-ui,Segoe UI,Arial;margin:20px;background:#0b1220;color:#e9eefc}
    .row{display:flex;gap:16px;flex-wrap:wrap}
    .card{background:#111a2e;border:1px solid #233055;border-radius:14px;padding:16px;min-width:280px}
    button{padding:10px 12px;border-radius:10px;border:1px solid #33456f;background:#1b2a4d;color:#e9eefc;cursor:pointer}
    button:hover{background:#243763}
    button.danger{background:#5b1f2a;border-color:#8a2c3d}
    button.danger:hover{background:#742636}
    .grid{display:grid;grid-template-columns:repeat(2,minmax(140px,1fr));gap:10px}
    code{background:#0b1220;padding:2px 6px;border-radius:8px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:8px;border-bottom:1px solid #233055;font-size:14px}
    .muted{color:#9fb0d6}
    .top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
    a{color:#9fb0d6}
  </style>
</head>
<body>
  <div class="top">
    <div>
      <h2 style="margin:0">Delivery Robot Dashboard</h2>
      <div class="muted" id="who">...</div>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
      <a href="admin_users.php">Manage Users</a>
      <button onclick="logout()">Logout</button>
    </div>
  </div>

  <div class="row" style="margin-top:14px">
    <div class="card">
      <h3>Control</h3>
      <div class="grid">
        <button onclick="enqueue(1)">ไปห้อง 1</button>
        <button onclick="enqueue(2)">ไปห้อง 2</button>
        <button onclick="enqueue(3)">ไปห้อง 3</button>
        <button onclick="enqueue(4)">ไปห้อง 4</button>
      </div>
      <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
        <button class="danger" onclick="setEstop(true)">E-STOP</button>
        <button onclick="setEstop(false)">ปลด E-STOP</button>
        <button class="danger" onclick="clearQueue()">Clear Queue</button>
      </div>
    </div>

    <div class="card">
      <h3>Status</h3>
      <div id="statusBox" class="muted">loading...</div>
      <div style="margin-top:10px" class="muted">
        Queue count: <span id="qCount">-</span><br/>
        E-STOP: <span id="estop">-</span><br/>
        Last seen: <span id="seen">-</span>
      </div>
    </div>

    <div class="card" style="flex:1;min-width:360px">
      <h3>Queue</h3>
      <table>
        <thead><tr><th>เวลา</th><th>ห้อง</th><th>สถานะ</th><th>ข้อความ</th></tr></thead>
        <tbody id="qBody"></tbody>
      </table>
    </div>
  </div>

<script>
function csrf(){ return localStorage.getItem('csrf') || ''; }

async function api(path, opts = {}) {
  const o = { method: opts.method || 'GET', headers: opts.headers || {} };
  if (opts.json) {
    o.headers['Content-Type'] = 'application/json';
    o.headers['X-CSRF-Token'] = csrf();
    o.body = JSON.stringify(opts.json);
  }
  const res = await fetch('api/' + path, o);
  const data = await res.json().catch(()=>({ok:false,error:'bad json'}));

  if (res.status === 401) { location.href = 'login.php'; throw new Error('login'); }
  if (!res.ok || data.ok === false) throw new Error(data.error || ('HTTP ' + res.status));
  return data;
}

async function initUser(){
  const me = await api('auth_me.php');
  if (!me.user) { location.href = 'login.php'; return; }
  document.getElementById('who').textContent = `User: ${me.user.username} (${me.user.role})`;
  if (me.csrf) localStorage.setItem('csrf', me.csrf);
}

async function logout(){
  await api('auth_logout.php', {method:'POST', json:{}});
  localStorage.removeItem('csrf');
  location.href = 'login.php';
}

async function enqueue(room){ await api('enqueue.php', {method:'POST', json:{room}}); await refreshAll(); }
async function clearQueue(){ await api('clear.php', {method:'POST', json:{}}); await refreshAll(); }
async function setEstop(enabled){ await api('estop.php', {method:'POST', json:{enabled}}); await refreshAll(); }

function fmt(v){ return v == null ? '-' : v; }

async function refreshStatus(){
  const s = await api('status.php');
  const st = s.status || {};
  document.getElementById('statusBox').innerHTML =
    `<div>Robot: <code>${fmt(st.name)}</code></div>
     <div>State: <code>${fmt(st.state)}</code></div>
     <div>Last target: <code>${fmt(st.last_target)}</code></div>
     <div>Active cmd: <code>${fmt(st.active_command_id)}</code></div>`;
  document.getElementById('qCount').textContent = s.queue_count ?? '-';
  document.getElementById('estop').textContent = (s.estop?.enabled ?? 0) ? 'ON' : 'OFF';
  document.getElementById('seen').textContent = st.last_seen_at ?? '-';
}

async function refreshQueue(){
  const q = await api('queue.php');
  const rows = q.queue || [];
  document.getElementById('qBody').innerHTML = rows.map(r => `
    <tr>
      <td>${r.created_at ?? '-'}</td>
      <td>${r.room ?? '-'}</td>
      <td>${r.status ?? '-'}</td>
      <td class="muted">${(r.message ?? '').toString()}</td>
    </tr>
  `).join('');
}

async function refreshAll(){
  try { await Promise.all([refreshStatus(), refreshQueue()]); } catch(e){ console.error(e); }
}

(async function(){
  await initUser();
  await refreshAll();
  setInterval(refreshAll, 1000);
})();
</script>
</body>
</html>
