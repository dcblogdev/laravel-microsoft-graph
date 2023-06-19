<?php

namespace Dcblogdev\MsGraph\Resources\Tasks;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Dcblogdev\MsGraph\Helpers\Paginator;

class Tasks extends MsGraph
{
    public function get($taskListId, $params = [], $perPage = 25, $instance = 'p')
    {
        $params = $this->getParams($params, $perPage, $instance);

        $filteredParams = $params;
        unset($filteredParams['$top']);
        unset($filteredParams['$skip']);

        $tasks         = MsGraph::get("me/todo/lists/$taskListId/tasks?".http_build_query($params));
        $filteredTasks = MsGraph::get("me/todo/lists/$taskListId/tasks?".http_build_query($filteredParams));
        $total         = count($filteredTasks['value']);
        $pages         = new Paginator($perPage, $instance);
        $pages->setTotal($total);

        return [
            'tasks'       => $tasks,
            'total'       => $total,
            'links'       => $pages->page_links(),
            'links_array' => $pages->page_links_array(),
        ];
    }

    public function find($taskListId, $taskId)
    {
        return MsGraph::get("me/todo/lists/$taskListId/tasks/$taskId");
    }

    public function store($taskListId, array $data)
    {
        return MsGraph::post("me/todo/lists/$taskListId/tasks", $data);
    }

    public function update($taskListId, $taskId, array $data)
    {
        return MsGraph::patch("me/todo/lists/$taskListId/tasks/$taskId", $data);
    }

    public function delete($taskListId, $taskId)
    {
        return MsGraph::delete("me/todo/lists/$taskListId/tasks/$taskId");
    }

    protected function getParams($params, $perPage, $instance)
    {
        $skip = $params['skip'] ?? 0;
        $page = request($instance, $skip);
        if ($page > 0) {
            $page--;
        }

        if ($params == []) {
            $params = [
                '$orderby' => 'createdDateTime',
                '$top'     => $perPage,
                '$skip'    => $page,
            ];
        } else {
            //ensure $top, $skip and $count are part of params
            if (!in_array('$top', $params)) {
                $params['$top'] = $perPage;
            }

            if (!in_array('$skip', $params)) {
                $params['$skip'] = $page;
            }
        }

        return $params;
    }
}
