<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\Log;

class BranchService
{
    public function all()
    {
        return Branch::all();
    }

    public function find(int $id): Branch
    {
        return Branch::findOrFail($id);
    }

    public function create(array $data): Branch
    {
        try {
            $branch = Branch::create($data);
            Log::info('BranchService@create success', ['id' => $branch->id]);
            return $branch;
        } catch (\Exception $e) {
            Log::error('BranchService@create error', [
                'data' => $data,
                'msg'  => $e->getMessage(),
                'trace'=> $e->getTraceAsString(),
            ]);
            throw new \Exception('Error al crear sucursal.');
        }
    }

    public function update(int $id, array $data): Branch
    {
        try {
            $branch = $this->find($id);
            $branch->update($data);
            Log::info('BranchService@update success', ['id' => $branch->id]);
            return $branch;
        } catch (\Exception $e) {
            Log::error('BranchService@update error', [
                'id'   => $id,
                'data' => $data,
                'msg'  => $e->getMessage(),
                'trace'=> $e->getTraceAsString(),
            ]);
            throw new \Exception('Error al actualizar sucursal.');
        }
    }

    public function delete(int $id): bool
    {
        try {
            $branch = $this->find($id);
            $result = $branch->delete();
            Log::info('BranchService@delete success', ['id' => $id]);
            return $result;
        } catch (\Exception $e) {
            Log::error('BranchService@delete error', [
                'id'   => $id,
                'msg'  => $e->getMessage(),
                'trace'=> $e->getTraceAsString(),
            ]);
            throw new \Exception('Error al eliminar sucursal.');
        }
    }
}
