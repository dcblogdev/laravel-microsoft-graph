<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait CalendarEvents
{
    /**
     * Get all calendar events
     * @param  integer $top
     * @param  integer $skip
     * @param  array $params
     * @return array
     */
    public function getCalendarEvents($top = 25, $skip = 0, $params = [])
    {
        if ($params == []) {

            $top = request('top', $top);
            $skip = request('skip', $skip);

            $params = http_build_query([
                "\$orderby" => "subject",
                "\$top" => $top,
                "\$skip" => $skip,
                "\$count" => "true"
            ]);
        } else {
           $params = http_build_query($params);
        }

        $events = self::get('me/calendar/events?'.$params);

        $data = self::getPagination($events, $top, $skip);

        return [
            'events' => $events,
            'total' => $data['total'],
            'top' => $data['top'],
            'skip' => $data['skip']
        ];
    }

    /**
     * Get a calendar specific event
     * @param  string $calendarId
     * @param  string $eventId
     * @return json
     */
    public function getCalendarEvent($calendarId, $eventId)
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
    public function updateCalendarEvent($calendarId, $eventId, $data)
    {
        return self::patch("me/calendars/$calendarId/events/$eventId");
    }

    /**
     * Deletes calendar event
     * @param  string $calendarId
     * @param  string $eventId
     * @return void
     */
    public function deleteCalendarEvent($calendarId, $eventId)
    {
        return self::delete("me/calendars/$calendarId/events/$eventId");
    }

}
