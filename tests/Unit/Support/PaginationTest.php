<?php

namespace Tests\Unit\Support;

use App\Support\Pagination;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    public function test_per_page_is_clamped_to_a_safe_range(): void
    {
        $this->assertSame(1, Pagination::perPage(Request::create('/', 'GET', ['per_page' => 0])));
        $this->assertSame(100, Pagination::perPage(Request::create('/', 'GET', ['per_page' => 1000])));
        $this->assertSame(25, Pagination::perPage(Request::create('/')));
    }

    public function test_meta_exposes_the_standard_list_metadata(): void
    {
        $paginator = new LengthAwarePaginator(
            ['one', 'two'],
            5,
            2,
            2,
            ['path' => '/items'],
        );

        $this->assertSame([
            'current_page' => 2,
            'per_page' => 2,
            'from' => 3,
            'to' => 4,
            'total' => 5,
            'last_page' => 3,
            'has_more_pages' => true,
        ], Pagination::meta($paginator));
    }
}
