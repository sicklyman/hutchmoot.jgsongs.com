<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reflection Wall — Hutchmoot</title>
<style>
:root {
  --bg:        #0f0f0d;
  --panel:     #1a1a18;
  --text:      #faf8f5;
  --muted:     #888;
  --accent:    #c4832a;
  --accent-dim:#7a5021;
  --div:       #2a2a28;
  --font:      'Georgia', serif;
  --sans:      system-ui, -apple-system, sans-serif;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { width: 100%; height: 100%; background: var(--bg); color: var(--text); font-family: var(--font); overflow: hidden; }

/* ── Main layout ── */
#main { display: grid; grid-template-columns: 24% 76%; height: 100vh; }

/* ── Left: QR ── */
#left {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 2.5rem 2rem; border-right: 1px solid var(--div); gap: 1.25rem;
}
#qr-wrap {
  padding: 1rem; background: var(--bg);
  border-radius: 12px; border: 2px solid var(--div);
  line-height: 0;
}
#qr-img { width: min(18vw, 200px); height: auto; }
.qr-url  { font-family: var(--sans); font-size: clamp(.85rem, 1.4vw, 1.1rem); color: var(--accent); letter-spacing: .03em; text-align: center; }
.qr-cta  { font-family: var(--sans); font-size: clamp(.75rem, 1.1vw, .9rem); color: var(--muted); text-align: center; }

/* ── Right: feed ── */
#right { display: flex; flex-direction: column; padding: 3rem 3rem 1.5rem; overflow: hidden; }
.session-chip {
  display: inline-block; background: var(--accent-dim); color: var(--accent);
  font-family: var(--sans); font-size: .75rem; font-weight: 600; letter-spacing: .08em;
  text-transform: uppercase; border-radius: 4px; padding: .25rem .6rem;
  margin-bottom: .75rem; opacity: 0; transition: opacity .3s;
}
.session-chip.on { opacity: 1; }
#prompt {
  font-size: clamp(2rem, 3.8vw, 3.2rem); line-height: 1.2; margin-bottom: 1.75rem;
  opacity: 0; transition: opacity .4s;
}
#prompt.on { opacity: 1; }
#idle-msg { font-size: clamp(1.2rem, 2.5vw, 1.8rem); color: var(--muted); margin-top: 2rem; }

#feed {
  flex: 1; overflow-y: auto; display: flex; flex-direction: column;
  gap: 1rem; padding-bottom: 1rem;
  scrollbar-width: none;
}
#feed::-webkit-scrollbar { display: none; }
.phrase { font-size: clamp(2rem, 3.8vw, 3rem); line-height: 1.3; animation: rise .6s ease-out; }
@keyframes rise { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: translateY(0); } }
#resp-count { font-family: var(--sans); font-size: .8rem; color: #444; margin-top: .75rem; text-align: right; }

/* ── Admin button ── */
#admin-btn {
  position: fixed; bottom: 1rem; right: 1rem;
  width: 30px; height: 30px; background: transparent;
  border: 1px solid #333; border-radius: 6px;
  color: #444; font-size: .85rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  z-index: 20; transition: border-color .15s, color .15s;
}
#admin-btn:hover { border-color: var(--accent); color: var(--accent); }

/* ── Admin overlay ── */
#admin-overlay { position: fixed; inset: 0; background: rgba(8,8,6,.93); z-index: 100; display: flex; align-items: center; justify-content: center; }
#admin-overlay.off { display: none; }
.admin-panel {
  position: relative; background: var(--panel);
  border: 1px solid #333; border-radius: 12px;
  padding: 2rem; width: min(500px, 92vw); max-height: 90vh; overflow-y: auto;
  font-family: var(--sans);
}
.admin-panel h2 { font-family: var(--font); font-size: 1.3rem; margin-bottom: 1.25rem; }
.admin-close {
  position: absolute; top: 1rem; right: 1rem;
  background: transparent; border: 1px solid #444; border-radius: 6px;
  color: #888; font-size: 1rem; width: 28px; height: 28px;
  display: flex; align-items: center; justify-content: center; cursor: pointer;
}
.admin-close:hover { color: var(--text); border-color: #888; }
.lbl { font-size: .75rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--muted); margin-bottom: .3rem; }
.ai {
  width: 100%; background: #111; border: 1.5px solid #333; border-radius: 6px;
  color: var(--text); font-size: .95rem; padding: .6rem .75rem;
  font-family: var(--sans); margin-bottom: .75rem; transition: border-color .15s;
}
.ai:focus { outline: none; border-color: var(--accent); }
textarea.ai { resize: vertical; }
.presets { display: flex; flex-direction: column; gap: .35rem; margin-bottom: .75rem; }
.preset {
  background: #1e1e1c; border: 1px solid #333; border-radius: 6px;
  color: #bbb; font-size: .85rem; padding: .5rem .75rem;
  text-align: left; cursor: pointer; font-family: var(--sans);
  transition: border-color .15s, color .15s;
}
.preset:hover { border-color: var(--accent); color: var(--text); }
.adiv { border-color: #333; margin: 1.25rem 0; }
.arow { display: flex; gap: .75rem; flex-wrap: wrap; }
.bp  { background: var(--accent); color: #fff; border: none; border-radius: 6px; padding: .65rem 1.25rem; font-size: .9rem; font-family: var(--sans); cursor: pointer; transition: opacity .15s; }
.bp:hover { opacity: .85; }
.bp:disabled { opacity: .4; cursor: default; }
.bd  { background: #2a0e0e; color: #e88; border: 1px solid #4a1e1e; border-radius: 6px; padding: .65rem 1.25rem; font-size: .9rem; font-family: var(--sans); cursor: pointer; transition: opacity .15s; }
.bd:hover { opacity: .85; }
.bd:disabled { opacity: .4; cursor: default; }
.bg  { background: transparent; color: var(--muted); border: 1px solid #333; border-radius: 6px; padding: .65rem 1.25rem; font-size: .9rem; font-family: var(--sans); cursor: pointer; transition: border-color .15s, color .15s; }
.bg:hover { border-color: #888; color: var(--text); }
.aerr { color: #e88; font-size: .85rem; margin-top: .5rem; min-height: 1.2em; }
.sbadge {
  display: inline-block; padding: .2rem .5rem; border-radius: 4px;
  font-size: .72rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; margin-bottom: .75rem;
}
.sbadge.live { background: #0e2a0e; color: #5cb85c; }
.sbadge.idle { background: #222; color: #666; }

/* ── Review overlay ── */
#review-overlay { position: fixed; inset: 0; background: var(--bg); z-index: 200; display: flex; flex-direction: column; font-family: var(--font); }
#review-overlay.off { display: none; }
#review-header {
  display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
  padding: 1.5rem 2.5rem; border-bottom: 1px solid var(--div); flex-shrink: 0;
}
#review-label { font-family: var(--sans); font-size: clamp(.8rem, 1.2vw, .95rem); color: var(--muted); margin-bottom: .25rem; }
#review-prompt-display { font-size: clamp(1.2rem, 2.5vw, 1.9rem); line-height: 1.25; }
#review-list {
  flex: 1; overflow-y: auto; padding: 2rem 3rem;
  display: flex; flex-direction: column; gap: 1.75rem;
  scroll-behavior: smooth;
}
.ritem {
  font-size: clamp(1.5rem, 3.2vw, 2.4rem); line-height: 1.3;
  opacity: 0; transform: translateY(8px);
  transition: opacity .25s, transform .25s;
}
.ritem.shown { opacity: 1; transform: none; }
#review-footer {
  display: flex; align-items: center; justify-content: space-between; gap: 1rem;
  padding: 1rem 2.5rem; border-top: 1px solid var(--div); flex-shrink: 0;
  font-family: var(--sans); font-size: .85rem; color: var(--muted);
}
#review-footer select {
  background: #111; border: 1px solid #333; border-radius: 6px;
  color: var(--text); font-size: .85rem; padding: .35rem .6rem; font-family: var(--sans);
}
</style>
</head>
<body>

<div id="main">
  <div id="left">
    <div id="qr-wrap"><img src="qr.png" alt="QR code" id="qr-img" style="display:block"></div>
    <div class="qr-url">hutchmoot.jgsongs.com/reflection</div>
    <div class="qr-cta">Scan to share your reflection</div>
  </div>

  <div id="right">
    <div class="session-chip" id="chip"></div>
    <div id="prompt"></div>
    <div id="idle-msg">Waiting for the next round…</div>
    <div id="feed"></div>
    <div id="resp-count"></div>
  </div>
</div>

<button id="admin-btn" title="Admin (press A)" onclick="openAdmin()">⚙</button>

<!-- Admin overlay -->
<div id="admin-overlay" class="off">
  <div class="admin-panel">
    <button class="admin-close" onclick="closeAdmin()">×</button>

    <!-- PIN panel -->
    <div id="pin-panel">
      <h2>Admin</h2>
      <div class="lbl">Enter PIN</div>
      <input class="ai" id="pin-input" type="password" maxlength="6" inputmode="numeric" placeholder="••••••" autocomplete="off">
      <div class="aerr" id="pin-err"></div>
      <div class="arow" style="margin-top:.75rem">
        <button class="bp" onclick="checkPin()">Unlock</button>
        <button class="bg" onclick="closeAdmin()">Cancel</button>
      </div>
    </div>

    <!-- Controls panel -->
    <div id="ctrl-panel" style="display:none">
      <h2>Reflection Controls</h2>
      <div id="status-area"></div>

      <hr class="adiv">

      <div class="lbl">Round label</div>
      <input class="ai" id="rlabel" type="text" maxlength="60" placeholder="e.g. Round 1">

      <div class="lbl">Prompt</div>
      <textarea class="ai" id="rprompt" rows="3" maxlength="300" placeholder="Type a prompt, or choose one below…"></textarea>

      <div class="lbl" style="margin-bottom:.4rem">Quick prompts</div>
      <div class="presets">
        <button class="preset" onclick="usePreset(0)">A word or phrase for what the songwriting felt like.</button>
        <button class="preset" onclick="usePreset(1)">What moved you in the songs you heard today?</button>
        <button class="preset" onclick="usePreset(2)">What will you carry away from this workshop?</button>
      </div>

      <button class="bp" id="start-btn" onclick="startRound()" style="width:100%;margin-bottom:1rem">Start round</button>

      <hr class="adiv">

      <div class="arow">
        <button class="bd" id="end-btn" onclick="endRound()" disabled>End round</button>
        <button class="bg" onclick="openReview()">Review mode →</button>
      </div>
      <div class="aerr" id="ctrl-err"></div>
    </div>
  </div>
</div>

<!-- Review overlay -->
<div id="review-overlay" class="off">
  <div id="review-header">
    <div>
      <div id="review-label"></div>
      <div id="review-prompt-display"></div>
    </div>
    <button class="bg" onclick="closeReview()" style="white-space:nowrap;flex-shrink:0">← Live display</button>
  </div>
  <div id="review-list"></div>
  <div id="review-footer">
    <select id="session-sel" onchange="loadReviewSession(this.value)"></select>
    <span id="review-count"></span>
  </div>
</div>

<script>
// ── State ──────────────────────────────────────────────────────────────────
let session    = null;
let phrases    = [];
let lastId     = 0;
let authed     = false;
let adminPin   = '';
let allSessions = [];

const PRESETS = [
  'A word or phrase for what the songwriting felt like.',
  'What moved you in the songs you heard today?',
  'What will you carry away from this workshop?',
];

// ── Keyboard shortcuts ──────────────────────────────────────────────────────
document.addEventListener('keydown', function (e) {
  const adminOpen  = !document.getElementById('admin-overlay').classList.contains('off');
  const reviewOpen = !document.getElementById('review-overlay').classList.contains('off');

  if (reviewOpen) {
    const list = document.getElementById('review-list');
    if      (e.key === 'ArrowDown')  { list.scrollBy({ top: 120,  behavior: 'smooth' }); e.preventDefault(); }
    else if (e.key === 'ArrowUp')    { list.scrollBy({ top: -120, behavior: 'smooth' }); e.preventDefault(); }
    else if (e.key === 'PageDown')   { list.scrollBy({ top: 500,  behavior: 'smooth' }); e.preventDefault(); }
    else if (e.key === 'PageUp')     { list.scrollBy({ top: -500, behavior: 'smooth' }); e.preventDefault(); }
    else if (e.key === 'Home')       { list.scrollTo({ top: 0,    behavior: 'smooth' }); e.preventDefault(); }
    else if (e.key === 'End')        { list.scrollTo({ top: list.scrollHeight, behavior: 'smooth' }); e.preventDefault(); }
    else if (e.key === 'Escape')     { closeReview(); }
    return;
  }

  if (adminOpen) {
    if (e.key === 'Escape') closeAdmin();
    return;
  }

  if (e.key === 'a' || e.key === 'A') openAdmin();
});

// ── Admin panel ─────────────────────────────────────────────────────────────
function openAdmin() {
  document.getElementById('admin-overlay').classList.remove('off');
  if (authed) {
    showCtrl();
  } else {
    document.getElementById('pin-panel').style.display = '';
    document.getElementById('ctrl-panel').style.display = 'none';
    document.getElementById('pin-err').textContent = '';
    setTimeout(() => document.getElementById('pin-input').focus(), 60);
  }
}

function closeAdmin() {
  document.getElementById('admin-overlay').classList.add('off');
}

document.getElementById('pin-input').addEventListener('keydown', function (e) {
  if (e.key === 'Enter') checkPin();
});

async function checkPin() {
  const pin = document.getElementById('pin-input').value.trim();
  if (!pin) return;
  try {
    const res  = await fetch('/api/admin-auth.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ pin })
    });
    const data = await res.json();
    if (data.ok) {
      adminPin = pin;
      authed   = true;
      document.getElementById('pin-input').value = '';
      showCtrl();
    } else {
      document.getElementById('pin-err').textContent = 'Incorrect PIN.';
    }
  } catch {
    document.getElementById('pin-err').textContent = 'Connection error — try again.';
  }
}

function showCtrl() {
  document.getElementById('pin-panel').style.display = 'none';
  document.getElementById('ctrl-panel').style.display = '';
  document.getElementById('ctrl-err').textContent = '';
  refreshStatus();
  const next = (session ? session.id : 0) + 1;
  if (!document.getElementById('rlabel').value) {
    document.getElementById('rlabel').value = 'Round ' + next;
  }
}

function refreshStatus() {
  const area   = document.getElementById('status-area');
  const endBtn = document.getElementById('end-btn');
  if (session) {
    area.innerHTML =
      '<div class="sbadge live">LIVE</div> <strong>' + esc(session.label) + '</strong>' +
      '<br><span style="color:#888;font-size:.85rem">' + esc(session.prompt) + '</span>';
    endBtn.disabled = false;
  } else {
    area.innerHTML = '<div class="sbadge idle">IDLE</div> No active round';
    endBtn.disabled = true;
  }
}

function usePreset(i) {
  document.getElementById('rprompt').value = PRESETS[i];
  document.getElementById('rprompt').focus();
}

async function startRound() {
  const label  = document.getElementById('rlabel').value.trim();
  const prompt = document.getElementById('rprompt').value.trim();
  if (!label || !prompt) {
    document.getElementById('ctrl-err').textContent = 'Label and prompt are required.';
    return;
  }
  document.getElementById('ctrl-err').textContent = '';
  document.getElementById('start-btn').disabled = true;
  try {
    const res  = await fetch('api/session.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Admin-Pin': adminPin },
      body: JSON.stringify({ action: 'open', label, prompt })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    session = data.session;
    phrases = [];
    lastId  = 0;
    clearFeed(); renderHeader();
    document.getElementById('rlabel').value  = '';
    document.getElementById('rprompt').value = '';
    refreshStatus();
    closeAdmin();
  } catch (err) {
    document.getElementById('ctrl-err').textContent = err.message;
  } finally {
    document.getElementById('start-btn').disabled = false;
  }
}

async function endRound() {
  document.getElementById('end-btn').disabled = true;
  try {
    await fetch('api/session.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Admin-Pin': adminPin },
      body: JSON.stringify({ action: 'close' })
    });
    session = null;
    refreshStatus();
    renderHeader();
    closeAdmin();
  } catch {
    document.getElementById('end-btn').disabled = false;
  }
}

// ── Review mode ──────────────────────────────────────────────────────────────
async function openReview() {
  closeAdmin();
  try {
    const res = await fetch('api/session.php?all=1');
    allSessions = await res.json();
  } catch { allSessions = []; }

  const sel = document.getElementById('session-sel');
  sel.innerHTML = allSessions.map(s =>
    '<option value="' + s.id + '">' + esc(s.label) + ': ' + esc(s.prompt.slice(0, 55)) + (s.prompt.length > 55 ? '…' : '') + '</option>'
  ).join('');

  document.getElementById('review-overlay').classList.remove('off');

  const defaultId = session ? session.id : (allSessions[allSessions.length - 1]?.id ?? 0);
  if (defaultId) {
    sel.value = defaultId;
    await loadReviewSession(defaultId);
  }
}

function closeReview() {
  document.getElementById('review-overlay').classList.add('off');
}

async function loadReviewSession(id) {
  const s = allSessions.find(x => x.id === parseInt(id));
  document.getElementById('review-label').textContent = s ? s.label : '';
  document.getElementById('review-prompt-display').textContent = s ? s.prompt : '';

  const list = document.getElementById('review-list');
  list.innerHTML = '<div style="color:#444;font-family:var(--sans);font-size:.9rem">Loading…</div>';

  try {
    const res  = await fetch('api/poll.php?since=0&session=' + id);
    const rows = await res.json();
    list.innerHTML = '';
    rows.forEach(function (r, i) {
      const div = document.createElement('div');
      div.className = 'ritem';
      div.textContent = '“' + r.phrase + '”';
      list.appendChild(div);
      setTimeout(function () { div.classList.add('shown'); }, i * 40);
    });
    document.getElementById('review-count').textContent =
      rows.length + (rows.length === 1 ? ' response' : ' responses');
  } catch {
    list.innerHTML = '<div style="color:#e88;font-family:var(--sans);font-size:.9rem">Failed to load.</div>';
  }
}

// ── Live feed ────────────────────────────────────────────────────────────────
function renderHeader() {
  const chip  = document.getElementById('chip');
  const pText = document.getElementById('prompt');
  const idle  = document.getElementById('idle-msg');
  if (session) {
    chip.textContent = session.label; chip.classList.add('on');
    pText.textContent = session.prompt; pText.classList.add('on');
    idle.style.display = 'none';
  } else {
    chip.classList.remove('on'); pText.classList.remove('on');
    idle.style.display = '';
  }
  updateCount();
}

function updateCount() {
  document.getElementById('resp-count').textContent = phrases.length
    ? phrases.length + (phrases.length === 1 ? ' response' : ' responses')
    : '';
}

function clearFeed() {
  document.getElementById('feed').innerHTML = '';
}

function appendToFeed(newItems) {
  if (!newItems.length) return;
  const feed = document.getElementById('feed');
  const atBottom = feed.scrollHeight - feed.scrollTop - feed.clientHeight < 80;
  for (const p of newItems) {
    const div = document.createElement('div');
    div.className = 'phrase';
    div.textContent = '”' + p.phrase + '”';
    feed.appendChild(div);
  }
  updateCount();
  if (atBottom) setTimeout(function () { feed.scrollTop = feed.scrollHeight; }, 60);
}

// ── Polling ───────────────────────────────────────────────────────────────────
async function pollSession() {
  if (!document.getElementById('review-overlay').classList.contains('off')) return;
  try {
    const res  = await fetch('api/session.php');
    const data = await res.json();
    if (data.active && data.session) {
      if (!session || session.id !== data.session.id) {
        session = data.session;
        phrases = [];
        lastId  = 0;
        clearFeed(); renderHeader();
      }
    } else if (session) {
      session = null;
      renderHeader();
    }
  } catch {}
}

async function pollResponses() {
  if (!session) return;
  if (!document.getElementById('review-overlay').classList.contains('off')) return;
  try {
    const res  = await fetch('api/poll.php?since=' + lastId + '&session=' + session.id);
    const rows = await res.json();
    if (rows.length) {
      for (const r of rows) {
        phrases.push(r);
        if (r.id > lastId) lastId = r.id;
      }
      appendToFeed(rows);
    }
  } catch {}
}

function esc(str) {
  return String(str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ── Init ──────────────────────────────────────────────────────────────────────
pollSession();
pollResponses();
setInterval(pollSession,   5000);
setInterval(pollResponses, 2500);
</script>
</body>
</html>
