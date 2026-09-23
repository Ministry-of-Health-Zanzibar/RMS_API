<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <title>Follow-up letter</title>
    <style>
        @page { size: A4 portrait; margin: 8mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111; background: #fff; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10.5pt; line-height: 1.45; }
        .letter { width: 100%; padding: 0; }
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
        .recipient { margin-bottom: 12px; }
        .uppercase { text-transform: uppercase; font-weight: 700; }
        .subject { margin-bottom: 10px; text-align: center; font-weight: 700; text-transform: uppercase; }
        .body { line-height: 1.55; }
        .body p { margin-bottom: 9px; }
        .signature-wrap { height: 70px; display: flex; align-items: center; margin-top: 4px; }
        .signature { display: block; width: 220px; height: 70px; object-fit: contain; object-position: left center; }
        .signatory { margin-top: 3px; }
        .footer { margin-top: 10px; text-align: center; color: #444; font-size: 8.5pt; }
        .footer .rule { margin: 8px 0 6px; }
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
                    <p>S.L.P 236 MNANZIMMOJA</p>
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
            <div>Ref: CDA.25/254/01</div>
            <div>DATE: {{ $referenceDate }}</div>
        </div>

        <div class="recipient">
            <p class="uppercase">GENERAL MANAGER,</p>
            <p class="uppercase">{{ $referral?->hospital?->hospital_name ?? 'N/A' }}</p>
            <p class="uppercase">{{ $referral?->hospital?->hospital_address ?? 'N/A' }}</p>
        </div>

        <p class="subject">RE: CONTINUATION OF TREATMENT FOR {{ $referral?->patient?->name ?? 'N/A' }}<br>{{ $ageLabel }}</p>
        <div class="body">
            <p>Please refer to the subject above.</p>
            <p>The Ministry of Health Zanzibar presents the above-mentioned patient for continuation of further treatment.</p>
            <p>Through this letter, we request that you continue providing the necessary treatment to <strong>{{ $referral?->patient?->name ?? 'N/A' }}</strong> as agreed.</p>
            <p>The treatment costs will be paid by the Ministry of Health in accordance with the agreement with <strong>{{ $referral?->hospital?->hospital_name ?? 'N/A' }}</strong>.</p>
            <p>The Ministry of Health requests brief treatment progress information, including the diagnosis, treatment given, assessment, and reason for returning again.</p>
            <p>The Ministry also requests that a complete report be submitted at the end of treatment.</p>
            <p>Please keep us informed of progress.</p>
            <p><strong>Thank you.</strong></p>
        </div>

        <div class="signature-wrap"><img class="signature" src="{{ $signatureData }}" alt="Signature"></div>
        <div class="signatory">
            <p>DR. MARYAM SEIF HEMED,</p>
            <p>DIRECTOR GENERAL,</p>
            <p>MINISTRY OF HEALTH,</p>
            <p>ZANZIBAR.</p>
        </div>
        <div class="footer">
            <hr class="rule">
            <p><em><strong>For direct communication:</strong></em></p>
            <p>Permanent Secretary, Director General</p>
            <p>{{ $dgEmail }}</p>
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
            <div>KUMB: CDA.25/254/01</div>
            <div>TAREHE: {{ $referenceDate }}</div>
        </div>

        <div class="recipient">
            <p class="uppercase">MKURUGENZI MTENDAJI,</p>
            <p class="uppercase">{{ $referral?->hospital?->hospital_name ?? 'N/A' }}</p>
            <p class="uppercase">{{ $referral?->hospital?->hospital_address ?? 'N/A' }}</p>
        </div>

        <p class="subject">Kuh: KUMPATIA MATIBABU MGONJWA {{ $referral?->patient?->name ?? 'N/A' }}<br>{{ $ageLabel }}</p>
        <div class="body">
            <p>Tafadhali naomba uhusike na mada ya hapo juu.</p>
            <p>Wizara ya Afya Zanzibar, inamleta kwa ajili ya kuendelea na matibabu zaidi mtajwa hapo juu.</p>
            <p>Kwa barua hii, tunaomba muendelee kumpatia matibabu stahiki mgonjwa <strong>{{ $referral?->patient?->name ?? 'N/A' }}</strong> kama tulivyokubaliana.</p>
            <p>Gharama za matibabu zitalipwa na Wizara ya Afya kwa mujibu wa Mkataba na <strong>{{ $referral?->hospital?->hospital_name ?? 'N/A' }}</strong>.</p>
            <p>Wizara ya Afya inaomba taarifa fupi za maendeleo ya matibabu kwa mhusika ikiwemo Diagnosis, Tiba aliyopewa, tathmini, na sababu ya kurudi mara nyingine.</p>
            <p>Aidha Wizara ya Afya inaomba ripoti kamili iwasilishwe mwishoni mwa matibabu.</p>
            <p>Naomba kuwasiliana kwa hatua.</p>
            <p><strong>Ahsante,</strong></p>
        </div>

        <div class="signature-wrap"><img class="signature" src="{{ $signatureData }}" alt="Sahihi"></div>
        <div class="signatory">
            <p>DKT. MARYAM SEIF HEMED,</p>
            <p>MKURUGENZI MKUU,</p>
            <p>WIZARA YA AFYA,</p>
            <p><u>ZANZIBAR</u></p>
        </div>
        <div class="footer">
            <hr class="rule">
            <p><em><strong>Kwa mawasiliano ya moja kwa moja:</strong></em></p>
            <p>Katibu Mkuu {{ $permanentSecretary }}, Mkurugenzi Mkuu barua pepe</p>
            <p>{{ $dgEmail }}</p>
        </div>
    @endif
</main>
</body>
</html>
