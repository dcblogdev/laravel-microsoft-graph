<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait Calendar
{
    /**
     * Get all calendars
     * @param  integer $limit
     * @param  integer $offset
     * @param  integer $skip
     * @return array
     */
    public function calendars ($limit = 25, $offset = 50, $skip = 0)
    {
        $skip = request('next', $skip);

        $messageQueryParams = array (
            "\$orderby" => "name",
            "\$skip" => $skip,
            "\$top" => $limit,
            "\$count" => "true",
        );

        $calendars = self::get('me/calendars?'.http_build_query($messageQueryParams));

        $data = self::getPagination($calendars, $offset);

        return [
            'calendars' => $calendars,
            'total' => $data['total'],
            'previous' => $data['previous'],
            'next' => $data['next'],
        ];
    }

    /**
     * Create calendar
     * @param  array $data
     * @return json
     */
    public function createCalendar($data)
    {
        return self::post("me/calendars", $data);
    }

    /**
     * Get calendar
     * @param  string $calendarId
     * @return json
     */
    public function getCalendar($calendarId)
    {
        return self::get("me/calendars/$calendarId");
    }

    /**
     * Update calendar
     * @param  string $calendarId
     * @param  array $data
     * @return json
     */
    public function updateCalendar($calendarId, $data)
    {
        return self::patch("me/calendars/$calendarId", $data);
    }

    /**
     * Delete Calendar
     * @param  string $calendarId
     * @return vocalendarId
     */
    public function deleteCalendar($calendarId)
    {
        return self::delete("me/calendars/$calendarId");
    }
}
