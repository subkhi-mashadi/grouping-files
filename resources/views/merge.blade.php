<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gabung PDF</title>
    <style>
        *{box-sizing:border-box}
        body{font-family:system-ui,sans-serif;background:#f4f5f7;margin:0;padding:24px;color:#1f2937}
        .layout{max-width:1200px;margin:auto;display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start}
        @media (max-width:900px){.layout{grid-template-columns:1fr}}
        .card{background:#fff;padding:20px;border-radius:12px;box-shadow:0 1px 4px #0002}
        .preview{position:sticky;top:24px;display:flex;flex-direction:column;height:calc(100vh - 48px);min-height:420px}
        @media (max-width:900px){.preview{position:static;height:70vh}}
        h1{margin:0 0 4px;font-size:1.3rem}
        h2{margin:0 0 12px;font-size:1.1rem;display:flex;justify-content:space-between;align-items:center}
        .status{font-size:.8rem;font-weight:400;color:#6b7280}
        .frame{flex:1;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;position:relative;background:#f9fafb}
        .frame iframe{width:100%;height:100%;border:0;display:none}
        .empty{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;text-align:center;padding:24px;color:#9ca3af}
        .row{display:flex;gap:6px;align-items:center;padding:10px;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:8px;background:#fafafa}
        .row input[type=file]{flex:2;min-width:0;font-size:.85rem}
        .row input[type=text]{flex:1;padding:8px;border:1px solid #d1d5db;border-radius:6px;min-width:0}
        button{cursor:pointer;border:0;border-radius:6px;padding:8px 12px;font-size:.9rem}
        .primary{background:#2563eb;color:#fff;padding:10px 18px}
        .ghost{background:#e5e7eb}
        .danger{background:#fee2e2;color:#b91c1c}
        .err{background:#fee2e2;color:#b91c1c;padding:10px;border-radius:6px;margin-bottom:12px;display:none}
        .err.show{display:block}
        small.hint{color:#6b7280;display:block;margin:0 0 14px}
        .actions{display:flex;gap:8px;margin-top:12px}
    </style>
</head>
<body>
<div class="layout">

    {{-- KIRI: preview hasil akhir --}}
    <div class="card preview">
        <h2>Preview Hasil Akhir <span class="status" id="status">Belum ada file</span></h2>
        <div class="frame">
            <div class="empty" id="empty">Pilih file PDF di sebelah kanan.<br>Preview hasil gabungan akan muncul di sini otomatis.</div>
            <iframe id="viewer" title="Preview PDF"></iframe>
        </div>
    </div>

    {{-- KANAN: form --}}
    <div class="card">
        <h1>Gabung PDF + Pilih Halaman</h1>
        <small class="hint">Isi kolom halaman, mis. <b>1-2</b> atau <b>1-2, 5-6</b>. Kosong = semua halaman. Urutan baris = urutan hasil gabungan.</small>

        <div class="err {{ $errors->any() ? 'show' : '' }}" id="err">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>

        <form method="POST" id="form" action="{{ route('merge.store') }}" enctype="multipart/form-data">
            @csrf
            <div id="rows"></div>
            <div class="actions">
                <button type="button" class="ghost" onclick="addRow()">+ Tambah file</button>
                <button type="submit" class="primary">Gabungkan &amp; Unduh</button>
            </div>
        </form>
    </div>
</div>

<template id="tpl">
    <div class="row">
        <button type="button" class="ghost" onclick="move(this,-1)">↑</button>
        <button type="button" class="ghost" onclick="move(this,1)">↓</button>
        <input type="file" name="files[]" accept="application/pdf">
        <input type="text" name="ranges[]" placeholder="Halaman: 1-2, 5-6">
        <button type="button" class="danger" onclick="removeRow(this)">✕</button>
    </div>
</template>

<script>
    const rows = document.getElementById('rows');
    const form = document.getElementById('form');
    const viewer = document.getElementById('viewer');
    const empty = document.getElementById('empty');
    const status = document.getElementById('status');
    const errBox = document.getElementById('err');
    const previewUrl = @json(route('merge.preview'));
    let timer, controller, blobUrl;

    function addRow(){ rows.appendChild(document.getElementById('tpl').content.cloneNode(true)); }
    function removeRow(btn){ btn.parentElement.remove(); schedule(); }
    function move(btn, dir){
        const row = btn.parentElement;
        const sib = dir < 0 ? row.previousElementSibling : row.nextElementSibling;
        if (!sib) return;
        dir < 0 ? rows.insertBefore(row, sib) : rows.insertBefore(sib, row);
        schedule();
    }

    rows.addEventListener('change', schedule);
    rows.addEventListener('input', e => { if (e.target.type === 'text') schedule(); });
    form.addEventListener('submit', e => {
        // hanya kirim baris yang ada filenya
        if (![...rows.querySelectorAll('input[type=file]')].some(f => f.files.length)) {
            e.preventDefault(); showError('Pilih minimal satu file PDF.');
        } else {
            [...rows.children].forEach(r => { if (!r.querySelector('input[type=file]').files.length) r.remove(); });
        }
    });

    function schedule(){ clearTimeout(timer); timer = setTimeout(refresh, 600); }

    function showError(msg){ errBox.textContent = msg; errBox.classList.toggle('show', !!msg); }

    function setEmpty(text){
        viewer.style.display = 'none'; empty.style.display = 'flex';
        empty.innerHTML = text;
    }

    async function refresh(){
        const fd = new FormData();
        fd.append('_token', form.querySelector('[name=_token]').value);
        let n = 0;
        [...rows.children].forEach(r => {
            const f = r.querySelector('input[type=file]').files[0];
            if (!f) return;
            fd.append('files[]', f);
            fd.append('ranges[]', r.querySelector('input[type=text]').value);
            n++;
        });

        if (!n) { showError(''); status.textContent = 'Belum ada file'; setEmpty('Pilih file PDF di sebelah kanan.<br>Preview hasil gabungan akan muncul di sini otomatis.'); return; }

        controller?.abort(); controller = new AbortController();
        status.textContent = 'Memuat preview…';

        try {
            const res = await fetch(previewUrl, { method:'POST', body:fd, signal:controller.signal, headers:{'Accept':'application/json'} });
            if (!res.ok) {
                const j = await res.json().catch(() => ({}));
                const msg = j.message || Object.values(j.errors || {}).flat().join(' ') || 'Gagal membuat preview.';
                showError(msg); status.textContent = 'Preview gagal';
                setEmpty('Preview tidak tersedia.<br>Periksa file / format halaman.');
                return;
            }
            showError('');
            const blob = await res.blob();
            if (blobUrl) URL.revokeObjectURL(blobUrl);
            blobUrl = URL.createObjectURL(blob);
            viewer.src = blobUrl;
            viewer.style.display = 'block'; empty.style.display = 'none';
            status.textContent = n + ' file digabung';
        } catch (e) {
            if (e.name !== 'AbortError') { showError('Gagal memuat preview.'); status.textContent = 'Preview gagal'; }
        }
    }

    addRow(); addRow(); addRow();
</script>
</body>
</html>
