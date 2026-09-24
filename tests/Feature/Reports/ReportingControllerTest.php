<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use Mockery;
use Tests\TestCase;

class ReportingControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_report_catalogue_requires_the_view_report_permission(): void
    {
        $this->getJson('/api/reports/types')->assertUnauthorized();

        $user = $this->userWithReportAccess(false);
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/types')
            ->assertForbidden();

        $user = $this->userWithReportAccess(true);
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/types')
            ->assertOk()
            ->assertJsonPath('data.0.key', 'top_diagnoses');
    }

    public function test_report_generation_requires_the_report_parameters(): void
    {
        $this->actingAs($this->userWithReportAccess(true), 'sanctum')
            ->postJson('/api/reports/generate', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['report_type', 'start_date', 'end_date']);
    }

    private function userWithReportAccess(bool $allowed): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasAnyRole')->andReturn(false);
        $user->shouldReceive('can')->with('View Report')->andReturn($allowed);

        return $user;
    }
}
