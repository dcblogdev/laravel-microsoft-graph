<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait CalendarEvents
{
    /**
     * Get all calendar events
     * @param  integer $limit
     * @param  integer $offset
     * @param  integer $skip
     * @return array
     */
    public function calendarEvents ($limit = 25, $offset = 50, $skip = 0)
    {
        $skip = request('next', $skip);

        $messageQueryParams = array (
            "\$orderby" => "subject",
            "\$skip" => $skip,
            "\$top" => $limit,
            "\$count" => "true",
        );

        $events = self::get('me/calendar/events?'.http_build_query($messageQueryParams));

        $data = self::getPagination($events, $offset);

        return [
            'events' => $events,
            'total' => $data['total'],
            'previous' => $data['previous'],
            'next' => $data['next'],
        ];
    }

    /**
     * Get a calendar specific event
     * @param  string $calendarId
     * @param  string $eventId
     * @return json
     */
    public function getCalendarEvent ($calendarId, $eventId)
    {
        return self::get("me/calendars/$calendarId/events/$eventId");
    }

    /**
     * Creates a new calendar event
     * @param  array $data
     * @return json
     */
    public function createCalendarEvent($data)
    {
        return self::post("me/calendar/events", $data);
    }

    /**
     * Updates calendar event
     * @param  string $calendarId
     * @param  string $eventId
     * @param  array $data
     * @return json
     */
    public function updateCalendarEvent ($calendarId, $eventId, $data)
    {
        return self::patch("me/calendars/$calendarId/events/$eventId");
    }

    /**
     * Deletes calendar event
     * @param  string $calendarId
     * @param  string $eventId
     * @return void
     */
    public function deleteCalendarEvent ($calendarId, $eventId)
    {
        return self::delete("me/calendars/$calendarId/events/$eventId");
    }

}
