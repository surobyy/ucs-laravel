<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Uniform Cost Search — Pencari Jalur Termurah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700&family=IBM+Plex+Mono:wght@400;500&family=Public+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #e9f0ee;
            --panel: #ffffff;
            --ink: #12333a;
            --muted: #587078;
            --line: #cddad7;
            --accent: #0e7c7b;
            --accent-ink: #ffffff;
            --route: #22c55e;
            --route-soft: #dcfce7;
            --bad: #b3261e;
            --reject: #dc2626;
            --reject-soft: #fee2e2;
            --soft: #f3f8f7;
            --display: "Bricolage Grotesque", "Segoe UI", system-ui, sans-serif;
            --body: "Public Sans", "Segoe UI", system-ui, sans-serif;
            --mono: "IBM Plex Mono", ui-monospace, Consolas, monospace;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0d1c20;
                --panel: #14272c;
                --ink: #e3f0ee;
                --muted: #8fa9ad;
                --line: #26424a;
                --accent: #3cc4c0;
                --accent-ink: #062326;
                --route: #4ade80;
                --route-soft: #143223;
                --reject: #f87171;
                --reject-soft: #4a1920;
                --soft: #102226;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font: 16px/1.6 var(--body);
        }

        .wrap {
            max-width: 1180px;
            margin: 0 auto;
            padding: 36px 20px 72px;
        }

        header {
            margin-bottom: 28px;
            max-width: 64ch;
        }

        h1 {
            font: 700 clamp(2rem, 4.5vw, 3rem)/1.1 var(--display);
            letter-spacing: -.02em;
            margin: 0 0 10px;
        }

        header p {
            margin: 0;
            color: var(--muted);
        }

        h2 {
            font: 700 1.1rem/1.3 var(--display);
            margin: 0 0 12px;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(280px, 340px) 1fr;
            gap: 28px;
            align-items: start;
        }

        @media (max-width:860px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }

        form {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 20px;
            display: grid;
            gap: 16px;
            position: sticky;
            top: 16px;
        }

        @media (max-width:860px) {
            form {
                position: static;
            }
        }

        label {
            display: grid;
            gap: 6px;
            font-weight: 600;
            font-size: .92rem;
        }

        .hint {
            font-weight: 400;
            color: var(--muted);
            font-size: .85rem;
        }

        input,
        textarea {
            font: 400 .9rem/1.5 var(--mono);
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: var(--soft);
            color: var(--ink);
            width: 100%;
        }

        textarea {
            min-height: 260px;
            resize: vertical;
        }

        input:focus-visible,
        textarea:focus-visible,
        button:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        .pair {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        button {
            font: 600 1rem var(--body);
            padding: 12px 20px;
            background: var(--accent);
            color: var(--accent-ink);
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            filter: brightness(1.08);
        }

        .alert {
            color: var(--bad);
            font-size: .9rem;
            font-weight: 600;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .graph-box {
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 8px;
        }

        svg {
            display: block;
            width: 100%;
            height: auto;
        }

        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 20px;
            margin: 12px 0 0;
            font-size: .85rem;
            color: var(--muted);
        }

        .legend span::before {
            content: "";
            display: inline-block;
            width: 22px;
            height: 4px;
            border-radius: 2px;
            margin-right: 8px;
            vertical-align: middle;
            background: var(--line);
        }

        .legend .l-route::before {
            background: var(--route);
        }

        .legend .l-queue::before {
            background: var(--reject);
            width: 22px;
            height: 4px;
            border-radius: 2px;
            opacity: .9;
        }

        .legend .l-node::before {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--accent);
        }

        .e {
            stroke: var(--line);
            stroke-width: 2;
            stroke-dasharray: 8 7;
        }

        .e.on {
            stroke: var(--route);
            stroke-width: 5;
            stroke-linecap: round;
            stroke-dasharray: 0;
        }

        .e.reject {
            stroke: var(--reject);
            stroke-width: 3.5;
            stroke-linecap: round;
            stroke-dasharray: 8 7;
            opacity: .9;
        }

        .w {
            fill: var(--muted);
            font: 500 11px var(--mono);
            paint-order: stroke;
            stroke: var(--soft);
            stroke-width: 4px;
        }

        .w.on {
            fill: var(--ink);
            font-weight: 700;
        }

        .w.reject {
            fill: var(--reject);
            font-weight: 700;
        }

        .n rect {
            fill: var(--panel);
            stroke: var(--line);
            stroke-width: 2;
        }

        .n text {
            fill: var(--ink);
            font: 600 13px var(--body);
        }

        .n.reject rect {
            fill: var(--reject-soft);
            stroke: var(--reject);
        }

        .n.reject text {
            fill: var(--reject);
        }

        .n.on rect {
            stroke: var(--route);
            fill: var(--route-soft);
        }

        .n.start rect {
            fill: var(--accent);
            stroke: var(--accent);
        }

        .n.start text {
            fill: var(--accent-ink);
        }

        .n.goal rect {
            fill: var(--route);
            stroke: var(--route);
        }

        .n.goal text {
            fill: #2b1c00;
        }

        .badge circle {
            fill: var(--ink);
        }

        .badge text {
            fill: var(--bg);
            font: 600 10px var(--mono);
        }

        .summary {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .chip {
            background: var(--soft);
            border: 1px solid var(--line);
            padding: 4px 12px;
            border-radius: 999px;
            font-weight: 600;
        }

        .chip:first-child {
            background: var(--accent);
            color: var(--accent-ink);
            border-color: var(--accent);
        }

        .chip:last-of-type {
            background: var(--route);
            color: #2b1c00;
            border-color: var(--route);
        }

        .arrow {
            color: var(--muted);
        }

        .total {
            font: 700 2.2rem/1 var(--display);
        }

        .total small {
            font: 400 .95rem var(--body);
            color: var(--muted);
            margin-left: 6px;
        }

        .fail {
            border-left: 5px solid var(--bad);
        }

        .fail strong {
            color: var(--bad);
        }

        .scroll {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .92rem;
        }

        th,
        td {
            text-align: left;
            padding: 9px 10px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        th {
            font-weight: 600;
            color: var(--muted);
            font-size: .85rem;
        }

        td.mono {
            font: 400 .84rem/1.5 var(--mono);
        }

        tr.last td {
            background: var(--soft);
            font-weight: 600;
        }

        @media (prefers-reduced-motion: no-preference) {

            .e,
            .n rect {
                transition: stroke .2s, fill .2s;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <header>
            <h1>Uniform Cost Search</h1>
            <p>Temukan jalur dengan total biaya paling kecil di antara dua titik. Ubah graf di sebelah kiri dan
                pratinjaunya langsung berubah.</p>
        </header>

        <div class="layout">
            <form method="POST" action="{{ route('ucs') }}">
                @csrf
                <label>Jalur / hubungan
                    <span class="hint">Satu baris per hubungan: TitikA,TitikB,biaya. Berlaku dua arah.</span>
                    <textarea id="edges" name="edges" spellcheck="false" required>{{ old('edges', $edges) }}</textarea>
                </label>
                <div class="pair">
                    <label>Titik awal <input id="start" name="start" value="{{ old('start', $start) }}"
                            required></label>
                    <label>Titik tujuan <input id="goal" name="goal" value="{{ old('goal', $goal) }}"
                            required></label>
                </div>
                @if ($errors->any())
                    <div class="alert" role="alert">{{ $errors->first() }}</div>
                @endif
                @if ($error)
                    <div class="alert" role="alert">{{ $error }}</div>
                @endif
                <button type="submit">Cari jalur</button>
            </form>

            <div>
                @if ($result)
                    <div class="card {{ $result['found'] ? '' : 'fail' }}">
                        @if ($result['found'])
                            <div class="summary">
                                @foreach ($result['path'] as $i => $node)
                                    @if ($i > 0)
                                        <span class="arrow" aria-hidden="true">→</span>
                                    @endif
                                    <span class="chip">{{ $node }}</span>
                                @endforeach
                            </div>
                            <div class="total">
                                {{ rtrim(rtrim(number_format($result['cost'], 2, '.', ''), '0'), '.') }}<small>total
                                    biaya</small></div>
                        @else
                            <strong>Tidak ada jalur</strong> dari {{ $start }} ke {{ $goal }}. Periksa
                            apakah kedua titik saling terhubung.
                        @endif
                    </div>
                @endif

                <div class="card">
                    <h2>Graf</h2>
                    <div class="graph-box"><svg id="graph" viewBox="0 0 700 440" role="img"
                            aria-label="Visualisasi graf"></svg></div>
                    <div class="legend">
                        <span class="l-route">Jalur berhasil</span>
                        <span class="l-queue">Titik yang dicek</span>
                        <span class="l-node">Angka kecil = urutan pengecekan</span>
                    </div>
                </div>

                @if ($result)
                    <div class="card">
                        <h2>Langkah pencarian</h2>
                        <div class="scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Langkah</th>
                                        <th>Titik yang dicek</th>
                                        <th>g(n)</th>
                                        <th>Sisa antrean yang belum dipilih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($result['steps'] as $i => $s)
                                        <tr class="{{ $loop->last && $result['found'] ? 'last' : '' }}">
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $s['expand'] }}</td>
                                            <td>{{ $s['cost'] }}</td>
                                            <td class="mono">
                                                {{ $s['frontier'] ? implode(', ', $s['frontier']) : '(kosong)' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        (function() {
            const NS = 'http://www.w3.org/2000/svg';
            const svg = document.getElementById('graph');
            const $edges = document.getElementById('edges');
            const $start = document.getElementById('start');
            const $goal = document.getElementById('goal');
            const PATH = @json($result['path'] ?? []);
            const ORDER = @json(collect($result['steps'] ?? [])->pluck('expand')->values());
            let showResult = PATH.length > 0 || ORDER.length > 0;

            const el = (name, attrs = {}, text) => {
                const n = document.createElementNS(NS, name);
                for (const k in attrs) n.setAttribute(k, attrs[k]);
                if (text !== undefined) n.textContent = text;
                return n;
            };

            function parse(text) {
                const out = [];
                text.split(/\r?\n/).forEach(line => {
                    const p = line.split(',').map(s => s.trim());
                    if (p.length === 3 && p[0] && p[1] && p[2] !== '' && !isNaN(p[2])) out.push(p);
                });
                return out;
            }

            function render() {
                const edges = parse($edges.value);
                const names = [];
                edges.forEach(([a, b]) => {
                    [a, b].forEach(n => {
                        if (!names.includes(n)) names.push(n);
                    });
                });
                svg.replaceChildren();
                if (!names.length) return;

                const cx = 350,
                    cy = 220,
                    rx = 270,
                    ry = 165;
                const pos = {};
                names.forEach((n, i) => {
                    const t = (i / names.length) * Math.PI * 2 - Math.PI / 2;
                    pos[n] = {
                        x: cx + rx * Math.cos(t),
                        y: cy + ry * Math.sin(t)
                    };
                });

                const pathSet = new Set(PATH);
                const checkedSet = new Set(ORDER.filter(n => !pathSet.has(n)));
                const onPath = new Set();
                if (showResult) {
                    for (let i = 0; i < PATH.length - 1; i++) {
                        const key = [PATH[i], PATH[i + 1]].sort().join('|');
                        onPath.add(key);
                    }
                }

                const gEdges = el('g'),
                    gLabels = el('g'),
                    gNodes = el('g');
                edges.forEach(([a, b, c]) => {
                    const key = [a, b].sort().join('|');
                    const on = onPath.has(key);
                    const checked = showResult && (checkedSet.has(a) || checkedSet.has(b));
                    const cls = showResult ? (on ? 'e on' : checked ? 'e reject' : 'e') : 'e';
                    gEdges.appendChild(el('line', {
                        class: cls,
                        x1: pos[a].x,
                        y1: pos[a].y,
                        x2: pos[b].x,
                        y2: pos[b].y
                    }));
                    gLabels.appendChild(el('text', {
                        class: showResult ? (on ? 'w on' : checked ? 'w reject' : 'w') : 'w',
                        x: (pos[a].x + pos[b].x) / 2,
                        y: (pos[a].y + pos[b].y) / 2 + 4,
                        'text-anchor': 'middle'
                    }, String(parseFloat(c))));
                });

                const start = $start.value.trim(),
                    goal = $goal.value.trim();
                names.forEach(n => {
                    const w = Math.max(56, n.length * 8 + 24),
                        h = 30;
                    let cls = 'n';
                    if (showResult && PATH.includes(n)) cls += ' on';
                    else if (showResult && checkedSet.has(n)) cls += ' reject';
                    if (n === start) cls += ' start';
                    else if (n === goal) cls += ' goal';
                    const g = el('g', {
                        class: cls,
                        transform: `translate(${pos[n].x},${pos[n].y})`
                    });
                    g.appendChild(el('rect', {
                        x: -w / 2,
                        y: -h / 2,
                        width: w,
                        height: h,
                        rx: 15
                    }));
                    g.appendChild(el('text', {
                        'text-anchor': 'middle',
                        y: 5
                    }, n));
                    const k = showResult ? ORDER.indexOf(n) : -1;
                    if (k >= 0) {
                        const b = el('g', {
                            class: 'badge',
                            transform: `translate(${w / 2 - 2},${-h / 2})`
                        });
                        b.appendChild(el('circle', {
                            r: 9
                        }));
                        b.appendChild(el('text', {
                            'text-anchor': 'middle',
                            y: 3.5
                        }, String(k + 1)));
                        g.appendChild(b);
                    }
                    gNodes.appendChild(g);
                });
                svg.append(gEdges, gLabels, gNodes);
            }

            [$edges, $start, $goal].forEach(i => i.addEventListener('input', () => {
                showResult = false;
                render();
            }));
            render();
        })();
    </script>
</body>

</html>
