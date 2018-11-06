<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait ToDo {

    public function tasks($limit = 25, $skip = 0, $messageQueryParams = [])
    {
		$skip = request('next', $skip);

		if ($messageQueryParams != []) {
			$messageQueryParams = [
			    "\filter" => "status eq 'notStarted'",
			    "\$skip" => $skip,
			    "\$top" => $limit,
			    "\$count" => "true",
			];
		}

		$tasks = self::get('me/messages?'.http_build_query($messageQueryParams));

		$data = self::getPagination($tasks, $skip);

        return [
            'tasks' => $tasks,
            'total' => $data['total'],
            'previous' => $data['previous'],
            'next' => $data['next'],
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
