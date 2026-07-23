<?php

namespace App\Services\Profile;

use App\Models\Profile\Official;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OfficialDirectoryService
{
    /**
     * Get paginated official list with search and filters.
     */
    public function getOfficials(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Official::query()->where('status', 'active');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('position_title', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        return $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
