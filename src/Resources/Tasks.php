<?php

namespace Daveismyname\MsGraph\Resources;

use Daveismyname\MsGraph\Facades\MsGraph;
use Exception;

class Tasks extends MsGraph
{
    private $top;
    private $skip;

    public function top(string $top)
    {
        $this->top = $top;
        return $this;
    }

    public function skip(string $skip)
    {
        $this->skip = $skip;
        return $this;
    }

	public function get($params = [])
	{
        if ($params == []) {

            $top = request('top', $this->top);
            $skip = request('skip', $this->skip);

            $params = http_build_query([
                "\$filter" => "status eq 'notStarted'",
                "\$top" => $top,
                "\$skip" => $skip,
                "\$count" => "true",
            ]);
        } else {
           $params = http_build_query($params);
        }   

        $tasks = MsGraph::get('me/messages?'.$params);

        $data = MsGraph::getPagination($tasks, $top, $skip);

        return [
            'tasks' => $tasks,
            'total' => $data['total'],
            'top' => $data['top'],
            'skip' => $data['skip']
        ];
	}

    public function folders()
    {
        return MsGraph::get("me/outlook/taskFolders");
    }

    public function find($id)
    {
        return MsGraph::get("me/outlook/tasks/$id");
    }

    public function store(array $data)
    {
        return MsGraph::post("me/outlook/tasks", $data);
    }

    public function update(string $id, array $data)
    {
        return MsGraph::patch("me/outlook/tasks/$id", $data);
    }

    public function delete($id)
    {
        return MsGraph::delete("me/outlook/tasks/$id");
    }
}