<?php

namespace Dcblogdev\MsGraph\Resources\Tasks;

use Dcblogdev\MsGraph\Facades\MsGraph;

class TaskLists extends MsGraph
{
    public function get(array $params = [])
    {
        $params = http_build_query($params);

        return MsGraph::get('me/todo/lists?'.$params);
    }

    public function find(string $listId)
    {
        return MsGraph::get("me/todo/lists/$listId");
    }

    public function store(string $name)
    {
        return MsGraph::post("me/todo/lists", [
            'displayName' => $name
        ]);
    }

    public function update(string $listId, string $name)
    {
        return MsGraph::patch("me/todo/lists/$listId", [
            'displayName' => $name
        ]);
    }

    public function delete(string $listId)
    {
        return MsGraph::delete("me/todo/lists/$listId");
    }
}