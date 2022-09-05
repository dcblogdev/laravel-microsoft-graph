<?php

namespace Dcblogdev\MsGraph\AdminResources;

use Dcblogdev\MsGraph\Facades\MsGraphAdmin;
use Exception;

class CalendarEvents extends MsGraphAdmin
{
    private $userId;
    private $top;
    private $skip;

    public function userid($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function top($top)
    {
        $this->top = $top;

        return $this;
    }

    public function skip($skip)
    {
        $this->skip = $skip;

        return $this;
    }

    public function get($calendarId, $params = [])
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        $top  = request('top', $this->top);
        $skip = request('skip', $this->skip);

        if ($params == []) {
            $params = http_build_query([
                '$orderby' => 'subject',
                '$top'     => $top,
                '$skip'    => $skip,
                '$count'   => 'true',
            ]);
        } else {
            $params = http_build_query($params);
        }

        $events = MsGraphAdmin::get("users/$this->userId/calendars/$calendarId/events?$params");
        $data   = MsGraphAdmin::getPagination($events, $top, $skip);

        return [
            'events' => $events,
            'total'  => $data['total'],
            'top'    => $data['top'],
            'skip'   => $data['skip'],
        ];
    }

    public function find($calendarId, $eventId)
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::get("users/$this->userId/calendars/$calendarId/events/$eventId");
    }

    public function store($calendarId, $data)
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::post("users/$this->userId/calendars/$calendarId/events", $data);
    }
}
