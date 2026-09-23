<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <title>Boarded-out letter {{ $letter->reference_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 8mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; background: #fff; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10.5pt; line-height: 1.45; }
        .letter { width: 100%; }
        .logo { display: block; width: auto; height: 70px; margin: 0 auto 7px; }
        .masthead { margin-bottom: 10px; text-align: center; }
        .masthead-title { margin: 0 0 2px; font-size: 12pt; font-weight: 700; }
        .masthead-grid { display: table; width: 100%; margin-top: 8px; text-align: left; font-size: 10pt; }
        .masthead-grid > div { display: table-cell; width: 50%; vertical-align: top; }
        .masthead-grid > div:last-child { text-align: right; }
        p { margin: 0 0 6px; }
        .rule { margin: 8px 0; border: 0; border-top: 2px solid #111; }
        .meta { display: table; width: 100%; margin: 8px 0 12px; font-weight: 600; }
        .meta > div { display: table-cell; width: 50%; }
        .meta > div:last-child { text-align: right; }
        .recipient { margin-bottom: 15px; }
        .uppercase { text-transform: uppercase; font-weight: 700; }
        .subject { margin-bottom: 12px; text-align: center; font-weight: 700; text-transform: uppercase; }
        .body { line-height: 1.62; }
        .body p { margin-bottom: 10px; }
        .recommendations { margin: 0 0 12px 24px; padding: 0; font-weight: 700; }
        .signature-wrap { height: 70px; display: flex; align-items: center; margin-top: 8px; }
        .signature { display: block; width: 220px; height: 70px; object-fit: contain; object-position: left center; }
        .signatory { margin-top: 3px; }
        .footer { margin-top: 12px; text-align: center; color: #444; font-size: 8.5pt; }
        .footer .rule { margin: 8px 0 6px; }
        .copy { margin-top: 14px; font-size: 9pt; }
    </style>
</head>
<body>
<main class="letter">
    <img class="logo" src="{{ $logoData }}" alt="Government of Zanzibar">

    @if ($language === 'en')
        <div class="masthead">
            <p class="masthead-title">REVOLUTIONARY GOVERNMENT OF ZANZIBAR</p>
            <p class="masthead-title">MINISTRY OF HEALTH</p>
            <div class="masthead-grid">
                <div>
                    <p>6 Barabara ya Health Office</p>
                    <p>S.L.P 236 MNAZIMMOJA</p>
                    <p>POSTIKODI 70467 Mjini Magharibi Zanzibar</p>
                </div>
                <div>
                    <p>Website: www.mohz.go.tz</p>
                    <p>Email: {{ $email }}</p>
                </div>
            </div>
        </div>

        <hr class="rule">
        <div class="meta">
            <div><strong>Ref:</strong> {{ $letter->reference_number ?: 'N/A' }}</div>
            <div>Date: {{ $letter->created_at?->format('d/m/Y') ?: 'N/A' }}</div>
        </div>

        <div class="recipient uppercase">
            @foreach (preg_split('/\s*,\s*/', (string) $letter->receiver, -1, PREG_SPLIT_NO_EMPTY) as $line)
                <p>{{ trim($line) }}{{ !$loop->last ? ',' : '' }}</p>
            @endforeach
        </div>

        <p class="subject">RE: MEDICAL BOARD ASSESSMENT OF {{ $patient?->name ?? 'N/A' }}</p>

        <div class="body">
            <p>Please refer to our correspondence with reference <strong>{{ $letter->reference_number ?: 'N/A' }}</strong>, dated <strong>{{ $letter->reference_date?->format('d/m/Y') ?: 'N/A' }}</strong>, on the above subject.</p>
            <p>The Medical Board, at its meeting held on <strong>{{ $boardDate }}</strong>, met and reviewed the case of patient <strong>{{ $patient?->name ?? 'N/A' }}</strong>.</p>
            <p>After detailed consideration of the report, the Board reached the following decision:</p>
            <ul class="recommendations">
                @foreach (($letter->recommendations ?: []) as $recommendation)
                    <li>{{ $recommendation }}</li>
                @endforeach
            </ul>
            <p>Attached to this letter is a copy of the original report containing the decision.</p>
            <p><strong><em>Yours faithfully,</em></strong></p>
        </div>
    @else
        <div class="masthead">
            <p class="masthead-title">SERIKALI YA MAPINDUZI YA ZANZIBAR</p>
            <p class="masthead-title">WIZARA YA AFYA</p>
            <div class="masthead-grid">
                <div>
                    <p>6 Barabara ya Health Office</p>
                    <p>S.L.P 236 MNANZIMMOJA</p>
                    <p>POSTIKODI 70467 Mjini Magharibi Zanzibar</p>
                </div>
                <div>
                    <p>Tovuti: www.mohz.go.tz</p>
                    <p>Barua pepe: {{ $email }}</p>
                </div>
            </div>
        </div>

        <hr class="rule">
        <div class="meta">
            <div><strong>KUMB:</strong> {{ $letter->reference_number ?: 'N/A' }}</div>
            <div><strong>Tarehe:</strong> {{ $letter->created_at?->format('d/m/Y') ?: 'N/A' }}</div>
        </div>

        <div class="recipient uppercase">
            @foreach (preg_split('/\s*,\s*/', (string) $letter->receiver, -1, PREG_SPLIT_NO_EMPTY) as $line)
                <p>{{ trim($line) }}{{ !$loop->last ? ',' : '' }}</p>
            @endforeach
        </div>

        <p class="subject">KUH: UCHUNGUZI WA AFYA WA {{ $patient?->name ?? 'N/A' }}</p>

        <div class="body">
            <p>Tafadhali rejea barua yenye kumbukumbu <strong>{{ $letter->reference_number ?: 'N/A' }}</strong> ya tarehe <strong>{{ $letter->reference_date?->format('d/m/Y') ?: 'N/A' }}</strong> yenye mada ya hapo juu.</p>
            <p>Bodi ya Madaktari katika kikao chake cha tarehe <strong>{{ $boardDate }}</strong> ilikaa na kumjadili mgonjwa <strong>{{ $patient?->name ?? 'N/A' }}</strong>.</p>
            <p>Bodi ilijadili kwa kina ripoti hiyo na ilifikia maamuzi kuwa ndugu <strong>{{ $patient?->name ?? 'N/A' }}</strong>:</p>
            <ul class="recommendations">
                @foreach (($letter->recommendations ?: []) as $recommendation)
                    <li>{{ $recommendation }}</li>
                @endforeach
            </ul>
            <p>Pamoja na barua hii ninafunganisha kopi ya ripoti halisi ya maamuzi hayo.</p>
            <p><strong><em>Ahsante,</em></strong></p>
        </div>
    @endif

    <div class="signature-wrap"><img class="signature" src="{{ $signatureData }}" alt="Signature"></div>
    <div class="signatory uppercase">
        @if ($language === 'en')
            <p>DR. MARYAM SEIF HEMED,</p>
            <p>DIRECTOR GENERAL,</p>
            <p>MINISTRY OF HEALTH,</p>
            <p>ZANZIBAR.</p>
        @else
            <p>DKT. MARYAM SEIF HEMED,</p>
            <p>MKURUGENZI MKUU,</p>
            <p>WIZARA YA AFYA,</p>
            <p><u>ZANZIBAR</u></p>
        @endif
    </div>

    <div class="footer">
        <hr class="rule">
        @if ($language === 'en')
            <p><em><strong>For direct communication:</strong></em></p>
            <p>Permanent Secretary, Director General</p>
            <p>Email: {{ $dgEmail }}</p>
        @else
            <p><em><strong>Kwa mawasiliano ya moja kwa moja:</strong></em></p>
            <p>Katibu Mkuu {{ $permanentSecretary }}, Mkurugenzi Mkuu barua pepe</p>
            <p>{{ $dgEmail }}</p>
        @endif
    </div>

    @if ($language !== 'en')
        <div class="copy"><strong>Nakla: NDG. {{ $patient?->name ?? 'N/A' }}</strong></div>
    @endif
</main>
</body>
</html>
