<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Collection;

/**
 * BaseRepository
 */
abstract class BaseRepository implements BaseRepositoryInterface {

    protected App $app;
    protected Model $model;

    public function __construct(App $app) {
        $this->app = $app;
        $this->makeModel();
    }

    /** Retorna la clase del modelo a usar */
    abstract public function model(): string;

    /** Instancia el modelo configurado en el repositorio */
    public function makeModel(): Model {
        $model = $this->app->make($this->model());
        if (!$model instanceof Model) {
            throw new \Exception("Clase {$this->model()} no es un modelo de Eloquent");
        }
        return $this->model = $model;
    }

    public function all(): Collection {
        return $this->model->all();
    }

    public function find(int|string $id): ?Model {
        return $this->model->find($id);
    }

    public function create(array $data): Model {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): ?Model {
        $record = $this->find($id);
        $record->update($data);
        return $record->fresh();
    }

    public function delete(int|string $id): bool {
        $record = $this->find($id);
        return $record ? (bool)$record->delete() : false;
    }
}