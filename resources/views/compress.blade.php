@extends('layouts.app')

@section('title', 'Kompres PDF — kecilkan ukuran file | GabungPDF')
@section('description', 'Kecilkan ukuran file PDF dengan pilihan kualitas. Gratis, tanpa login, file tidak disimpan.')

@push('styles')
        .levels{display:grid;gap:10px;margin:16px 0}
        .lv{display:flex;gap:12px;align-items:center;padding:13px 14px;border:2px solid var(--line);border-radius:14px;cursor:pointer;transition:.15s;background:var(--surface)}
        .lv:hover{border-color:#c7cbf5}
        .lv input{display:none}
        .lv:has(input:checked){border-color:var(--brand);background:var(--brand-soft);box-shadow:0 0 0 3px var(--brand-soft)}
        .lv .e{font-size:1.4rem}
        .lv b{display:block;font-size:.95rem}.lv small{color:var(--muted)}
        .lv .tag{margin-left:auto;font-size:.7rem;font-weight:800;color:var(--brand);background:var(--surface);padding:4px 9px;border-radius:99px;border:1px solid var(--line)}
        .result{display:none;margin-bottom:16px;border-radius:16px;padding:16px;background:linear-gradient(135deg,var(--brand-soft),transparent);border:1px solid var(--line)}
        .result.show{display:block;animation:pop .3s ease}
        .result .big{font-size:2.2rem;font-weight:800;letter-spacing:-.03em;line-height:1}
        .result .row{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
        .bars{margin-top:12px;display:grid;gap:6px}
        .bar2{height:10px;border-radius:99px;background:var(--line);overflow:hidden}
        .bar2 i{display:block;height:100%;border-radius:99px;transition:width .6s ease}
        .bar2.o i{background:#94a3b8;width:100%}.bar2.n i{background:linear-gradient(90deg,var(--brand),var(--brand2))}
        .bl{display:flex;justify-content:space-between;font-size:.8rem;color:var(--muted);font-weight:600}
@endpush

@section('content')
    <header class="hero">
        <h1>Kecilkan PDF, <span class="grad">tanpa ribet.</span></h1>
        <p>Pilih tingkat kompresi, lihat berapa ukuran yang berhasil dihemat, lalu unduh hasilnya.</p>
        <div class="badges"><span>3 level kompresi</span><span>Lihat hasil sebelum unduh</span><span>File tidak disimpan</span></div>
    </header>

    <main class="app">
        <section class="card preview">
            <div class="bar">
                <div class="dots"><b></b><b></b><b></b></div>
                <div class="title">Hasil kompresi</div>
                <span class="state" id="state">Menunggu file</span>
            </div>
            <div class="frame">
                <div class="empty" id="empty">
                    <div class="stack"><div class="sheet two"></div><div class="sheet"></div></div>
                    <div><b style="color:var(--ink)">Preview muncul di sini</b><br>Pilih file dan tingkat kompresi, lalu klik Kompres.</div>
                </div>
                <iframe id="viewer" title="Preview PDF"></iframe>
                <div class="spin" id="spin"></div>
            </div>
        </section>

        <section class="card tool">
            <h2>Kompres file PDF</h2>
            <p class="sub">Satu file per proses. Maks 50 MB.</p>

            <div class="drop" id="drop">
                <div class="ic">＋</div>
                <b id="dropTitle">Klik atau tarik file PDF ke sini</b>
                <small id="dropSub">Satu file · maks 50 MB</small>
                <input type="file" id="picker" accept="application/pdf" hidden>
            </div>

            <div class="levels">
                <label class="lv"><input type="radio" name="level" value="low"><span class="e">🖼️</span><span><b>Ringan</b><small>Kualitas tetap tajam, hemat sedikit</small></span></label>
                <label class="lv"><input type="radio" name="level" value="medium" checked><span class="e">⚖️</span><span><b>Seimbang</b><small>Kualitas bagus, ukuran jauh lebih kecil</small></span><span class="tag">DISARANKAN</span></label>
                <label class="lv"><input type="radio" name="level" value="high"><span class="e">📦</span><span><b>Maksimal</b><small>Ukuran terkecil, gambar lebih buram</small></span></label>
            </div>

            <div class="result" id="result">
                <div class="row"><div><div class="big grad" id="saved">0%</div><small style="color:var(--muted)">lebih kecil</small></div>
                    <div style="text-align:right;font-weight:700"><span id="beforeTxt"></span> → <span id="afterTxt"></span></div></div>
                <div class="bars">
                    <div><div class="bl"><span>Sebelum</span><span id="b1"></span></div><div class="bar2 o"><i></i></div></div>
                    <div><div class="bl"><span>Sesudah</span><span id="b2"></span></div><div class="bar2 n"><i id="nbar" style="width:100%"></i></div></div>
                </div>
            </div>

            <div class="err" id="err"></div>
            <button class="cta" id="go" disabled>🗜️ Kompres PDF</button>
            <button class="cta" id="dl" style="display:none;margin-top:10px">⬇ Unduh PDF Terkompresi</button>
        </section>
    </main>

    <section class="features">
        <div class="card f"><div class="e">🗜️</div><h3>Tiga level kompresi</h3><p>Dari ringan sampai maksimal. Pilih sesuai kebutuhan: kirim email, upload formulir, atau arsip.</p></div>
        <div class="card f"><div class="e">📊</div><h3>Hasil transparan</h3><p>Lihat ukuran sebelum dan sesudah, plus persen yang dihemat, sebelum Anda mengunduh.</p></div>
        <div class="card f"><div class="e">🔒</div><h3>Privat &amp; cepat</h3><p>Tanpa akun. File hanya diproses sesaat dan tidak disimpan di server.</p></div>
    </section>
@endsection

@push('scripts')
<script>
const $ = id => document.getElementById(id);
const drop=$('drop'), picker=$('picker'), go=$('go'), dl=$('dl'), viewer=$('viewer'), empty=$('empty'),
      state=$('state'), spin=$('spin'), errBox=$('err'), result=$('result');
const url = @json(route('compress.store'));
const token = document.querySelector('meta[name=csrf-token]').content;
const fmt = b => b > 1048576 ? (b/1048576).toFixed(2)+' MB' : Math.max(1,Math.round(b/1024))+' KB';

let file = null, blob = null, blobUrl = null, outName = 'compressed.pdf';

function setState(t,bad){ state.textContent=t; state.classList.toggle('err',!!bad); }
function showError(m){ errBox.textContent=m; errBox.classList.toggle('show',!!m); }
function reset(){ blob=null; dl.style.display='none'; result.classList.remove('show'); go.style.display=''; }

function setFile(f){
    if (!f) return;
    if (f.type !== 'application/pdf' && !/\.pdf$/i.test(f.name)) { showError('File harus berformat PDF.'); return; }
    file = f; showError(''); reset();
    outName = f.name.replace(/\.pdf$/i,'') + '-compressed.pdf';
    $('dropTitle').textContent = f.name; $('dropSub').textContent = fmt(f.size) + ' · klik untuk ganti file';
    go.disabled = false; setState('Siap dikompres');
}

drop.onclick = () => picker.click();
picker.onchange = () => { setFile(picker.files[0]); picker.value=''; };
['dragenter','dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('over'); }));
['dragleave','drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('over'); }));
drop.addEventListener('drop', e => setFile(e.dataTransfer.files[0]));
window.addEventListener('dragover', e => e.preventDefault());
window.addEventListener('drop', e => { e.preventDefault(); setFile(e.dataTransfer.files[0]); });
document.querySelectorAll('input[name=level]').forEach(r => r.onchange = () => { if (file) { reset(); setState('Siap dikompres'); } });

go.onclick = async () => {
    if (!file) return;
    const fd = new FormData();
    fd.append('_token', token); fd.append('file', file);
    fd.append('level', document.querySelector('input[name=level]:checked').value);
    spin.classList.add('on'); go.disabled = true; setState('Mengompres…'); showError('');
    try{
        const res = await fetch(url, {method:'POST', body:fd, headers:{'Accept':'application/json'}});
        if (!res.ok){
            const j = await res.json().catch(()=>({}));
            showError(j.message || Object.values(j.errors||{}).flat().join(' ') || 'Gagal mengompres file.');
            setState('Gagal', true); return;
        }
        const before = +res.headers.get('X-Original-Size'), after = +res.headers.get('X-Compressed-Size');
        blob = await res.blob();
        if (blobUrl) URL.revokeObjectURL(blobUrl);
        blobUrl = URL.createObjectURL(blob);
        viewer.src = blobUrl + '#navpanes=1&view=FitH'; viewer.style.display='block'; empty.style.display='none';

        const pct = Math.max(0, Math.round((1 - after/before) * 100));
        $('saved').textContent = pct + '%';
        $('beforeTxt').textContent = fmt(before); $('afterTxt').textContent = fmt(after);
        $('b1').textContent = fmt(before); $('b2').textContent = fmt(after);
        $('nbar').style.width = Math.max(4, after/before*100) + '%';
        result.classList.add('show'); dl.style.display='block'; go.style.display='none';
        setState(pct ? 'Hemat ' + pct + '%' : 'Sudah optimal');
        if (!pct) showError('File ini sudah cukup kecil, tidak banyak yang bisa dihemat.');
    }catch(e){
        showError('Gagal menghubungi server. Coba lagi.'); setState('Gagal', true);
    }finally{ spin.classList.remove('on'); go.disabled = false; }
};

dl.onclick = () => {
    if (!blob) return;
    const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = outName; a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 2000);
};
</script>
@endpush
