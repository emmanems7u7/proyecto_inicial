{{-- resources/views/ventas/partials/panel_estilos.blade.php --}}
<style>
    .pd {
        --ink: #0f172a;
        --muted: #64748b;
        --line: #e8ecf4;
        --bg: #f6f8fc;
        --indigo: #4f46e5;
        --violet: #7c3aed;
        --emerald: #10b981;
        --sky: #0ea5e9;
        --amber: #f59e0b;
        --rose: #f43f5e;
        --r: 18px;
        color: var(--ink);
    }

    /* ---------- Entrada escalonada ---------- */
    @keyframes pd-up {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: none; }
    }
    .pd-in { opacity: 0; animation: pd-up .6s cubic-bezier(.2, .7, .2, 1) forwards; animation-delay: calc(var(--i, 0) * 70ms); }

    /* ---------- Hero ---------- */
    .pd-hero {
        position: relative; overflow: hidden; border-radius: 24px; padding: 28px 30px;
        background: linear-gradient(120deg, #1e1b4b 0%, #4338ca 55%, #7c3aed 100%);
        color: #fff; box-shadow: 0 20px 45px -18px rgba(67, 56, 202, .55);
    }
    .pd-orb { position: absolute; border-radius: 50%; filter: blur(40px); opacity: .45; pointer-events: none; animation: pd-float 9s ease-in-out infinite; }
    .pd-orb.a { width: 260px; height: 260px; right: -60px; top: -90px; background: #22d3ee; }
    .pd-orb.b { width: 220px; height: 220px; left: 30%; bottom: -130px; background: #f472b6; animation-delay: -4s; }
    @keyframes pd-float { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(-22px, 18px); } }

    .pd-hero > * { position: relative; z-index: 1; }
    .pd-hero h1 { font-size: 1.55rem; font-weight: 700; margin: 0; color: #fff; letter-spacing: -.01em; }
    .pd-hero .pd-sub { color: rgba(255, 255, 255, .75); font-size: .9rem; margin-top: 4px; }
    .pd-wave { display: inline-block; transform-origin: 70% 70%; animation: pd-wave 2.4s ease-in-out 1s 2; }
    @keyframes pd-wave { 0%, 60%, 100% { transform: rotate(0); } 10%, 30% { transform: rotate(16deg); } 20%, 40% { transform: rotate(-10deg); } 50% { transform: rotate(8deg); } }

    .pd-pill {
        display: inline-flex; align-items: center; gap: 8px; padding: 7px 14px; border-radius: 999px;
        background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .25);
        backdrop-filter: blur(8px); font-size: .8rem; color: #fff; text-decoration: none;
    }
    a.pd-pill:hover { background: rgba(255, 255, 255, .24); color: #fff; }
    .pd-live { position: relative; width: 9px; height: 9px; border-radius: 50%; background: #34d399; }
    .pd-live::after { content: ""; position: absolute; inset: 0; border-radius: 50%; background: #34d399; animation: pd-ping 1.8s ease-out infinite; }
    .pd-live.off { background: #94a3b8; }
    .pd-live.off::after { display: none; }
    @keyframes pd-ping { 0% { transform: scale(1); opacity: .8; } 100% { transform: scale(3.2); opacity: 0; } }

    /* ---------- Acciones rápidas ---------- */
    .pd-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-top: 22px; }
    .pd-action {
        display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px;
        background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .2);
        backdrop-filter: blur(8px); color: #fff; text-decoration: none; font-weight: 600; font-size: .85rem;
        transition: transform .25s, background .25s, box-shadow .25s;
    }
    .pd-action:hover { transform: translateY(-4px); background: rgba(255, 255, 255, .22); box-shadow: 0 14px 24px -12px rgba(0, 0, 0, .45); color: #fff; }
    .pd-action .ico {
        width: 38px; height: 38px; flex: none; display: grid; place-items: center; border-radius: 11px;
        background: var(--c); color: #fff; font-size: 1rem; box-shadow: 0 8px 16px -8px var(--c);
    }
    .pd-action:hover .ico i { animation: pd-wiggle .7s ease-in-out; }
    @keyframes pd-wiggle {
        0%, 100% { transform: rotate(0) scale(1); }
        25% { transform: rotate(-14deg) scale(1.2); }
        50% { transform: rotate(12deg) scale(1.2); }
        75% { transform: rotate(-6deg) scale(1.1); }
    }

    /* ---------- Tarjetas ---------- */
    .pd-card { background: #fff; border: 1px solid var(--line); border-radius: var(--r); box-shadow: 0 8px 24px -16px rgba(15, 23, 42, .25); height: 100%; }
    .pd-card-b { padding: 20px 22px; }
    .pd-title { display: flex; align-items: center; gap: 10px; font-size: .98rem; font-weight: 700; margin: 0 0 16px; color: var(--ink); }
    .pd-title .t-ico { width: 32px; height: 32px; border-radius: 10px; display: grid; place-items: center; font-size: .85rem; color: #fff; background: var(--c, var(--indigo)); }
    .pd-title .badge-n { margin-left: auto; }
    .pd-link { font-size: .8rem; font-weight: 600; color: var(--indigo); text-decoration: none; }
    .pd-link i { transition: transform .2s; }
    .pd-link:hover i { transform: translateX(4px); }

    /* ---------- KPI ---------- */
    .pd-kpi { position: relative; overflow: hidden; padding: 18px 20px; transition: transform .25s, box-shadow .25s; }
    .pd-kpi:hover { transform: translateY(-5px); box-shadow: 0 22px 34px -20px rgba(15, 23, 42, .4); }
    .pd-kpi::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--c); }
    .pd-kpi::after {
        content: ""; position: absolute; right: -30px; top: -30px; width: 110px; height: 110px; border-radius: 50%;
        background: var(--c); opacity: .08; transition: transform .5s;
    }
    .pd-kpi:hover::after { transform: scale(1.6); }
    .pd-kpi .lbl { font-size: .78rem; color: var(--muted); font-weight: 600; }
    .pd-kpi .val { font-size: 1.55rem; font-weight: 800; letter-spacing: -.02em; margin-top: 4px; line-height: 1.15; }
    .pd-kpi .foot { font-size: .75rem; margin-top: 6px; color: var(--muted); }
    .pd-kpi .foot.bad { color: var(--rose); font-weight: 600; }
    .pd-chip {
        position: absolute; right: 16px; top: 16px; width: 44px; height: 44px; border-radius: 14px; z-index: 1;
        display: grid; place-items: center; color: #fff; font-size: 1.05rem;
        background: linear-gradient(135deg, var(--c), var(--c2, var(--c))); box-shadow: 0 10px 18px -8px var(--c);
        animation: pd-bob 3.2s ease-in-out infinite;
    }
    @keyframes pd-bob { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }

    /* ---------- Gráfico de barras ---------- */
    .pd-bars { display: flex; align-items: flex-end; gap: 12px; height: 220px; padding-top: 10px; }
    .pd-col { flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; min-width: 0; }
    .pd-col .amt { font-size: .68rem; color: var(--muted); margin-bottom: 4px; white-space: nowrap; opacity: 0; transition: opacity .2s; }
    .pd-col:hover .amt, .pd-col.hoy .amt { opacity: 1; }
    .pd-col .bar {
        width: 100%; max-width: 46px; border-radius: 10px 10px 4px 4px; min-height: 4px;
        background: linear-gradient(180deg, #818cf8, #4f46e5); transform-origin: bottom;
        animation: pd-grow .9s cubic-bezier(.2, .8, .2, 1) backwards; animation-delay: calc(var(--k) * 90ms + .3s);
        transition: filter .2s, transform .2s;
    }
    .pd-col.hoy .bar { background: linear-gradient(180deg, #34d399, #059669); }
    .pd-col:hover .bar { filter: brightness(1.12); transform: scaleY(1.03); }
    .pd-col .dia { font-size: .75rem; color: var(--muted); margin-top: 8px; font-weight: 600; }
    @keyframes pd-grow { from { transform: scaleY(0); } to { transform: scaleY(1); } }

    /* ---------- Listas y medidores ---------- */
    .pd-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px dashed var(--line); }
    .pd-row:last-child { border-bottom: 0; }
    .pd-row .main { flex: 1; min-width: 0; }
    .pd-row .name { font-weight: 600; font-size: .86rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pd-row .meta { font-size: .73rem; color: var(--muted); }
    .pd-row .side { text-align: right; font-size: .82rem; font-weight: 700; white-space: nowrap; }
    .pd-row .side small { display: block; font-weight: 500; color: var(--muted); font-size: .72rem; }
    .pd-rank {
        width: 28px; height: 28px; flex: none; border-radius: 9px; display: grid; place-items: center;
        font-size: .75rem; font-weight: 800; color: var(--indigo); background: #eef2ff;
    }
    .pd-row:nth-child(1) .pd-rank { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #fff; }
    .pd-row:nth-child(2) .pd-rank { background: linear-gradient(135deg, #cbd5e1, #94a3b8); color: #fff; }
    .pd-row:nth-child(3) .pd-rank { background: linear-gradient(135deg, #fdba74, #ea580c); color: #fff; }
    .pd-meter { height: 6px; border-radius: 6px; background: #eef1f7; margin-top: 6px; overflow: hidden; }
    .pd-meter > span { display: block; height: 100%; border-radius: 6px; background: var(--c, var(--indigo)); animation: pd-fill 1.1s cubic-bezier(.2, .8, .2, 1) backwards .4s; }
    @keyframes pd-fill { from { width: 0; } }

    .pd-tag { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: .72rem; font-weight: 700; }
    .pd-tag.ok   { background: #d1fae5; color: #047857; }
    .pd-tag.warn { background: #fef3c7; color: #b45309; }
    .pd-tag.bad  { background: #ffe4e6; color: #be123c; }
    .pd-tag.info { background: #e0f2fe; color: #0369a1; }
    .pd-tag.pulse { animation: pd-tagpulse 1.6s ease-in-out infinite; }
    @keyframes pd-tagpulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, .45); } 50% { box-shadow: 0 0 0 6px rgba(244, 63, 94, 0); } }

    .pd-go {
        width: 32px; height: 32px; border-radius: 10px; display: grid; place-items: center; background: #eef2ff;
        color: var(--indigo); text-decoration: none; transition: .2s; flex: none;
    }
    .pd-go:hover { background: var(--indigo); color: #fff; transform: translateX(3px); }

    .pd-empty { text-align: center; padding: 26px 10px; color: var(--muted); font-size: .85rem; }
    .pd-empty i { display: block; font-size: 1.8rem; margin-bottom: 8px; color: var(--emerald); animation: pd-bob 2.6s ease-in-out infinite; }
    .pd-empty.neutral i { color: #cbd5e1; }

    /* ---------- Accesibilidad ---------- */
    @media (prefers-reduced-motion: reduce) {
        .pd *, .pd *::before, .pd *::after { animation: none !important; transition: none !important; }
        .pd-in { opacity: 1; }
    }
    @media (max-width: 575px) {
        .pd-hero { padding: 22px 18px; border-radius: 20px; }
        .pd-hero h1 { font-size: 1.25rem; }
        .pd-bars { gap: 6px; }
    }
</style>

{{-- Estilos antiguos que usan otras vistas --}}
@include('ventas.partials.tabla_estilos')