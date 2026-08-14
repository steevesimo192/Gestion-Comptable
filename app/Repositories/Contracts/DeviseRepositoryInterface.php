<?php

namespace App\Repositories\Contracts;

use App\Models\Devise;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DeviseRepositoryInterface
{
    public function paginate(
        int $perPage = 25,
        ?string $search = null,
        ?bool $actif = null
    ): LengthAwarePaginator;

    public function findById(string $id): Devise;

    public function create(array $data): Devise;

    public function update(Devise $devise, array $data): Devise;

    public function delete(Devise $devise): void;
}
