<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class Pagination
{
    public const DEFAULT_PER_PAGE = 25;

    public const MAX_PER_PAGE = 100;

    private function __construct()
    {
    }

    public static function perPage(Request $request, int $default = self::DEFAULT_PER_PAGE): int
    {
        $default = min(max($default, 1), self::MAX_PER_PAGE);

        return min(
            max((int) $request->input('per_page', $default), 1),
            self::MAX_PER_PAGE,
        );
    }

    public static function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }
}
