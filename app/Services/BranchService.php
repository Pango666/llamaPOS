<?php
namespace App\Services;

use App\Models\Branch;

class BranchService
{
    public function all() { return Branch::all(); }
    public function find($id) { return Branch::findOrFail($id); }
    public function create(array $data) { return Branch::create($data); }
    public function update($id, array $data) {
        $b = $this->find($id);
        $b->update($data);
        return $b;
    }
    public function delete($id) { $this->find($id)->delete(); }
}
