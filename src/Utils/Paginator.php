<?php

namespace App\Utils;

class Paginator
{
    public static function paginate(array $queryParams, int $defaultLimit = 10, array $allowedSorts = []): array
    {
        $page = max(1, (int)($queryParams['page'] ?? 1));
        $limit = max(1, min(100, (int)($queryParams['limit'] ?? $defaultLimit)));
        $sort = $queryParams['sort'] ?? 'created_at';
        $direction = strtolower($queryParams['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        $offset = ($page - 1) * $limit;

        return compact('page', 'limit', 'sort', 'direction', 'offset');
    }
}
