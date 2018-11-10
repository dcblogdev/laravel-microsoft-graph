<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait Events
{
    /**
     * Get Events
     * @param  integer $top
     * @param  integer $skip
     * @param  array $params
     * @return array
     */
    public function getEvents($top = 25, $skip = 0, $params = [])
    {
        if ($params == []) {

            $top = request('top', $top);
            $skip = request('skip', $skip);

            $params = http_build_query([
                "\$orderby" => "subject",
                "\$top" => $top,
                "\$skip" => $skip,
                "\$count" => "true",
            ]);
        } else {
           $params = http_build_query($params);
        } 

        $events = self::get('me/events?'.$params);

        $data = self::getPagination($events, $top, $skip);

        return [
            'events' => $events,
            'total' => $data['total'],
            'top' => $data['top'],
            'skip' => $data['skip']
        ];
    }

    /**
     * Create event
     * @param  array $data
     * @return json
     */
    public function createEvent($data)
    {
        return self::post("me/events", $data);
    }

    /**
     * Get event
     * @param  string $eventId
     * @return json
     */
    public function getEvent($eventId)
    {
        return self::get("me/events/$eventId");
    }

    /**
     * Update the event
     * @param  string $eventId
     * @param  array $data
     * @return json
     */
    public function updateEvent($eventId, $data)
    {
        return self::patch("me/events/$eventId", $data);
    }

    /**
     * Delete event
     * @param  string $eventId
     * @return voeventId
     */
    public function deleteEvent($eventId)
    {
        return self::delete("me/events/$eventId");
    }
}
