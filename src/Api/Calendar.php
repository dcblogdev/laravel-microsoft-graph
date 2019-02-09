<?php

namespace Daveismyname\MsGraph\Api;

trait Calendar
{
    /**
     * Get all calendars
     * @param  integer $top
     * @param  integer $skip
     * @param  array $params
     * @return array
     */
    public function getCalendars($top = 25, $skip = 0, $params = [])
    {
        if ($params == []) {

            $top = request('top', $top);
            $skip = request('skip', $skip);

            $params = http_build_query([
                "\$orderby" => "name",
                "\$top" => $top,
                "\$skip" => $skip,
                "\$count" => "true"
            ]);
        } else {
           $params = http_build_query($params);
        }

        $calendars = self::get('me/calendars?'.$params);

        $data = self::getPagination($calendars, $top, $skip);

        return [
            'calendars' => $calendars,
            'total' => $data['total'],
            'top' => $data['top'],
            'skip' => $data['skip']
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
