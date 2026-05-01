<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hutchmoot — Reflect (Display)</title>
<style>
  :root {
    --bg: #1a1a18;
    --text: #faf8f5;
    --accent: #c4832a;
    --font: 'Georgia', serif;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body {
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: var(--bg);
    color: var(--text);
    font-family: var(--font);
  }
  header {
    position: fixed;
    top: 0; left: 0; right: 0;
    text-align: center;
    padding: 1.5rem;
    background: var(--bg);
    z-index: 10;
    border-bottom: 1px solid #333;
  }
  header h1 {
    font-size: clamp(1.2rem, 3vw, 2rem);
    color: var(--accent);
    letter-spacing: 0.05em;
  }
  header p {
    font-size: clamp(.8rem, 1.5vw, 1rem);
    color: #888;
    margin-top: .3rem;
    font-family: system-ui, sans-serif;
  }
  #feed {
    position: fixed;
    inset: 0;
    padding-top: 6rem;
    padding-bottom: 1rem;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    gap: .75rem;
    padding-left: 2rem;
    padding-right: 2rem;
  }
  .phrase {
    font-size: clamp(1.4rem, 3.5vw, 2.2rem);
    line-height: 1.3;
    animation: rise 0.6s ease-out;
    opacity: 1;
    color: var(--text);
  }
  .phrase.old {
    opacity: 0.5;
    font-size: clamp(1rem, 2.5vw, 1.6rem);
    color: #aaa;
  }
  .phrase.older {
    opacity: 0.2;
    font-size: clamp(.9rem, 2vw, 1.3rem);
  }
  @keyframes rise {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  #counter {
    position: fixed;
    bottom: 1rem;
    right: 1.5rem;
    font-family: system-ui, sans-serif;
    font-size: .8rem;
    color: #555;
  }
</style>
</head>
<body>
<header>
  <h1>Hutchmoot 2026</h1>
  <p>What did today feel like?</p>
</header>

<div id="feed"></div>
<div id="counter">0 responses</div>

<script>
const MAX_VISIBLE = 6;
let lastId = 0;
const phrases = [];

async function poll() {
  try {
    const res = await fetch('api/poll.php?since=' + lastId);
    const data = await res.json();
    if (data.length) {
      for (const row of data) {
        phrases.push(row);
        if (row.id > lastId) lastId = row.id;
      }
      render();
    }
  } catch {}
}

function render() {
  const feed = document.getElementById('feed');
  feed.innerHTML = '';

  // Show most recent MAX_VISIBLE, newest at bottom
  const visible = phrases.slice(-MAX_VISIBLE);

  visible.forEach((p, i) => {
    const div = document.createElement('div');
    div.className = 'phrase';
    const age = visible.length - 1 - i;
    if (age >= 4) div.className += ' older';
    else if (age >= 2) div.className += ' old';
    div.textContent = '“' + p.phrase + '”';
    feed.appendChild(div);
  });

  document.getElementById('counter').textContent =
    phrases.length + (phrases.length === 1 ? ' response' : ' responses');
}

poll();
setInterval(poll, 2500);
</script>
</body>
</html>
