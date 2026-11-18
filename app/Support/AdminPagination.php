<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

/** Shared, bounded pagination for already authorized administrative queries. */
final class AdminPagination
{
    public const SIZES = [10, 20, 50, 100];

    public static function perPage(): int
    {
        $size = filter_var(request()->query('per_page'), FILTER_VALIDATE_INT);

        return in_array($size, self::SIZES, true) ? $size : 20;
    }

    public static function page(int $total, int $size): int
    {
        $page = request()->query('page', 1);
        $page = is_scalar($page) && preg_match('/^[0-9]+$/D', (string) $page) ? (int) $page : 1;

        return max(1, min($page, (int) max(1, ceil($total / $size))));
    }

    public static function paginate(Builder $query): LengthAwarePaginator
    {
        $size = self::perPage();
        $total = (clone $query)->toBase()->getCountForPagination();

        return self::configure($query->paginate($size, ['*'], 'page', self::page($total, $size), $total));
    }

    public static function configure(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return $paginator->onEachSide(1)->appends([
            ...Arr::except(request()->query(), ['page', 'per_page']),
            'per_page' => $paginator->perPage(),
        ]);
    }

    /** In-memory example; never creates records. */
    public static function demo(): LengthAwarePaginator
    {
        $size = self::perPage();
        $page = self::page(137, $size);

        return self::configure(new LengthAwarePaginator(
            array_slice(range(1, 137), ($page - 1) * $size, $size), 137, $size, $page,
            ['path' => request()->url()],
        ));
    }
}
