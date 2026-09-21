<?php

namespace Tests\Unit\Helpers;

use App\Http\Helpers\Helper;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class HelperTest extends TestCase
{
    public function test_send_error_throws_an_unauthorized_json_response(): void
    {
        try {
            Helper::sendError('Invalid credentials');
            $this->fail('Expected an HTTP response exception.');
        } catch (HttpResponseException $exception) {
            $response = $exception->getResponse();

            $this->assertSame(401, $response->getStatusCode());
            $this->assertSame([
                'success' => false,
                'message' => 'Invalid credentials',
                'statusCode' => 401,
            ], $response->getData(true));
        }
    }

    public function test_server_error_logs_exception_without_exposing_its_message(): void
    {
        Log::spy();

        $response = Helper::serverError(new RuntimeException('database password leaked'));

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame([
            'message' => 'Internal Server Error',
            'error' => 'An unexpected error occurred.',
            'statusCode' => 500,
        ], $response->getData(true));
        $this->assertStringNotContainsString('database password', $response->getContent());
    }
}
