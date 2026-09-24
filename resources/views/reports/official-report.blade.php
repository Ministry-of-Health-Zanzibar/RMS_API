<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] }}</title>
    <style>
        @page { margin: 42px 36px 42px; }
        body { color: #1e293b; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; text-align: center; }
        .logo { height: 58px; width: auto; margin-bottom: 3px; }
        .government { color: #1e3a8a; font-size: 13px; font-weight: bold; letter-spacing: .4px; }
        .ministry { color: #0f766e; font-size: 11px; font-weight: bold; margin-top: 3px; }
        .title { color: #0f172a; font-size: 14px; font-weight: bold; margin-top: 10px; }
        .period { color: #475569; font-size: 9px; margin-top: 4px; }
        .confidential { color: #991b1b; font-size: 8px; font-weight: bold; letter-spacing: .6px; margin-top: 5px; }
        .metadata { border: 1px solid #cbd5e1; background: #f8fafc; margin: 12px 0; padding: 8px 10px; }
        .metadata-title { color: #1e3a8a; font-weight: bold; margin-bottom: 4px; }
        .metadata-row { display: inline-block; margin-right: 18px; margin-bottom: 3px; }
        .metadata-label { color: #64748b; font-weight: bold; }
        .summary { margin: 8px 0 12px; }
        .summary-item { display: inline-block; border: 1px solid #dbe3ee; background: #f8fafc; margin-right: 6px; min-width: 90px; padding: 6px; }
        .summary-label { color: #64748b; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .summary-value { color: #0f172a; font-size: 12px; font-weight: bold; margin-top: 2px; }
        table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        th, td { border: 1px solid #cbd5e1; overflow-wrap: break-word; padding: 5px; vertical-align: top; }
        th { background: #1d4ed8; color: #ffffff; font-size: 8px; text-align: left; }
        td { font-size: 8px; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        thead { display: table-header-group; }
        .empty { border: 1px solid #cbd5e1; color: #64748b; padding: 16px; text-align: center; }
        .notes { color: #64748b; font-size: 8px; margin-top: 10px; }
        .section-title { border-bottom: 1px solid #cbd5e1; color: #1e3a8a; font-size: 11px; font-weight: bold; margin: 15px 0 6px; padding-bottom: 3px; }
        .section-text { line-height: 1.45; margin: 0 0 8px; }
        .finding { margin: 3px 0; }
        .footer { border-top: 1px solid #cbd5e1; color: #64748b; font-size: 8px; margin-top: 16px; padding-top: 5px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        @if ($branding['logo_data_uri'])<img class="logo" src="{{ $branding['logo_data_uri'] }}" alt="SMZ logo">@endif
        <div class="government">{{ $branding['government'] }}</div>
        <div class="ministry">{{ $branding['ministry'] }}</div>
        <div class="title">{{ $report['title'] }}</div>
        <div class="period">Reporting period: {{ $report['period'] }}</div>
        @if (!empty($report['confidential']))<div class="confidential">CONFIDENTIAL CLINICAL REPORT</div>@endif
    </div>

    <div class="metadata">
        <div class="metadata-title">Report information</div>
        @foreach ($report['filters'] as $label => $value)
            <span class="metadata-row"><span class="metadata-label">{{ $label }}:</span> {{ $value }}</span>
        @endforeach
        <div class="metadata-row"><span class="metadata-label">Generated:</span> {{ $report['generated_at_label'] ?? $report['generated_at'] }}</div>
        <div class="metadata-row"><span class="metadata-label">Prepared by:</span> {{ $report['generated_by'] }}</div>
    </div>

    @if (!empty($report['summary']))
        <div class="summary">
            @foreach ($report['summary'] as $label => $value)
                <div class="summary-item">
                    <div class="summary-label">{{ str_replace('_', ' ', $label) }}</div>
                    <div class="summary-value">{{ is_numeric($value) ? number_format((float) $value) : $value }}</div>
                </div>
            @endforeach
        </div>
    @endif

    @if (!empty($report['sections']))
        @foreach ($report['sections'] as $section)
            <div class="section-title">{{ $section['title'] }}</div>
            @if (($section['kind'] ?? '') === 'text')
                <p class="section-text">{{ $section['text'] ?? '' }}</p>
            @elseif (($section['kind'] ?? '') === 'metrics')
                <div class="summary">
                    @foreach ($section['metrics'] ?? [] as $metric)
                        <div class="summary-item">
                            <div class="summary-label">{{ $metric['label'] }}</div>
                            <div class="summary-value">{{ $metric['value'] ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>
            @elseif (($section['kind'] ?? '') === 'list')
                @foreach ($section['items'] ?? [] as $item)<div class="finding">• {{ $item }}</div>@endforeach
            @elseif (($section['kind'] ?? '') === 'table')
                @if (!empty($section['rows']))
                    <table>
                        <thead><tr>@foreach ($section['columns'] ?? [] as $column)<th>{{ $column['label'] }}</th>@endforeach</tr></thead>
                        <tbody>
                            @foreach ($section['rows'] as $row)
                                <tr>
                                    @foreach ($section['columns'] ?? [] as $column)
                                        <td>
                                            @if (($column['type'] ?? 'text') === 'percentage' && is_numeric($row[$column['key']] ?? null))
                                                {{ number_format((float) $row[$column['key']], 2) }}%
                                            @elseif (in_array(($column['type'] ?? 'text'), ['integer', 'number'], true) && is_numeric($row[$column['key']] ?? null))
                                                {{ number_format((float) $row[$column['key']]) }}
                                            @else
                                                {{ $row[$column['key']] ?? '' }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty">No records were found for this section.</div>
                @endif
            @endif
        @endforeach
    @elseif (($report['pagination']['total'] ?? 0) > 0)
        <table>
            <thead><tr>@foreach ($report['columns'] as $column)<th>{{ $column['label'] }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach ($report['rows'] as $row)
                    <tr>
                        @foreach ($report['columns'] as $column)
                            <td>
                                @if (($column['type'] ?? 'text') === 'percentage' && is_numeric($row[$column['key']] ?? null))
                                    {{ number_format((float) $row[$column['key']], 2) }}%
                                @elseif (in_array(($column['type'] ?? 'text'), ['integer', 'number'], true) && is_numeric($row[$column['key']] ?? null))
                                    {{ number_format((float) $row[$column['key']]) }}
                                @else
                                    {{ $row[$column['key']] ?? '' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">No records were found for the selected criteria.</div>
    @endif

    @if (!empty($report['notes']))
        <div class="notes">@foreach ($report['notes'] as $note)<div>• {{ $note }}</div>@endforeach</div>
    @endif
    <div class="footer">Ministry of Health Zanzibar | {{ $report['title'] }} | Page {PAGE_NUM} of {PAGE_COUNT}</div>
</body>
</html>
