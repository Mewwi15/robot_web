<?php
declare(strict_types=1);

session_start();
// TODO: เช็กว่า login แล้วค่อยให้เข้า (คุณน่าจะมีอยู่แล้ว)

$cfgPathLocal = __DIR__ . '/../private/config.local.php';
$cfgPathExample = __DIR__ . '/../private/config.example.php';

$cfg = null;
if (file_exists($cfgPathLocal)) {
    $cfg = require $cfgPathLocal;
} else {
    $cfg = require $cfgPathExample;
}

// ส่งเฉพาะค่าที่จำเป็นให้ JS
$mqttPublic = [
    'url' => $cfg['mqtt']['url'] ?? '',
    'username' => $cfg['mqtt']['username'] ?? '',
    'robot_id' => $cfg['mqtt']['robot_id'] ?? 'rb01',
];

// หมายเหตุ: password จะส่งไป JS อยู่ดีเพราะต้อง connect จาก browser
// แต่ “ไม่อยู่ใน GitHub” แล้ว (ความปลอดภัยเพิ่มอีกขั้นควรใช้ backend token ในอนาคต)
$mqttPassword = (string) ($cfg['mqtt']['password'] ?? '');
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="<?= htmlspecialchars((string) ($csrf ?? ''), ENT_QUOTES, 'UTF-8') ?>">
    <title>Robot Operator</title>

    <style>
        :root {
            --bg0: #070815;
            --bg1: #0b1226;
            --card: rgba(255, 255, 255, .06);
            --stroke: rgba(255, 255, 255, .12);
            --stroke2: rgba(255, 255, 255, .18);

            --text: #f6f7ff;
            --muted: rgba(246, 247, 255, .72);
            --muted2: rgba(246, 247, 255, .55);

            --a: #7c3aed;
            --b: #06b6d4;
            --ok: #22c55e;
            --warn: #f59e0b;
            --bad: #ff4d6d;

            --shadow: 0 18px 55px rgba(0, 0, 0, .50);
            --r: 20px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: system-ui, "Noto Sans Thai", "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(1100px 700px at 12% 8%, rgba(124, 58, 237, .26), transparent 60%),
                radial-gradient(1100px 700px at 86% 0%, rgba(6, 182, 212, .22), transparent 55%),
                linear-gradient(180deg, var(--bg0), var(--bg1));
            overflow-x: hidden;
        }

        .wrap {
            max-width: 1080px;
            margin: 0 auto;
            padding: 18px;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(12px);
            background: linear-gradient(180deg, rgba(7, 8, 21, .80), rgba(7, 8, 21, .40));
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: var(--r);
            box-shadow: var(--shadow);
            padding: 14px;
        }

        .top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 44px;
            height: 44px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(124, 58, 237, .98), rgba(6, 182, 212, .98));
            display: grid;
            place-items: center;
            font-weight: 1000;
            letter-spacing: .5px;
            box-shadow: 0 14px 30px rgba(124, 58, 237, .24);
        }

        h1 {
            margin: 0;
            font-size: 15px;
            letter-spacing: .2px;
        }

        .sub {
            margin: 3px 0 0;
            font-size: 12px;
            color: var(--muted);
        }

        .controls {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        select,
        button,
        a.btn {
            appearance: none;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid var(--stroke);
            background: rgba(255, 255, 255, .06);
            color: var(--text);
            font-size: 14px;
            outline: none;
        }

        select:focus,
        button:focus,
        a.btn:focus {
            border-color: var(--stroke2);
            box-shadow: 0 0 0 3px rgba(6, 182, 212, .16);
        }

        button {
            cursor: pointer;
            user-select: none;
            transition: transform .08s ease;
        }

        button:active {
            transform: translateY(1px);
        }

        button[disabled] {
            opacity: .55;
            cursor: not-allowed;
        }

        .btn-primary {
            border: 0;
            background: linear-gradient(135deg, var(--a), var(--b));
            font-weight: 1000;
        }

        .btn-danger {
            border: 0;
            background: linear-gradient(135deg, var(--bad), #f97316);
            font-weight: 1000;
        }

        a.btn {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 999px;
            border: 1px solid var(--stroke);
            background: rgba(255, 255, 255, .04);
            color: var(--muted);
            font-size: 12px;
            white-space: nowrap;
        }

        .dot {
            width: 9px;
            height: 9px;
            border-radius: 99px;
            background: rgba(245, 247, 255, .25);
            box-shadow: 0 0 0 3px rgba(245, 247, 255, .06);
        }

        .dot.ok {
            background: var(--ok);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, .16);
        }

        .dot.bad {
            background: var(--bad);
            box-shadow: 0 0 0 3px rgba(255, 77, 109, .16);
        }

        .dot.warn {
            background: var(--warn);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .16);
        }

        .grid {
            display: grid;
            grid-template-columns: 1.15fr .85fr;
            gap: 12px;
            margin-top: 12px;
        }

        .panel {
            border-radius: var(--r);
            border: 1px solid var(--stroke);
            background: var(--card);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .panel-h {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .panel-h h2 {
            margin: 0;
            font-size: 14px;
            letter-spacing: .2px;
        }

        .panel-b {
            padding: 12px 14px;
        }

        .statusBox {
            border: 1px solid rgba(255, 255, 255, .12);
            background: linear-gradient(180deg, rgba(255, 255, 255, .08), rgba(255, 255, 255, .04));
            border-radius: 20px;
            padding: 14px;
        }

        .big {
            font-size: 28px;
            font-weight: 1000;
            letter-spacing: .3px;
            margin: 2px 0 8px;
        }

        .meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            color: var(--muted);
            font-size: 12px;
        }

        .rooms {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .roomBtn {
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(0, 0, 0, .22);
            padding: 12px;
        }

        .roomBtn .t {
            font-weight: 900;
        }

        .roomBtn .d {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .roomBtn button {
            width: 100%;
            margin-top: 10px;
            padding: 12px 12px;
            border-radius: 14px;
            border: 0;
            font-weight: 1000;
            background: linear-gradient(135deg, rgba(124, 58, 237, .98), rgba(6, 182, 212, .98));
        }

        .roomBtn button.park {
            background: linear-gradient(135deg, rgba(34, 197, 94, .98), rgba(6, 182, 212, .98));
        }

        .tools {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .debug {
            margin-top: 10px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, .10);
            background: rgba(0, 0, 0, .25);
            padding: 12px;
            font-size: 12px;
            color: rgba(246, 247, 255, .88);
            white-space: pre-wrap;
            min-height: 140px;
            max-height: 320px;
            overflow: auto;
            display: none;
        }

        details[open] .debug {
            display: block;
        }

        @media (max-width: 980px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .rooms {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="topbar">
            <div class="top">
                <div class="brand">
                    <div class="logo">RB</div>
                    <div>
                        <h1>Robot Operator</h1>
                        <div class="sub">แสดง “อยู่ ROOM X” + ส่งคำสั่งไปห้อง</div>
                    </div>
                </div>

                <div class="controls">
                    <span class="pill" id="connPill">
                        <span class="dot warn" id="connDot"></span>
                        <span id="connText">CONNECTING…</span>
                    </span>

                    <select id="robotSel" title="Robot">
                        <option value="rb01">rb01</option>
                    </select>

                    <button id="reconnectBtn" type="button">Reconnect</button>
                    <button class="btn-danger" id="stopBtn" type="button">STOP</button>
                    <a class="btn" href="#" id="logoutBtn">Logout</a>
                    <script>
                        document.getElementById('logoutBtn').addEventListener('click', async (e) => {
                            e.preventDefault();
                            await fetch('/robot_web/public/api/auth_logout.php', { method: 'POST', credentials: 'same-origin' });
                            location.href = '/robot_web/public/login.php';
                        });
                    </script>

                </div>
            </div>
        </div>

        <div class="grid">
            <!-- LEFT -->
            <div class="panel">
                <div class="panel-h">
                    <h2>Status</h2>
                    <span class="pill">
                        <span class="dot" id="statusDot"></span>
                        <span id="statusText">—</span>
                    </span>
                </div>
                <div class="panel-b">
                    <div class="statusBox">
                        <div class="big" id="roomText">อยู่ ROOM —</div>
                        <div class="meta">
                            <span id="onlineText">—</span>
                            <span>•</span>
                            <span id="lastSeen">last seen: —</span>
                            <span>•</span>
                            <span id="ledText">LED: —</span>
                        </div>
                    </div>

                    <div class="rooms">
                        <div class="roomBtn">
                            <div class="t">ROOM 1</div>
                            <div class="d">ไปห้อง 1</div>
                            <button type="button" data-room="1">ไป ROOM 1</button>
                        </div>
                        <div class="roomBtn">
                            <div class="t">ROOM 2</div>
                            <div class="d">ไปห้อง 2</div>
                            <button type="button" data-room="2">ไป ROOM 2</button>
                        </div>
                        <div class="roomBtn">
                            <div class="t">ROOM 3</div>
                            <div class="d">ไปห้อง 3</div>
                            <button type="button" data-room="3">ไป ROOM 3</button>
                        </div>
                        <div class="roomBtn">
                            <div class="t">PARK</div>
                            <div class="d">กลับที่จอด</div>
                            <button class="park" type="button" data-room="4">กลับ PARK</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="panel">
                <div class="panel-h">
                    <h2>Tools</h2>
                    <span class="pill"><span class="dot warn" id="safetyDot"></span><span id="safetyText">Safety:
                            ready</span></span>
                </div>
                <div class="panel-b">
                    <div class="tools">
                        <button class="btn-primary" id="pingBtn" type="button">PING</button>
                        <button id="ledOnBtn" type="button">LED ON</button>
                        <button id="ledOffBtn" type="button">LED OFF</button>
                        <button id="clearDebugBtn" type="button">Clear debug</button>
                    </div>

                    <details style="margin-top:12px;">
                        <summary style="cursor:pointer; color:var(--muted);">Debug (สำหรับช่าง/ทดสอบ)</summary>
                        <div class="debug" id="debugLog">—</div>
                    </details>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <script>
        (() => {
            const $ = (q) => document.querySelector(q);

            // ========= CONFIG (แก้ให้ตรง HiveMQ Cloud ของคุณ) =========
            const MQTT_CFG = {
                url: 'wss://fd15bc00ad9b496daa47e8753a621692.s1.eu.hivemq.cloud:8884/mqtt',
                username: 'web_user',
                password: 'Web123456789',
                keepaliveSec: 30,
                connectTimeoutMs: 8000,
                qosCmd: 1,
                qosStatus: 1,
                staleMs: 15000,
                backoffBaseMs: 1000,
                backoffMaxMs: 30000,
            };

            const COOLDOWN_MS = 700;

            // ========= DOM =========
            const connDot = $('#connDot');
            const connText = $('#connText');

            const statusDot = $('#statusDot');
            const statusText = $('#statusText');

            const roomText = $('#roomText');
            const onlineText = $('#onlineText');
            const lastSeen = $('#lastSeen');
            const ledText = $('#ledText');

            const robotSel = $('#robotSel');

            const reconnectBtn = $('#reconnectBtn');
            const stopBtn = $('#stopBtn');
            const pingBtn = $('#pingBtn');
            const ledOnBtn = $('#ledOnBtn');
            const ledOffBtn = $('#ledOffBtn');
            const clearDebugBtn = $('#clearDebugBtn');

            const safetyDot = $('#safetyDot');
            const safetyText = $('#safetyText');

            const debugLog = $('#debugLog');

            // ========= UI helpers =========
            function setDot(dotEl, state) {
                if (!dotEl) return;
                dotEl.className = 'dot ' + (state === 'ok' ? 'ok' : state === 'bad' ? 'bad' : state === 'warn' ? 'warn' : '');
            }
            function setConn(state, text) {
                setDot(connDot, state);
                if (connText) connText.textContent = text;
                if (onlineText) onlineText.textContent = (state === 'ok') ? 'ONLINE' : (state === 'warn') ? 'CONNECTING' : 'OFFLINE';
            }
            function setStatus(state, text) {
                setDot(statusDot, state);
                if (statusText) statusText.textContent = text;
            }
            function log(line) {
                if (!debugLog) return;
                const ts = new Date().toLocaleString();
                debugLog.textContent = `[${ts}] ${line}\n` + (debugLog.textContent || '');
            }
            function safeParseJson(text) {
                try { return JSON.parse(text); } catch { return null; }
            }
            function formatRoom(room) {
                if (room === null || room === undefined || room === '') return '—';
                const n = Number(room);
                if (Number.isFinite(n) && n === 4) return 'PARK';
                if (Number.isFinite(n)) return `ROOM ${n}`;
                return `ROOM ${String(room)}`;
            }

            function getRobotId() {
                return String(robotSel?.value || 'rb01').trim() || 'rb01';
            }
            function topics(robotId) {
                const rid = String(robotId).trim();
                return {
                    cmd: `robots/${rid}/cmd`,
                    status: `robots/${rid}/status`,
                };
            }

            // ========= MQTT manager =========
            let client = null;
            let connecting = false;
            let reconnectTimer = null;
            let backoffMs = MQTT_CFG.backoffBaseMs;
            let manualClose = false; // << เพิ่ม


            let lastStatusAt = 0;
            let staleTimer = null;

            let lastClickAt = 0;
            function cooldownOk() {
                const now = Date.now();
                if (now - lastClickAt < COOLDOWN_MS) return false;
                lastClickAt = now;
                return true;
            }

            function clearReconnect() {
                if (reconnectTimer) clearTimeout(reconnectTimer);
                reconnectTimer = null;
            }

            function startStaleWatch() {
                if (staleTimer) clearInterval(staleTimer);
                staleTimer = setInterval(() => {
                    if (!lastStatusAt) return;
                    const age = Date.now() - lastStatusAt;
                    if (age > MQTT_CFG.staleMs) {
                        setConn('warn', 'STALE');
                        setStatus('warn', `stale ${Math.round(age / 1000)}s`);
                    }
                }, 1000);
            }

            function scheduleReconnect(reason) {
                clearReconnect();
                const wait = backoffMs;
                backoffMs = Math.min(backoffMs * 2, MQTT_CFG.backoffMaxMs);
                setConn('warn', 'RECONNECTING…');
                log(`reconnect in ${wait}ms (${reason || 'unknown'})`);
                reconnectTimer = setTimeout(connect, wait);
            }

            function disconnect() {
                manualClose = true;
                clearReconnect();

                if (staleTimer) clearInterval(staleTimer);
                staleTimer = null;
                lastStatusAt = 0;

                try {
                    if (client) {
                        client.removeAllListeners(); // สำคัญ กัน event เก่าทำงานต่อ
                        client.end(true);
                    }
                } catch { }

                client = null;
                connecting = false;

                setConn('bad', 'OFFLINE');
                setStatus('bad', 'disconnected');
            }


            function connect() {
                if (connecting) return;

                manualClose = false;
                connecting = true;

                setConn('warn', 'CONNECTING…');
                setStatus('warn', '—');

                try {
                    if (client) {
                        client.removeAllListeners();
                        client.end(true);
                    }
                } catch { }
                client = null;

                const rid = getRobotId();
                const t = topics(rid);
                const clientId = 'web_' + crypto.getRandomValues(new Uint32Array(1))[0].toString(16);

                client = mqtt.connect(MQTT_CFG.url, {
                    username: MQTT_CFG.username,
                    password: MQTT_CFG.password,
                    clientId,
                    clean: true,
                    keepalive: MQTT_CFG.keepaliveSec,
                    connectTimeout: MQTT_CFG.connectTimeoutMs,
                    reconnectPeriod: 0,
                });

                client.on('connect', () => {
                    connecting = false;
                    backoffMs = MQTT_CFG.backoffBaseMs;

                    setConn('ok', 'ONLINE');

                    client.subscribe(t.status, { qos: MQTT_CFG.qosStatus }, (err) => {
                        if (err) {
                            log('subscribe error: ' + err.message);
                            scheduleReconnect('subscribe_failed');
                            return;
                        }
                        setStatus('ok', 'listening');
                        log('subscribed: ' + t.status);
                        startStaleWatch();
                    });
                });

                client.on('close', () => {
                    connecting = false;
                    if (manualClose) {
                        log('closed (manual)');
                        return;
                    }
                    setConn('warn', 'DISCONNECTED');
                    setStatus('warn', '—');
                    scheduleReconnect('close');
                });

                client.on('error', (e) => {
                    connecting = false; // กันค้าง
                    log('error: ' + (e?.message || e));
                    setConn('warn', 'ERROR');

                    // บางเคส error ไม่ตามด้วย close ให้จัดการเอง
                    if (!manualClose) {
                        try { client?.end(true); } catch { }
                        scheduleReconnect('error');
                    }
                });
            }


            function publishCmd(obj) {
                if (!client?.connected) throw new Error('mqtt_not_connected');

                const rid = getRobotId();
                const t = topics(rid);
                client.publish(t.cmd, JSON.stringify(obj), { qos: MQTT_CFG.qosCmd }, (err) => {
                    if (err) log('publish error: ' + err.message);
                    else log('published: ' + JSON.stringify(obj));
                });
            }

            function sendGoto(room) {
                if (!cooldownOk()) return;
                publishCmd({
                    v: 1,
                    cmd_id: crypto.randomUUID(),
                    cmd: 'goto',
                    room: Number(room),
                    ts: Date.now(),
                });
                setStatus('ok', `sent goto ${formatRoom(room)}`);
            }

            function sendStopConfirm() {
                // ป้องกันกดพลาด: ต้องกด 2 ครั้งภายใน 3 วินาที
                const now = Date.now();
                if (!sendStopConfirm._t || (now - sendStopConfirm._t) > 3000) {
                    sendStopConfirm._t = now;
                    setDot(safetyDot, 'warn');
                    if (safetyText) safetyText.textContent = 'Press STOP again to confirm';
                    setTimeout(() => {
                        setDot(safetyDot, 'warn');
                        if (safetyText) safetyText.textContent = 'Safety: ready';
                    }, 3200);
                    return;
                }

                if (!cooldownOk()) return;
                publishCmd({ v: 1, cmd_id: crypto.randomUUID(), cmd: 'stop', ts: Date.now() });
                setDot(safetyDot, 'bad');
                if (safetyText) safetyText.textContent = 'STOP sent';
                setStatus('warn', 'STOP sent');
                sendStopConfirm._t = 0;
            }
            sendStopConfirm._t = 0;

            function sendPing() {
                if (!cooldownOk()) return;
                publishCmd({ v: 1, cmd_id: crypto.randomUUID(), cmd: 'ping', ts: Date.now() });
                setStatus('ok', 'ping sent');
            }

            function sendLed(value) {
                if (!cooldownOk()) return;
                publishCmd({ v: 1, cmd_id: crypto.randomUUID(), cmd: 'led', value: String(value), ts: Date.now() });
                setStatus('ok', `led ${value} sent`);
            }

            // ========= events =========
            reconnectBtn?.addEventListener('click', () => { disconnect(); connect(); });
            clearDebugBtn?.addEventListener('click', () => { if (debugLog) debugLog.textContent = ''; });

            stopBtn?.addEventListener('click', () => {
                try { sendStopConfirm(); } catch (e) { log('STOP failed: ' + (e?.message || e)); connect(); }
            });

            pingBtn?.addEventListener('click', () => {
                try { sendPing(); } catch (e) { log('PING failed: ' + (e?.message || e)); connect(); }
            });

            ledOnBtn?.addEventListener('click', () => {
                try { sendLed('on'); } catch (e) { log('LED ON failed: ' + (e?.message || e)); connect(); }
            });
            ledOffBtn?.addEventListener('click', () => {
                try { sendLed('off'); } catch (e) { log('LED OFF failed: ' + (e?.message || e)); connect(); }
            });

            document.querySelectorAll('button[data-room]').forEach(btn => {
                btn.addEventListener('click', () => {
                    try {
                        const room = Number(btn.getAttribute('data-room'));
                        sendGoto(room);
                    } catch (e) {
                        log('GOTO failed: ' + (e?.message || e));
                        connect();
                    }
                });
            });

            robotSel?.addEventListener('change', () => {
                // reset UI
                if (roomText) roomText.textContent = 'อยู่ ROOM —';
                if (ledText) ledText.textContent = 'LED: —';
                if (lastSeen) lastSeen.textContent = 'last seen: —';
                setStatus('warn', '—');
                disconnect();
                connect();
            });

            // ========= boot =========
            setConn('warn', 'CONNECTING…');
            setStatus('warn', '—');
            setDot(safetyDot, 'warn');
            if (safetyText) safetyText.textContent = 'Safety: ready';
            connect();
        })();
    </script>

    <script>
        const MQTT_CFG = {
            url: <?= json_encode($mqttPublic['url'], JSON_UNESCAPED_SLASHES) ?>,
            username: <?= json_encode($mqttPublic['username']) ?>,
            password: <?= json_encode($mqttPassword) ?>,
            robotId: <?= json_encode($mqttPublic['robot_id']) ?>,

            qosCmd: 1,
            qosStatus: 1,
            keepaliveSec: 30,
            connectTimeoutMs: 8000,
            backoffBaseMs: 800,
            backoffMaxMs: 8000,
        };
    </script>

</body>

</html>