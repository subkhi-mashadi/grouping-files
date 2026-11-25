<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GabungPDF — Alat PDF online gratis')</title>
    <meta name="description" content="@yield('description', 'Gabung dan kompres PDF online. Gratis, tanpa login, file tidak disimpan.')">
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
        /* nav tabs */
        .tabs{display:flex;gap:4px;background:var(--surface);border:1px solid var(--line);padding:4px;border-radius:99px}
        .tabs a{text-decoration:none;color:var(--muted);font-weight:700;font-size:.88rem;padding:8px 18px;border-radius:99px;transition:.15s}
        .tabs a:hover{color:var(--ink)}
        .tabs a.on{background:linear-gradient(120deg,var(--brand),var(--brand2));color:#fff;box-shadow:0 6px 14px -6px rgba(99,102,241,.7)}
        @media(max-width:700px){.pill{display:none}}
        @stack('styles')
    </style>
</head>
<body>
<div class="blob a"></div><div class="blob b"></div>

<div class="wrap">
    <nav>
        <a class="logo" href="/"><i>⧉</i> GabungPDF</a>
        <div class="tabs">
            <a href="{{ route('merge.index') }}" class="{{ request()->routeIs('merge.*') ? 'on' : '' }}">Gabung PDF</a>
            <a href="{{ route('compress.index') }}" class="{{ request()->routeIs('compress.*') ? 'on' : '' }}">Kompres PDF</a>
        </div>
        <span class="pill">100% gratis · tanpa login</span>
    </nav>

    @yield('content')

    <footer>© {{ date('Y') }} GabungPDF · dibuat agar urusan PDF jadi gampang</footer>
</div>

@stack('scripts')
</body>
</html>
