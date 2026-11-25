<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GabungPDF — Gabung PDF & pilih halaman, gratis</title>
    <meta name="description" content="Gabungkan beberapa PDF jadi satu, pilih halaman yang dibutuhkan, lihat hasilnya langsung. Gratis, tanpa login, file tidak disimpan.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#f6f7fb; --surface:#fff; --ink:#0f172a; --muted:#64748b; --line:#e5e7f0;
            --brand:#6366f1; --brand2:#ec4899; --brand-soft:#eef0ff; --ok:#10b981; --bad:#ef4444;
            --shadow:0 10px 40px -12px rgba(79,70,229,.25);
            --radius:20px;
        }
        @media (prefers-color-scheme:dark){
            :root{--bg:#0b1020;--surface:#131a2e;--ink:#eef1ff;--muted:#94a3c4;--line:#232c47;--brand-soft:#1c2347;--shadow:0 10px 40px -12px rgba(0,0,0,.6)}
        }
        *{box-sizing:border-box;margin:0}
        html{scroll-behavior:smooth}
        body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--ink);line-height:1.55;-webkit-font-smoothing:antialiased;overflow-x:hidden}
        .grad{background:linear-gradient(120deg,var(--brand),var(--brand2));-webkit-background-clip:text;background-clip:text;color:transparent}
        .blob{position:fixed;z-index:-1;border-radius:50%;filter:blur(90px);opacity:.35;pointer-events:none}
        .blob.a{width:480px;height:480px;background:#818cf8;top:-140px;left:-120px}
        .blob.b{width:420px;height:420px;background:#f472b6;top:200px;right:-140px}

        .wrap{max-width:1280px;margin:0 auto;padding:0 20px}
        nav{display:flex;align-items:center;justify-content:space-between;padding:22px 0}
        .logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:1.2rem;letter-spacing:-.02em;text-decoration:none;color:var(--ink)}
        .logo i{width:36px;height:36px;border-radius:11px;background:linear-gradient(135deg,var(--brand),var(--brand2));display:grid;place-items:center;color:#fff;font-style:normal;box-shadow:0 6px 16px -4px rgba(99,102,241,.6)}
        .pill{font-size:.78rem;font-weight:600;color:var(--brand);background:var(--brand-soft);padding:6px 12px;border-radius:99px}

        .hero{text-align:center;padding:36px 0 40px}
        .hero h1{font-size:clamp(2rem,5.2vw,3.6rem);font-weight:800;letter-spacing:-.035em;line-height:1.1;max-width:820px;margin:0 auto 16px}
        .hero p{color:var(--muted);font-size:1.1rem;max-width:560px;margin:0 auto 22px}
        .badges{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
        .badges span{display:inline-flex;gap:6px;align-items:center;font-size:.85rem;font-weight:600;background:var(--surface);border:1px solid var(--line);padding:7px 14px;border-radius:99px}
        .badges span::before{content:"✓";color:var(--ok);font-weight:800}

        .app{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(340px,1fr);gap:24px;align-items:start;padding-bottom:56px}
        @media(max-width:960px){.app{grid-template-columns:1fr}.preview{order:2}}
        .card{background:var(--surface);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow)}

        /* preview */
        .preview{position:sticky;top:20px;overflow:hidden;display:flex;flex-direction:column;height:min(80vh,780px);min-height:520px}
        @media(max-width:960px){.preview{position:static;height:70vh}}
        .bar{display:flex;align-items:center;gap:8px;padding:12px 16px;border-bottom:1px solid var(--line)}
        .dots{display:flex;gap:6px}.dots b{width:11px;height:11px;border-radius:50%;background:#fb7185}.dots b:nth-child(2){background:#fbbf24}.dots b:nth-child(3){background:#34d399}
        .bar .title{flex:1;text-align:center;font-size:.82rem;font-weight:600;color:var(--muted)}
        .state{font-size:.75rem;font-weight:700;padding:4px 10px;border-radius:99px;background:var(--brand-soft);color:var(--brand)}
        .state.err{background:#fee2e2;color:var(--bad)}
        .frame{flex:1;position:relative;background:repeating-linear-gradient(45deg,var(--bg),var(--bg) 12px,transparent 12px,transparent 24px)}
        .frame{overflow:hidden}
        .frame iframe{position:absolute;top:0;left:-44px;width:calc(100% + 44px);height:100%;border:0;display:none;background:#fff}
        .empty{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;text-align:center;padding:30px;color:var(--muted)}
        .sheet{width:120px;height:154px;background:var(--surface);border:1px solid var(--line);border-radius:10px;box-shadow:var(--shadow);position:relative;animation:float 4s ease-in-out infinite}
        .sheet::before,.sheet::after{content:"";position:absolute;left:16px;right:16px;height:8px;border-radius:4px;background:var(--line)}
        .sheet::before{top:22px;box-shadow:0 18px 0 var(--line),0 36px 0 var(--line),0 54px 0 var(--brand-soft)}
        .sheet::after{bottom:22px;right:50px;background:linear-gradient(90deg,var(--brand),var(--brand2))}
        .sheet.two{position:absolute;transform:rotate(-8deg) translateX(-34px);opacity:.55;animation-delay:.4s}
        @keyframes float{50%{transform:translateY(-8px)}}
        .sheet.two{animation:none}
        .stack{position:relative;width:150px;height:160px;display:grid;place-items:center}
        .spin{display:none;position:absolute;inset:0;background:color-mix(in srgb,var(--surface) 70%,transparent);backdrop-filter:blur(3px);place-items:center;z-index:2}
        .spin.on{display:grid}
        .spin::after{content:"";width:38px;height:38px;border-radius:50%;border:4px solid var(--brand-soft);border-top-color:var(--brand);animation:rot .8s linear infinite}
        @keyframes rot{to{transform:rotate(360deg)}}

        /* tool */
        .tool{padding:24px}
        .tool h2{font-size:1.25rem;font-weight:800;letter-spacing:-.02em;margin-bottom:4px}
        .tool .sub{color:var(--muted);font-size:.92rem;margin-bottom:18px}
        .drop{border:2px dashed #c7cbf5;background:var(--brand-soft);border-radius:16px;padding:26px 16px;text-align:center;cursor:pointer;transition:.2s}
        @media(prefers-color-scheme:dark){.drop{border-color:#3a4585}}
        .drop:hover,.drop.over{border-color:var(--brand);transform:translateY(-2px);box-shadow:0 10px 24px -10px rgba(99,102,241,.5)}
        .drop .ic{width:52px;height:52px;margin:0 auto 10px;border-radius:16px;background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;display:grid;place-items:center;font-size:1.5rem;box-shadow:0 8px 18px -6px rgba(99,102,241,.6)}
        .drop b{display:block;font-weight:700}.drop small{color:var(--muted)}

        .list{margin:16px 0;display:grid;gap:10px}
        .item{display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center;padding:12px;border:1px solid var(--line);border-radius:14px;background:var(--surface);transition:.15s;animation:pop .25s ease}
        @keyframes pop{from{opacity:0;transform:translateY(8px) scale(.98)}}
        .item.dragging{opacity:.4}.item.target{border-color:var(--brand);box-shadow:0 0 0 3px var(--brand-soft)}
        .num{width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;font-weight:800;font-size:.85rem;display:grid;place-items:center;cursor:grab}
        .meta{min-width:0}
        .name{font-weight:700;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .size{font-size:.75rem;color:var(--muted)}
        .rng{margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;align-items:center}
        .rng input{flex:1;min-width:120px;padding:8px 11px;border:1.5px solid var(--line);border-radius:10px;font:inherit;font-size:.85rem;background:var(--bg);color:var(--ink);outline:0;transition:.15s}
        .rng input:focus{border-color:var(--brand);box-shadow:0 0 0 3px var(--brand-soft)}
        .chip{font:inherit;font-size:.72rem;font-weight:700;padding:5px 10px;border-radius:99px;border:0;background:var(--brand-soft);color:var(--brand);cursor:pointer}
        .chip:hover{background:var(--brand);color:#fff}
        .tools{display:flex;flex-direction:column;gap:4px}
        .ib{width:28px;height:28px;border-radius:8px;border:0;background:var(--bg);color:var(--muted);cursor:pointer;font-size:.8rem;transition:.15s}
        .ib:hover{background:var(--brand-soft);color:var(--brand)}.ib.x:hover{background:#fee2e2;color:var(--bad)}

        .hint{display:flex;gap:8px;font-size:.8rem;color:var(--muted);background:var(--bg);padding:10px 12px;border-radius:12px;margin-bottom:16px}
        .err{display:none;background:#fee2e2;color:#b91c1c;border-radius:12px;padding:11px 14px;font-size:.88rem;font-weight:600;margin-bottom:14px}
        .err.show{display:block}
        .cta{width:100%;border:0;cursor:pointer;font:inherit;font-weight:800;font-size:1.02rem;color:#fff;padding:15px;border-radius:14px;background:linear-gradient(120deg,var(--brand),var(--brand2));box-shadow:0 12px 26px -10px rgba(236,72,153,.7);transition:.2s}
        .cta:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 16px 30px -10px rgba(236,72,153,.8)}
        .cta:disabled{opacity:.45;cursor:not-allowed;box-shadow:none}

        /* features */
        .features{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;padding-bottom:56px}
        @media(max-width:800px){.features{grid-template-columns:1fr}}
        .f{padding:24px}
        .f .e{width:46px;height:46px;border-radius:14px;background:var(--brand-soft);display:grid;place-items:center;font-size:1.4rem;margin-bottom:12px}
        .f h3{font-size:1.05rem;font-weight:800;margin-bottom:4px}.f p{color:var(--muted);font-size:.92rem}
        footer{text-align:center;color:var(--muted);font-size:.85rem;padding:0 0 36px}
    </style>
</head>
<body>
<div class="blob a"></div><div class="blob b"></div>

<div class="wrap">
    <nav>
        <a class="logo" href="/"><i>⧉</i> GabungPDF</a>
        <span class="pill">100% gratis · tanpa login</span>
    </nav>

    <header class="hero">
        <h1>Gabung banyak PDF, <span class="grad">ambil halaman yang kamu mau.</span></h1>
        <p>Upload beberapa file, tentukan halaman tiap file (misal <b>1-2, 5-6</b>), lihat hasilnya langsung, lalu unduh.</p>
        <div class="badges"><span>Preview langsung</span><span>Pilih halaman bebas</span><span>File tidak disimpan</span></div>
    </header>

    <main class="app">
        {{-- PREVIEW --}}
        <section class="card preview">
            <div class="bar">
                <div class="dots"><b></b><b></b><b></b></div>
                <div class="title">Hasil akhir</div>
                <span class="state" id="state">Menunggu file</span>
            </div>
            <div class="frame">
                <div class="empty" id="empty">
                    <div class="stack"><div class="sheet two"></div><div class="sheet"></div></div>
                    <div><b style="color:var(--ink)">Preview muncul di sini</b><br>Tambahkan PDF di sebelah kanan, hasil gabungan tampil otomatis.</div>
                </div>
                <iframe id="viewer" title="Preview PDF"></iframe>
                <div class="spin" id="spin"></div>
            </div>
        </section>

        {{-- TOOL --}}
        <section class="card tool">
            <h2>Mulai gabungkan</h2>
            <p class="sub">Tarik &amp; lepas file, atur urutan, tentukan halaman.</p>

            <div class="drop" id="drop">
                <div class="ic">＋</div>
                <b>Klik atau tarik file PDF ke sini</b>
                <small>Bisa pilih banyak file sekaligus · maks 50 MB per file</small>
                <input type="file" id="picker" accept="application/pdf" multiple hidden>
            </div>

            <div class="list" id="list"></div>

            <div class="hint"><span>💡</span><span>Kosongkan kolom halaman untuk memakai <b>semua halaman</b>. Contoh: <b>1-3</b>, <b>2, 4, 7</b>, atau <b>1-2, 5-6</b>. Seret nomor di kiri untuk mengubah urutan.</span></div>

            <div class="err" id="err"></div>
            <button class="cta" id="go" disabled>⬇ Gabungkan &amp; Unduh PDF</button>
        </section>
    </main>

    <section class="features">
        <div class="card f"><div class="e">🎯</div><h3>Pilih halaman tepat</h3><p>Ambil hanya halaman yang perlu dari setiap file, mulai dari satu halaman hingga banyak rentang.</p></div>
        <div class="card f"><div class="e">👀</div><h3>Lihat sebelum unduh</h3><p>Hasil akhir tampil langsung. Salah halaman? Ubah, preview ikut berubah.</p></div>
        <div class="card f"><div class="e">🔒</div><h3>Privat &amp; cepat</h3><p>Tanpa akun. File hanya diproses sesaat dan tidak disimpan di server.</p></div>
    </section>

    <footer>© {{ date('Y') }} GabungPDF · dibuat agar urusan PDF jadi gampang</footer>
</div>

<script>
const $ = id => document.getElementById(id);
const list = $('list'), drop = $('drop'), picker = $('picker'), go = $('go');
const viewer = $('viewer'), empty = $('empty'), state = $('state'), spin = $('spin'), errBox = $('err');
const previewUrl = @json(route('merge.preview'));
const token = document.querySelector('meta[name=csrf-token]').content;

let items = [];          // {id,file,ranges}
let uid = 0, timer, ctrl, blobUrl, blob, dragId = null;

const fmt = b => b > 1048576 ? (b/1048576).toFixed(1)+' MB' : Math.max(1,Math.round(b/1024))+' KB';

function addFiles(files){
    [...files].forEach(f => {
        if (f.type !== 'application/pdf' && !/\.pdf$/i.test(f.name)) return;
        items.push({id:++uid, file:f, ranges:''});
    });
    render(); schedule(0);
}

function render(){
    list.innerHTML = '';
    items.forEach((it, i) => {
        const el = document.createElement('div');
        el.className = 'item'; el.draggable = true; el.dataset.id = it.id;
        el.innerHTML = `
            <div class="num" title="Seret untuk mengurutkan"></div>
            <div class="meta">
                <div class="name"></div><div class="size"></div>
                <div class="rng"><input type="text" placeholder="Halaman: semua  ·  contoh 1-2, 5-6">
                    <button class="chip" data-v="">Semua</button><button class="chip" data-v="1">Hal. 1</button></div>
            </div>
            <div class="tools">
                <button class="ib" data-a="up" title="Naik">▲</button>
                <button class="ib" data-a="down" title="Turun">▼</button>
                <button class="ib x" data-a="del" title="Hapus">✕</button>
            </div>`;
        el.querySelector('.num').textContent = i + 1;
        el.querySelector('.name').textContent = it.file.name;
        el.querySelector('.size').textContent = fmt(it.file.size);
        const inp = el.querySelector('input'); inp.value = it.ranges;
        inp.oninput = () => { it.ranges = inp.value; schedule(); };
        el.querySelectorAll('.chip').forEach(c => c.onclick = () => { it.ranges = c.dataset.v; inp.value = it.ranges; schedule(0); });
        el.querySelectorAll('.ib').forEach(b => b.onclick = () => act(it.id, b.dataset.a));

        el.ondragstart = e => { dragId = it.id; el.classList.add('dragging'); e.dataTransfer.effectAllowed='move'; };
        el.ondragend = () => { dragId = null; el.classList.remove('dragging'); list.querySelectorAll('.target').forEach(x=>x.classList.remove('target')); };
        el.ondragover = e => { if (dragId && dragId !== it.id) { e.preventDefault(); el.classList.add('target'); } };
        el.ondragleave = () => el.classList.remove('target');
        el.ondrop = e => { e.preventDefault(); e.stopPropagation(); if (!dragId || dragId === it.id) return;
            const from = items.findIndex(x=>x.id===dragId), to = items.findIndex(x=>x.id===it.id);
            items.splice(to, 0, items.splice(from,1)[0]); render(); schedule(0); };
        list.appendChild(el);
    });
    go.disabled = !items.length;
}

function act(id, a){
    const i = items.findIndex(x => x.id === id);
    if (a === 'del') items.splice(i,1);
    if (a === 'up' && i > 0) [items[i-1], items[i]] = [items[i], items[i-1]];
    if (a === 'down' && i < items.length-1) [items[i+1], items[i]] = [items[i], items[i+1]];
    render(); schedule(0);
}

/* dropzone */
drop.onclick = () => picker.click();
picker.onchange = () => { addFiles(picker.files); picker.value = ''; };
['dragenter','dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); if(!dragId) drop.classList.add('over'); }));
['dragleave','drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('over'); }));
drop.addEventListener('drop', e => { if (e.dataTransfer.files.length) addFiles(e.dataTransfer.files); });
window.addEventListener('dragover', e => e.preventDefault());
window.addEventListener('drop', e => { e.preventDefault(); if (!dragId && e.dataTransfer.files.length) addFiles(e.dataTransfer.files); });

/* preview */
function setState(t, bad){ state.textContent = t; state.classList.toggle('err', !!bad); }
function showError(m){ errBox.textContent = m; errBox.classList.toggle('show', !!m); }

function schedule(ms = 600){ clearTimeout(timer); blob = null; timer = setTimeout(refresh, ms); }

async function refresh(){
    if (!items.length){
        showError(''); setState('Menunggu file'); viewer.style.display='none'; empty.style.display='flex'; blob = null; return;
    }
    const fd = new FormData(); fd.append('_token', token);
    items.forEach(it => { fd.append('files[]', it.file); fd.append('ranges[]', it.ranges); });

    ctrl?.abort(); ctrl = new AbortController();
    spin.classList.add('on'); setState('Memuat…');
    try{
        const res = await fetch(previewUrl, {method:'POST', body:fd, signal:ctrl.signal, headers:{'Accept':'application/json'}});
        if (!res.ok){
            const j = await res.json().catch(()=>({}));
            showError(j.message || Object.values(j.errors||{}).flat().join(' ') || 'Gagal membuat preview.');
            setState('Perlu diperbaiki', true); viewer.style.display='none'; empty.style.display='flex'; blob = null;
            return;
        }
        showError('');
        blob = await res.blob();
        if (blobUrl) URL.revokeObjectURL(blobUrl);
        blobUrl = URL.createObjectURL(blob);
        viewer.src = blobUrl + '#navpanes=1&view=FitH'; viewer.style.display='block'; empty.style.display='none';
        setState(items.length + ' file digabung');
    }catch(e){
        if (e.name === 'AbortError') return;
        showError('Gagal memuat preview. Coba lagi.'); setState('Gagal', true);
    }finally{ spin.classList.remove('on'); }
}

/* download */
go.onclick = async () => {
    if (!blob){ clearTimeout(timer); await refresh(); }
    if (!blob) return;
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob); a.download = 'merged.pdf'; a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 2000);
};
</script>
</body>
</html>
