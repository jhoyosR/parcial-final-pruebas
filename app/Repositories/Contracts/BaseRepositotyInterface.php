<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * BaseRepositoryInterface
 */
interface BaseRepositoryInterface {
    public function all(): Collection;
    public function find(int|string $id): ?Model;
    public function create(array $data): Model;
    public function update(int|string $id, array $data): ?Model;
    public function delete(int|string $id): bool;
    public function makeModel(): Model;
}