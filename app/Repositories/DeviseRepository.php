<?php

namespace App\Repositories;

use App\Models\Devise;
use App\Repositories\Contracts\DeviseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeviseRepository implements DeviseRepositoryInterface
{
    public function paginate(
        int $perPage = 25,
        ?string $search = null,
        ?bool $actif = null
    ): LengthAwarePaginator {
        $query = Devise::query();

        if ($search !== null && $search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('code', 'ILIKE', "%{$search}%")
                    ->orWhere('nom', 'ILIKE', "%{$search}%");
            });
        }

        if ($actif !== null) {
            $query->where('actif', $actif);
        }

        return $query
            ->orderBy('code')
            ->paginate($perPage);
    }

    public function findById(string $id): Devise
    {
        return Devise::findOrFail($id);
    }

    public function create(array $data): Devise
    {
        return Devise::create($data);
    }

    public function update(Devise $devise, array $data): Devise
    {
        $devise->update($data);

        return $devise->refresh();
    }

    public function delete(Devise $devise): void
    {
        $devise->delete();
    }
}
