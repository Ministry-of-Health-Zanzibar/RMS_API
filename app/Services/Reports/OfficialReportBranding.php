<?php

namespace App\Services\Reports;

final class OfficialReportBranding
{
    public function data(): array
    {
        $logoPath = public_path('images/letters/smz.png');

        return [
            'government' => 'THE REVOLUTIONARY GOVERNMENT OF ZANZIBAR',
            'ministry' => 'MINISTRY OF HEALTH ZANZIBAR',
            'logo_path' => is_file($logoPath) ? $logoPath : null,
            'logo_data_uri' => is_file($logoPath)
                ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
                : null,
        ];
    }
}
