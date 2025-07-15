<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function all(int $perPage = 15)
    {
        return Product::with('variants')
            ->orderBy('name')
            ->paginate($perPage);
    }
    
    public function create(array $data)
    {
        return Product::create($data);
    }

    public function find(int $id): Product
    {
        return Product::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $p = Product::findOrFail($id);
        $p->update($data);
        return $p;
    }

    public function delete($id)
    {
        Product::findOrFail($id)->delete();
    }
}
