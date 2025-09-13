<?php
/**
 * app_plain.php — plain PHP app (no frameworks)
 * Routes:
 *   GET /? -> dashboard HTML
 *   GET /?api=projects&q=... -> JSON fake data
 */
declare(strict_types=1);

// tiny router
$api = $_GET['api'] ?? null;
if ($api === 'projects') {
    header('Content-Type: application/json; charset=utf-8');
    $q = strtolower(trim($_GET['q'] ?? ''));
    $owners = ['alice','bob','carol','dave'];
    $items = [];
    for ($i=1; $i<=6; $i++) {
        $owner = $owners[array_rand($owners)];
        $p = [
            'id' => sprintf('PRJ-%d-%04d', time(), rand(1000,9999)),
            'name' => "Project $i",
            'owner' => $owner,
            'status' => ['active','paused','done'][array_rand([0,1,2])],
            'created_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'budget' => round(mt_rand(1000, 9000) + mt_rand() / mt_getrandmax(), 2),
        ];
        if ($q) {
            if (stripos($p['name'], $q) === false && stripos($p['owner'], $q) === false) continue;
        }
        $items[] = $p;
    }
    echo json_encode(['ok'=>true,'items'=>$items,'ts'=>gmdate('c')], JSON_UNESCAPED_UNICODE);
    exit;
}

?><!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>project • ionCube-ready</title>
<style>
:root{--c0:#0d1117;--c1:#161b22;--c2:#21262d;--a:#58a6ff;--g:#2ea043;--y:#d29922;--t:#8b949e}
*{box-sizing:border-box}html,body{margin:0;background:var(--c0);color:#c9d1d9;font-family:system-ui,Segoe UI,Roboto}
.container{max-width:980px;margin:0 auto;padding:24px}
.top{display:flex;gap:12px;align-items:center;justify-content:space-between}
.badge{background:linear-gradient(90deg,#8a2be2,#00d4ff);padding:4px 10px;border-radius:999px;font-weight:700;font-size:12px}
.card{background:var(--c1);border:1px solid var(--c2);border-radius:12px;overflow:hidden}
.header{padding:16px 20px;border-bottom:1px solid var(--c2);display:flex;gap:16px;align-items:center}
.header input{flex:1;background:#0b1220;border:1px solid #1e2633;color:#d1e9ff;padding:10px 12px;border-radius:8px;outline:none}
.header button{background:var(--a);border:0;color:#0d1117;padding:10px 14px;border-radius:8px;font-weight:700;cursor:pointer}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:16px}
.tile{background:#0d1220;border:1px solid #1f2a3b;border-radius:10px;padding:12px}
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:8px 10px;border-bottom:1px solid #1f2a3b;text-align:left;font-size:14px}
.pill{padding:2px 8px;border-radius:999px;color:#0d1117;font-weight:700;font-size:12px}
.active{background:var(--a)}.paused{background:var(--y)}.done{background:var(--g)}
.empty{opacity:.6;text-align:center;padding:28px}
footer{opacity:.6;padding:16px 0;text-align:center}
@media(max-width:800px){.grid{grid-template-columns:1fr}}
pre.note{background:#0b1220;border:1px dashed #224;padding:8px 12px;border-radius:8px}
</style>
</head>
<body>
<div class="container">
  <div class="top">
    <div style="display:flex;gap:12px;align-items:center">
      <div class="badge">PROJECT</div>
      <div><div style="font-weight:800">ionCube-ready</div><small>plain mode (no encoding detected)</small></div>
    </div>
    <small><?=htmlspecialchars(date('Y-m-d H:i:s'))?></small>
  </div>
  <div class="card">
    <div class="header">
      <input id="q" placeholder="Tìm project theo tên/owner…"/>
      <button id="btn">Search</button>
      <button id="info">Info</button>
    </div>
    <div class="grid" id="stats"></div>
    <div class="tile">
      <table class="table" id="tbl">
        <thead><tr><th>ID</th><th>Name</th><th>Owner</th><th>Status</th><th>Budget</th><th>Created</th></tr></thead>
        <tbody id="rows"><tr><td colspan="6" class="empty">Đang tải…</td></tr></tbody>
      </table>
    </div>
  </div>
  <footer>© <?=date('Y')?> demo • ionCube-ready</footer>

  <details style="margin-top:16px">
    <summary>Hướng dẫn build ionCube</summary>
    <pre class="note">1) Cài ionCube Loader cho PHP (php.ini)
2) Dùng ionCube Encoder (bản CLI) để mã hoá app_plain.php -> dist/app_encoded.php
   ./build.sh
Nếu không có Encoder, code sẽ chạy ở chế độ "runtime encoded" hoặc "plain".</pre>
  </details>
</div>
<script>
const $=s=>document.querySelector(s);
const money=n=>new Intl.NumberFormat('vi-VN',{style:'currency',currency:'VND',maximumFractionDigits:0}).format(n);
function pill(c){let s=document.createElement('span');s.className='pill '+c;s.textContent=c;return s;}
async function load(q){
  const r=await fetch('?api=projects&q='+encodeURIComponent(q||''));
  const j=await r.json();
  const rows=$('#rows'); rows.innerHTML='';
  if(!j.items.length){ rows.innerHTML='<tr><td colspan="6" class="empty">Không có dữ liệu</td></tr>'; return; }
  let sum=0,a=0,p=0,d=0;
  j.items.forEach(it=>{ sum+=it.budget; if(it.status==='active')a++; else if(it.status==='paused')p++; else d++;
    const tr=document.createElement('tr');
    tr.innerHTML=`<td style="font-family:monospace">${it.id}</td><td>${it.name}</td><td>${it.owner}</td><td></td><td>${money(it.budget*1000)}</td><td><small>${it.created_at}</small></td>`;
    tr.children[3].appendChild(pill(it.status)); rows.appendChild(tr);
  });
  $('#stats').innerHTML=`
    <div class="tile">Tổng project: <b>${j.items.length}</b></div>
    <div class="tile">Ngân sách: <b>${money(sum*1000)}</b></div>
    <div class="tile">Active: <b>${a}</b></div>
    <div class="tile">Paused: <b>${p}</b></div>
    <div class="tile">Done: <b>${d}</b></div>`;
}
$('#btn').onclick=()=>load($('#q').value.trim());
$('#q').addEventListener('keydown',e=>{if(e.key==='Enter')$('#btn').click();});
$('#info').onclick=()=>alert('Mode: plain (no ionCube build found)');
load('');
</script>
</body></html>
