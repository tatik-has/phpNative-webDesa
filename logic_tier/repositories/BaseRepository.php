<?php

require_once __DIR__ . '/../../data_tier/enums/StatusPermohonan.php';
abstract class BaseRepository
{
    /** Instance model yang digunakan oleh repository ini */
    protected object $model;

    public function getAllWithUser(): array
    {
        return $this->model->getAllWithUser();
    }

    public function findWithUser(int $id): ?array
    {
        return $this->model->findWithUser($id);
    }

    public function findOrFail(int $id): array
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): int
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    public function archive(int $id): bool
    {
        return $this->model->archive($id);
    }

    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }
}