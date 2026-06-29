<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

trait Paginatable
{
    /**
     * Paginer une query Eloquent avec métadonnées enrichies.
     *
     * @param  Builder  $query
     * @param  int      $perPage  Entre 1 et 100 (forcé par sécurité)
     * @param  int      $page     Numéro de page courant
     * @return array{
     *     data: \Illuminate\Support\Collection,
     *     meta: array{
     *         current_page: int,
     *         per_page: int,
     *         total: int,
     *         last_page: int,
     *         from: int|null,
     *         to: int|null,
     *         has_more_pages: bool,
     *         has_previous_pages: bool,
     *     }
     * }
     */
    protected function paginateQuery(Builder $query, int $perPage = 15, int $page = 1): array
    {
        $perPage = max(1, min($perPage, 100));
        $page    = max(1, $page);

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->getCollection(),
            'meta' => [
                'current_page'       => $paginator->currentPage(),
                'per_page'           => $paginator->perPage(),
                'total'              => $paginator->total(),
                'last_page'          => $paginator->lastPage(),
                'from'               => $paginator->firstItem(),
                'to'                 => $paginator->lastItem(),
                'has_more_pages'     => $paginator->hasMorePages(),
                'has_previous_pages' => $paginator->currentPage() > 1,
            ],
        ];
    }
}