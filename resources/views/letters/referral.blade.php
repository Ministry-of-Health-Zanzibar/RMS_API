<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <title>Referral letter {{ $reference }}</title>
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
        .recipient { margin-bottom: 15px; }
        .uppercase { text-transform: uppercase; font-weight: 700; }
        .body { line-height: 1.65; }
        .body p { margin-bottom: 10px; }
        .patient-list { margin: 0 0 10px 22px; padding: 0; font-weight: 700; }
        .signature-wrap { height: 70px; display: flex; align-items: center; margin-top: 8px; }
        .signature { display: block; width: 220px; height: 70px; object-fit: contain; object-position: left center; }
        .signatory { margin-top: 3px; }
        .footer { margin-top: 12px; text-align: center; color: #444; font-size: 8.5pt; }
        .footer .rule { margin: 8px 0 6px; }
        .link { color: #111; text-decoration: none; }
        .subject { margin-bottom: 10px; text-align: center; font-weight: 700; text-transform: uppercase; }
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
                    <p>Website: <span class="link">www.mohz.go.tz</span></p>
                    <p>Email: <span class="link">{{ $email }}</span></p>
                </div>
            </div>
        </div>

        <hr class="rule">
        <div class="meta">
            <div><strong>Ref:</strong> {{ $reference }}</div>
            <div>Date: {{ $startDate }}</div>
        </div>

        <div class="recipient">
            <p class="uppercase">GENERAL MANAGER,</p>
            <p class="uppercase">{{ $referral?->hospital?->hospital_name ?? 'N/A' }}</p>
            <p class="uppercase">{{ $referral?->hospital?->hospital_address ?? 'N/A' }}</p>
            @if ($referral?->hospital?->contact_number)<p>TEL: {{ $referral->hospital->contact_number }}</p>@endif
            @if ($referral?->hospital?->hospital_email)<p>Email: {{ $referral->hospital->hospital_email }}</p>@endif
        </div>

        <div class="body">
            <p>The Ministry of Health, Zanzibar is pleased to transfer the following patient for further investigation and treatment in your hospital.</p>
            <p>He will arrive at <strong>{{ $flight?->arrival_airport ?? 'N/A' }}</strong> on <strong>{{ $flight?->arrival_date ? \Carbon\Carbon::parse($flight->arrival_date)->format('d F, Y') : 'N/A' }}</strong> at <strong>{{ $flight?->arrival_time ?? 'N/A' }}</strong> by <strong>{{ $flight?->airline ?? 'N/A' }}</strong> flight no. <strong>{{ $flight?->flight_number ?? 'N/A' }}</strong>.</p>
            <ol class="patient-list"><li>{{ $referral?->patient?->name ?? 'N/A' }}</li></ol>
            <p>The medical referral report is attachment for your reference.</p>
            <p>As agreed, his bill should be sent to us for payment.</p>
            <p style="margin-top: 16px;">Yours faithfully</p>
        </div>

        <div class="signature-wrap"><img class="signature" src="{{ $signatureData }}" alt="Signature"></div>
        <div class="signatory">
            <p class="uppercase">DKT. MARYAM SEIF HEMED,</p>
            <p class="uppercase">DIRECTOR GENERAL,</p>
            <p class="uppercase">MINISTRY OF HEALTH,</p>
            <p class="uppercase">ZANZIBAR.</p>
        </div>

        <div class="footer">
            <hr class="rule">
            <p><em><strong>For direct communication:</strong></em></p>
            <p>Permanent Secretary, Director General</p>
            <p>Email: {{ $dgEmail }}</p>
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
                    <p>Tovuti: <span class="link">www.mohz.go.tz</span></p>
                    <p>Barua pepe: <span class="link">{{ $email }}</span></p>
                </div>
            </div>
        </div>

        <hr class="rule">
        <div class="meta">
            <div>KUMB: CDA.25/254/01</div>
            <div>TAREHE: {{ $startDate }}</div>
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
            <p>Kwa barua hii, tunaomba mumpokee na kumpatia matibabu stahiki mgonjwa <strong>{{ $referral?->patient?->name ?? 'N/A' }}</strong> kama tulivyokubaliana.</p>
            <p>Gharama za matibabu zitalipwa na Wizara ya Afya kwa mujibu wa Mkataba na <strong>{{ $referral?->hospital?->hospital_name ?? 'N/A' }}</strong>.</p>
            <p>Wizara ya Afya inaomba taarifa fupi za maendeleo ya matibabu kwa mhusika ikiwemo Diagnosis, Tiba aliyopewa, tathmini, na sababu ya kurudi mara nyingine.</p>
            <p>Aidha Wizara ya Afya inaomba ripoti kamili iwasilishwe mwishoni mwa matibabu.</p>
            <p>Kumbukumbu zake za matibabu zimeambatanishwa kwa urahisi wa kurejea.</p>
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
