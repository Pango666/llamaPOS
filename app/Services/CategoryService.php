<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function all()
    {
        return Category::all();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function find(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $c = Category::findOrFail($id);
        $c->update($data);
        return $c;
    }

    public function delete($id)
    {
        Category::findOrFail($id)->delete();
    }
}
