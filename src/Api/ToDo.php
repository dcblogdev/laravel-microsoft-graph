<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait ToDo {

    public function tasks($top = 25, $skip = 0, $params = [])
    {
        if ($params == []) {

            $top = request('top', $top);
            $skip = request('skip', $skip);

            $params = http_build_query([
                "\filter" => "status eq 'notStarted'",
                "\$top" => $top,
                "\$skip" => $skip,
                "\$count" => "true",
            ]);
        } else {
           $params = http_build_query($params);
        }

        $tasks = self::get('me/messages?'.$params);

        $total = isset($tasks['@odata.count']) ? $tasks['@odata.count'] : 0;

        if (isset($tasks['@odata.nextLink'])) {

            $parts = parse_url($tasks['@odata.nextLink']);
            parse_str($parts['query'], $query);

            $top = isset($query['$top']) ? $query['$top'] : 0;
            $skip = isset($query['$skip']) ? $query['$skip'] : 0;
        }

        return [
            'tasks' => $tasks,
            'total' => $total,
            'top' => $top,
            'skip' => $skip
        ];
    }

    public function taskCreate($data)
    {
        return self::post("me/outlook/tasks", $data);
    }

    public function taskGet($id)
    {
        return self::get("me/outlook/tasks/$id");
    }

    public function taskUpdate($id, $data)
    {
        return self::patch("me/outlook/tasks/$id", $data);
    }

    public function taskDelete($id)
    {
        return self::delete("me/outlook/tasks/$id");
    }
}
