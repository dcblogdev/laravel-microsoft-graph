<?php

namespace Dcblogdev\MsGraph\Resources\Tasks;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Dcblogdev\MsGraph\Helpers\Paginator;

class Tasks extends MsGraph
{
    public function get(string $taskListId, array $params = [], int $perPage = 25, string $instance = 'p'): array
    {
        $params = $this->getParams($params, $perPage, $instance);

        $filteredParams = $params;
        unset($filteredParams['$top']);
        unset($filteredParams['$skip']);

        $tasks = MsGraph::get("me/todo/lists/$taskListId/tasks?".http_build_query($params));
        $filteredTasks = MsGraph::get("me/todo/lists/$taskListId/tasks?".http_build_query($filteredParams));
        $total = count($filteredTasks['value']);
        $pages = new Paginator($perPage, $instance);
        $pages->setTotal($total);

        return [
            'tasks' => $tasks,
            'total' => $total,
            'links' => $pages->page_links(),
            'links_array' => $pages->page_links_array(),
        ];
    }

    public function find(string $taskListId, string $taskId): array
    {
        return MsGraph::get("me/todo/lists/$taskListId/tasks/$taskId");
    }

    public function store(string $taskListId, array $data): array
    {
        return MsGraph::post("me/todo/lists/$taskListId/tasks", $data);
    }

    public function update(string $taskListId, string $taskId, array $data): array
    {
        return MsGraph::patch("me/todo/lists/$taskListId/tasks/$taskId", $data);
    }

    public function delete(string $taskListId, string $taskId): string
    {
        return MsGraph::delete("me/todo/lists/$taskListId/tasks/$taskId");
    }

    protected function getParams(array $params, int $perPage, string $instance): array
    {
        $skip = $params['skip'] ?? 0;
        $page = request($instance, $skip);
        if ($page > 0) {
            $page--;
        }

        if ($params == []) {
            $params = [
                '$orderby' => 'createdDateTime',
                '$top' => $perPage,
                '$skip' => $page,
            ];
        } else {
            // ensure $top, $skip and $count are part of params
            if (! in_array('$top', $params)) {
                $params['$top'] = $perPage;
            }

            if (! in_array('$skip', $params)) {
                $params['$skip'] = $page;
            }
        }

        return $params;
    }
}
