<?php
namespace App\Services;

use App\Models\ProductVariant;

class VariantService
{
    public function all() { return ProductVariant::all(); }
    public function create(array $data){ return ProductVariant::create($data); }
    public function update($id,array $data){
        $v=ProductVariant::findOrFail($id);
        $v->update($data);
        return $v;
    }
    public function delete($id){ ProductVariant::findOrFail($id)->delete(); }
}
