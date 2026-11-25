@extends('layouts.app')

@section('title', 'Gabung PDF & pilih halaman — GabungPDF')
@section('description', 'Gabungkan beberapa PDF jadi satu, pilih halaman yang dibutuhkan, lihat hasilnya langsung. Gratis, tanpa login.')

@section('content')
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
@endsection

@push('scripts')
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
@endpush
