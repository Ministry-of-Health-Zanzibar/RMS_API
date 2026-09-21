<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use App\Services\Reports\TopDiagnosesExport;
use App\Services\Reports\TopDiagnosesReport;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;
use ZipArchive;

class TopDiagnosesTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function authorizeReport(bool $allowed = true): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('can')->with('View Report')->andReturn($allowed);
        $this->actingAs($user, 'sanctum');
        Carbon::setTestNow(Carbon::parse('2026-09-07 12:00:00'));
    }

    private function fixture(): array
    {
        return [[
            'diagnosis_name' => 'Condition & other', 'patient_count' => 1, 'history_count' => 2,
            'patients' => [[
                'patient_id' => 7, 'name' => '=Formula, "Name"', 'gender' => null, 'history_count' => 2,
                'referred_hospitals' => [['hospital_id' => 1, 'hospital_name' => 'Hospital A']],
            ]],
        ]];
    }

    public function test_authentication_and_report_permission_are_required(): void
    {
        $this->getJson('/api/reports/top-diagnoses')->assertUnauthorized();
        $this->authorizeReport(false);
        $this->getJson('/api/reports/top-diagnoses')->assertForbidden();
    }

    public function test_invalid_ranges_and_formats_are_rejected(): void
    {
        $this->authorizeReport();
        foreach ([
            ['start_date' => '2026-09-01', 'end_date' => '2026-03-01'],
            ['start_date' => '2026-03-01', 'end_date' => '2026-09-08'],
            ['start_date' => 'bad', 'end_date' => '2026-09-07'],
            ['start_date' => '2026-03-01', 'end_date' => '2026-09-07', 'format' => 'exe'],
        ] as $params) {
            $this->getJson('/api/reports/top-diagnoses?'.http_build_query($params))->assertUnprocessable();
        }
    }

    public function test_today_is_capped_at_now_and_past_end_dates_include_the_entire_day(): void
    {
        $this->authorizeReport();
        $service = Mockery::mock(TopDiagnosesReport::class);
        $service->shouldReceive('generate')->once()->with('2026-03-01 00:00:00', '2026-09-07 12:00:00')->andReturn($this->fixture());
        $service->shouldReceive('generate')->once()->with('2026-03-01 00:00:00', '2026-04-01 00:00:00')->andReturn([]);
        $this->app->instance(TopDiagnosesReport::class, $service);
        $this->getJson('/api/reports/top-diagnoses?start_date=2026-03-01&end_date=2026-09-07')
            ->assertOk()->assertJsonPath('data.0.patient_count', 1);
        $this->getJson('/api/reports/top-diagnoses?start_date=2026-03-01&end_date=2026-03-31')
            ->assertOk()->assertJsonPath('data', []);
    }

    public function test_csv_pdf_and_word_downloads_contain_patient_details(): void
    {
        $this->authorizeReport();
        $service = Mockery::mock(TopDiagnosesReport::class);
        $service->shouldReceive('generate')->andReturn($this->fixture());
        $this->app->instance(TopDiagnosesReport::class, $service);
        $url = '/api/reports/top-diagnoses?start_date=2026-03-01&end_date=2026-09-07&format=';
        $csv = $this->get($url.'csv')->assertOk()->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $lines = explode("\n", trim(substr($csv, 3)));
        $row = str_getcsv($lines[1], ',', '"', '');
        $this->assertSame("'=Formula, \"Name\"", $row[5]);
        $this->assertSame('Hospital A', $row[8]);
        $pdf = $this->get($url.'pdf')->assertOk()->getContent();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $word = $this->get($url.'docx')->assertOk()->getContent();
        $path = tempnam(sys_get_temp_dir(), 'test-docx-');
        try {
            file_put_contents($path, $word);
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($path));
            $document = $zip->getFromName('word/document.xml');
            $this->assertNotFalse(simplexml_load_string($document));
            $this->assertStringContainsString('Condition &amp; other', $document);
            $this->assertStringContainsString('Hospital A', $document);
            $this->assertNotFalse($zip->getFromName('[Content_Types].xml'));
            $zip->close();
        } finally {
            unlink($path);
        }
    }

    public function test_csv_formula_prefixes_are_neutralized(): void
    {
        $export = new TopDiagnosesExport;
        foreach (['=1+1', '+1', '-1', '@SUM(A1)', "\tformula", '  =1'] as $value) {
            $this->assertSame("'".$value, $export->csvCell($value));
        }
        $this->assertSame('Patient name', $export->csvCell('Patient name'));
    }
}
