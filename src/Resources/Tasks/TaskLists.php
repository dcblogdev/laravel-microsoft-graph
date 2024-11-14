<?php

namespace Dcblogdev\MsGraph\Resources\Tasks;

use Dcblogdev\MsGraph\Facades\MsGraph;

class TaskLists extends MsGraph
{
    public function get(array $params = []): array
    {
        $params = http_build_query($params);

        return MsGraph::get('me/todo/lists?'.$params);
    }

    public function find(string $listId): array
    {
        return MsGraph::get("me/todo/lists/$listId");
    }

    public function store(string $name): array
    {
        return MsGraph::post('me/todo/lists', [
            'displayName' => $name,
        ]);
    }

    public function update(string $listId, string $name): array
    {
        return MsGraph::patch("me/todo/lists/$listId", [
            'displayName' => $name,
        ]);
    }

    public function delete(string $listId): string
    {
        return MsGraph::delete("me/todo/lists/$listId");
    }
}
