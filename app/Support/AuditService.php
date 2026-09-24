<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

final class AuditService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'access_token',
        'refresh_token',
        'authorization',
        'secret',
        'api_key',
    ];

    private function __construct()
    {
    }

    public static function record(
        string $action,
        string $module,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $description = null,
        array $context = [],
    ): void {
        $properties = [
            'action' => $action,
            'module' => $module,
            'old_values' => self::scrub($oldValues),
            'new_values' => self::scrub($newValues),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];

        foreach ($context as $key => $value) {
            if (! in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                $properties[$key] = self::scrub($value);
            }
        }

        $logger = activity('audit')->withProperties($properties);
        if ($subject !== null) {
            $logger->performedOn($subject);
        }

        if (auth()->check()) {
            $logger->causedBy(auth()->user());
        }

        $logger->log($description ?? ucfirst(str_replace('_', ' ', $action)));
    }

    public static function scrub(mixed $value): mixed
    {
        if ($value instanceof Model) {
            $value = $value->getAttributes();
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                    continue;
                }
                $result[$key] = self::scrub($item);
            }

            return $result;
        }

        if (is_object($value)) {
            return self::scrub((array) $value);
        }

        return $value;
    }
}
