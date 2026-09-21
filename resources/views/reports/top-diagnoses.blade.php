<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top 10 Diagnoses</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { font-size: 20px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #ccc; padding: 5px; overflow-wrap: break-word; }
        th { background: #eee; text-align: left; }
        thead { display: table-header-group; }
    </style>
</head>
<body>
    <h1>Top 10 Diagnoses</h1>
    <p>{{ $period }}</p>
    <p>Ranked by unique patients. Diagnosis totals repeat on each patient row; do not sum them.
       Hospitals are referrals for the same diagnosis within the selected period.</p>
    <table>
        <thead><tr>@foreach ($headers as $header)<th>{{ $header }}</th>@endforeach</tr></thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
        @empty
            <tr><td colspan="9">No diagnoses found for this period.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
