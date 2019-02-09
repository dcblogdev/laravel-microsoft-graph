<?php

namespace Daveismyname\MsGraph\Api;

trait ToDo {

    public function getTasks($top = 25, $skip = 0, $params = [])
    {
        if ($params == []) {

            $top = request('top', $top);
            $skip = request('skip', $skip);

            $params = http_build_query([
                "\$filter" => "status eq 'notStarted'",
                "\$top" => $top,
                "\$skip" => $skip,
                "\$count" => "true",
            ]);
        } else {
           $params = http_build_query($params);
        }

        $tasks = self::get('me/messages?'.$params);

        $data = self::getPagination($tasks, $top, $skip);

        return [
            'tasks' => $tasks,
            'total' => $data['total'],
            'top' => $data['top'],
            'skip' => $data['skip']
        ];
    }

    public function getTaskFolders()
    {
        return self::get('me/outlook/taskFolders');
    }

    public function createTask($data)
    {
        return self::post("me/outlook/tasks", $data);
    }

    public function getTask($taskId)
    {
        return self::get("me/outlook/tasks/$taskId");
    }

    public function updateTask($taskId, $data)
    {
        return self::patch("me/outlook/tasks/$taskId", $data);
    }

    public function deleteTask($taskId)
    {
        return self::delete("me/outlook/tasks/$taskId");
    }
}
