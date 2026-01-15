<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Users</title>
  <style>
    body{font-family:system-ui,Segoe UI,Arial;margin:20px;background:#0b1220;color:#e9eefc}
    .card{background:#111a2e;border:1px solid #233055;border-radius:14px;padding:16px;max-width:900px}
    input,select{padding:10px;border-radius:10px;border:1px solid #33456f;background:#0b1220;color:#e9eefc}
    button{padding:10px 12px;border-radius:10px;border:1px solid #33456f;background:#1b2a4d;color:#e9eefc;cursor:pointer}
    table{width:100%;border-collapse:collapse;margin-top:10px}
    th,td{padding:8px;border-bottom:1px solid #233055;font-size:14px}
    .row{display:flex;gap:10px;flex-wrap:wrap}
    .muted{color:#9fb0d6}
    a{color:#9fb0d6}
  </style>
</head>
<body>
  <a href="index.php">← Back</a>
  <h2>Manage Users</h2>

  <div class="card">
    <h3>Create user</h3>
    <div class="row">
      <input id="u" placeholder="username" />
      <input id="p" placeholder="password (min 8)" />
      <select id="r">
        <option value="viewer">viewer</option>
        <option value="admin">admin</option>
      </select>
      <button onclick="createUser()">Create</button>
    </div>

    <h3 style="margin-top:18px">Users</h3>
    <div class="muted" id="msg"></div>
    <table>
      <thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Last login</th></tr></thead>
      <tbody id="tbody"></tbody>
    </table>

    <h3 style="margin-top:18px">Change password</h3>
    <div class="row">
      <input id="uid" placeholder="user_id" />
      <input id="np" placeholder="new password (min 8)" />
      <button onclick="changePass()">Change</button>
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

async function load(){
  const me = await api('auth_me.php');
  if (me.csrf) localStorage.setItem('csrf', me.csrf);
  const d = await api('users_list.php');
  document.getElementById('tbody').innerHTML = d.users.map(u=>`
    <tr><td>${u.id}</td><td>${u.username}</td><td>${u.role}</td><td>${u.last_login_at ?? '-'}</td></tr>
  `).join('');
}

async function createUser(){
  document.getElementById('msg').textContent = '';
  await api('users_create.php', {method:'POST', json:{
    username: document.getElementById('u').value.trim(),
    password: document.getElementById('p').value,
    role: document.getElementById('r').value
  }});
  document.getElementById('msg').textContent = 'Created';
  await load();
}

async function changePass(){
  document.getElementById('msg').textContent = '';
  await api('users_password.php', {method:'POST', json:{
    user_id: document.getElementById('uid').value,
    new_password: document.getElementById('np').value
  }});
  document.getElementById('msg').textContent = 'Password changed';
}
load();
</script>
</body>
</html>
