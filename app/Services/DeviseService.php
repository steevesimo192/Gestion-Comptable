<?php

namespace App\Services;

use App\Models\Devise;
use App\Repositories\Contracts\DeviseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeviseService
{
    public function __construct(
        private DeviseRepositoryInterface $repository
    ) {
    }

    public function paginate(
        int $perPage = 25,
        ?string $search = null,
        ?bool $actif = null
    ): LengthAwarePaginator {
        return $this->repository->paginate(
            $perPage,
            $search,
            $actif
        );
    }

    public function find(string $id): Devise
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Devise
    {
        $data['code'] = strtoupper($data['code']);

        return $this->repository->create($data);
    }

    public function update(
        Devise $devise,
        array $data
    ): Devise {
        if (isset($data['code'])) {
            $data['code'] = strtoupper($data['code']);
        }

        return $this->repository->update(
            $devise,
            $data
        );
    }

    public function delete(Devise $devise): void
    {
        $this->repository->delete($devise);
    }
}
