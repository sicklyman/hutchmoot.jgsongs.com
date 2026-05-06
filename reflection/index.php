<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hutchmoot — Reflect</title>
<style>
  :root {
    --bg: #faf8f5;
    --text: #1a1a18;
    --accent: #c4832a;
    --accent-light: #f5ead8;
    --border: #e8e4de;
    --radius: 8px;
    --font: 'Georgia', serif;
    --sans: system-ui, -apple-system, sans-serif;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: var(--sans);
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
  }
  .card {
    background: #fff;
    border-radius: var(--radius);
    box-shadow: 0 1px 4px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.04);
    padding: 2rem;
    width: 100%;
    max-width: 480px;
  }
  .chip {
    display: inline-block;
    background: var(--accent-light);
    color: var(--accent);
    font-size: .75rem;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    border-radius: 4px;
    padding: .25rem .6rem;
    margin-bottom: .75rem;
  }
  h1 { font-family: var(--font); font-size: 1.5rem; margin-bottom: 1.25rem; line-height: 1.3; }
  textarea {
    width: 100%;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    padding: .75rem;
    font-size: 1rem;
    font-family: var(--sans);
    resize: vertical;
    min-height: 100px;
    transition: border-color .15s;
  }
  textarea:focus { outline: none; border-color: var(--accent); }
  .char { text-align: right; font-size: .8rem; color: #7a7570; margin-top: .3rem; margin-bottom: .75rem; }
  .btn {
    display: block; width: 100%;
    padding: .85rem;
    background: var(--accent); color: #fff;
    border: none; border-radius: var(--radius);
    font-size: 1rem; font-weight: 500;
    cursor: pointer; transition: opacity .15s;
  }
  .btn:hover { opacity: .9; }
  .btn:disabled { opacity: .5; cursor: default; }
  .btn-outline {
    display: inline-block;
    background: none;
    border: 1.5px solid var(--accent); color: var(--accent);
    padding: .5rem 1.25rem;
    border-radius: var(--radius); font-size: .9rem; cursor: pointer;
  }
  .hint { font-size: .8rem; color: #7a7570; text-align: center; margin-top: 1rem; }
  /* Waiting */
  #waiting { text-align: center; }
  #waiting h2 { font-family: var(--font); font-size: 1.3rem; margin-bottom: .5rem; }
  #waiting p { color: #7a7570; font-size: .9rem; }
  .dot {
    display: inline-block; width: 8px; height: 8px;
    background: var(--accent); border-radius: 50%;
    margin-top: 1.25rem;
    animation: blink 1.5s ease-in-out infinite;
  }
  @keyframes blink { 0%,100% { opacity: .25; } 50% { opacity: 1; } }
  /* Confirm */
  #confirm { text-align: center; }
  #confirm .check { font-size: 2.5rem; margin-bottom: .5rem; }
  #confirm p { color: #7a7570; font-size: .9rem; margin-bottom: 1rem; }
</style>
</head>
<body>
<div class="card">

  <div id="waiting">
    <h2>We'll begin soon.</h2>
    <p>Watch the screen.</p>
    <div class="dot"></div>
  </div>

  <div id="form-view" style="display:none">
    <div class="chip" id="round-chip"></div>
    <h1 id="prompt-text"></h1>
    <textarea id="phrase" maxlength="280" placeholder="Write here…"></textarea>
    <p class="char"><span id="char-count">0</span>/280</p>
    <button class="btn" id="submit-btn" onclick="doSubmit()">Send</button>
    <p class="hint" id="remaining-hint"></p>
  </div>

  <div id="confirm" style="display:none">
    <div class="check">✓</div>
    <p>Sent! Keep watching the screen.</p>
    <button class="btn-outline" id="again-btn" onclick="showForm()" style="margin-top:.5rem">Add another</button>
  </div>

</div>
<script>
let activeSession = null;
let submits = 0;
const MAX = 3;

async function checkSession() {
  try {
    const res  = await fetch('api/session.php');
    const data = await res.json();
    if (data.active && data.session) {
      const incoming = data.session;
      if (!activeSession || activeSession.id !== incoming.id) {
        activeSession = incoming;
        submits = parseInt(sessionStorage.getItem('rf_' + activeSession.id) || '0', 10);
        showForm();
      }
    } else {
      if (activeSession) { activeSession = null; showWaiting(); }
    }
  } catch {}
}

function showWaiting() {
  document.getElementById('waiting').style.display = '';
  document.getElementById('form-view').style.display = 'none';
  document.getElementById('confirm').style.display = 'none';
}

function showForm() {
  if (!activeSession) return;
  document.getElementById('round-chip').textContent = activeSession.label;
  document.getElementById('prompt-text').textContent = activeSession.prompt;
  document.getElementById('phrase').value = '';
  document.getElementById('char-count').textContent = '0';
  document.getElementById('waiting').style.display = 'none';
  document.getElementById('confirm').style.display = 'none';
  document.getElementById('form-view').style.display = '';
  updateHint();
}

function updateHint() {
  const left = MAX - submits;
  const hint = document.getElementById('remaining-hint');
  const btn  = document.getElementById('submit-btn');
  if (left <= 0) {
    hint.textContent = "You've reached the maximum of 3 submissions for this round.";
    btn.disabled = true;
  } else {
    hint.textContent = left === 1 ? '1 submission remaining.' : `${left} submissions remaining.`;
    btn.disabled = false;
  }
}

document.getElementById('phrase').addEventListener('input', function () {
  document.getElementById('char-count').textContent = this.value.length;
});

async function doSubmit() {
  if (submits >= MAX || !activeSession) return;
  const phrase = document.getElementById('phrase').value.trim();
  if (!phrase) return;

  const btn = document.getElementById('submit-btn');
  btn.disabled = true;
  btn.textContent = 'Sending…';

  try {
    const res = await fetch('api/submit.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ phrase })
    });
    if (!res.ok) throw new Error();
    submits++;
    sessionStorage.setItem('rf_' + activeSession.id, submits);
    document.getElementById('form-view').style.display = 'none';
    document.getElementById('again-btn').style.display = submits >= MAX ? 'none' : '';
    document.getElementById('confirm').style.display = '';
  } catch {
    btn.textContent = 'Send';
    updateHint();
    alert('Something went wrong — please try again.');
  }
}

checkSession();
setInterval(checkSession, 5000);
</script>
</body>
</html>
