<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait Events
{
    /**
     * Get Events
     * @param  integer $limit
     * @param  integer $offset
     * @param  integer $skip
     * @return array
     */
    public function events ($limit = 25, $offset = 50, $skip = 0)
    {
        $skip = request('next', $skip);

        $messageQueryParams = array (
            "\$orderby" => "subject",
            "\$skip" => $skip,
            "\$top" => $limit,
            "\$count" => "true",
        );

        $events = self::get('me/events?'.http_build_query($messageQueryParams));

        $data = self::getPagination($events, $offset);

        return [
            'events' => $events,
            'total' => $data['total'],
            'previous' => $data['previous'],
            'next' => $data['next'],
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
