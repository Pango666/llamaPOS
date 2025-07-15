<?php
namespace App\Services;

use App\Models\Client;

class ClientService
{
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Client::orderBy('name')->get();
    }

    public function find(int $id): Client
    {
        return Client::findOrFail($id);
    }

    public function create(array $data): Client
    {
        return Client::create($data);
    }

    public function update(int $id, array $data): Client
    {
        $client = $this->find($id);
        $client->update($data);
        return $client;
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }
}