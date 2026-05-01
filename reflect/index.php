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
  h1 {
    font-family: var(--font);
    font-size: 1.6rem;
    margin-bottom: 0.4rem;
  }
  p.sub {
    color: #7a7570;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
  }
  textarea {
    width: 100%;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    padding: 0.75rem;
    font-size: 1rem;
    font-family: var(--sans);
    resize: vertical;
    min-height: 100px;
    transition: border-color .15s;
  }
  textarea:focus { outline: none; border-color: var(--accent); }
  .char { text-align: right; font-size: 0.8rem; color: #7a7570; margin-top: .3rem; margin-bottom: .75rem; }
  button {
    width: 100%;
    padding: 0.85rem;
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: var(--radius);
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: opacity .15s;
  }
  button:hover { opacity: .9; }
  button:disabled { opacity: .5; cursor: default; }
  .confirm {
    text-align: center;
    padding: 1rem 0;
  }
  .confirm .check {
    font-size: 2rem;
    margin-bottom: .5rem;
  }
  .confirm p { color: #7a7570; font-size: .9rem; }
  .confirm .again { margin-top: 1rem; color: var(--accent); background: none; width: auto; border: 1.5px solid var(--accent); padding: .5rem 1.25rem; border-radius: var(--radius); }
  .hint { font-size: .8rem; color: #7a7570; text-align: center; margin-top: 1rem; }
</style>
</head>
<body>
<div class="card" id="app">
  <div id="form-view">
    <h1>Reflect</h1>
    <p class="sub">A word or phrase for what today felt like.</p>
    <textarea id="phrase" maxlength="280" placeholder="Write here…"></textarea>
    <p class="char"><span id="count">0</span>/280</p>
    <button id="submit-btn" onclick="submitPhrase()">Send</button>
    <p class="hint" id="remaining">You can submit up to 3 times.</p>
  </div>
  <div id="confirm-view" style="display:none" class="confirm">
    <div class="check">✓</div>
    <p>Sent! Keep watching the screen.</p>
    <button class="again" onclick="showForm()">Add another</button>
  </div>
</div>

<script>
let submits = parseInt(sessionStorage.getItem('reflect_submits') || '0', 10);
const MAX = 3;

function updateRemaining() {
  const left = MAX - submits;
  const el = document.getElementById('remaining');
  if (left <= 0) {
    el.textContent = "You've reached the maximum of 3 submissions.";
    document.getElementById('submit-btn').disabled = true;
  } else {
    el.textContent = left === 1 ? '1 submission remaining.' : `${left} submissions remaining.`;
  }
}

document.getElementById('phrase').addEventListener('input', function() {
  document.getElementById('count').textContent = this.value.length;
});

updateRemaining();

function showForm() {
  document.getElementById('form-view').style.display = '';
  document.getElementById('confirm-view').style.display = 'none';
  document.getElementById('phrase').value = '';
  document.getElementById('count').textContent = '0';
  updateRemaining();
}

async function submitPhrase() {
  if (submits >= MAX) return;
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
    sessionStorage.setItem('reflect_submits', submits);
    document.getElementById('form-view').style.display = 'none';
    document.getElementById('confirm-view').style.display = '';
  } catch {
    btn.textContent = 'Send';
    btn.disabled = false;
    alert('Something went wrong — please try again.');
  }
}
</script>
</body>
</html>
