<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait Contacts {

    public function contacts($top = 25, $skip = 0, $params = [])
    {
        if ($params == []) {

            $top = request('top', $top);
            $skip = request('skip', $skip);

            $params = http_build_query([
                "\$orderby" => "displayName",
                "\$top" => $top,
                "\$skip" => $skip,
                "\$count" => "true",
            ]);
        } else {
           $params = http_build_query($params);
        }   

        $contacts = self::get('me/contacts?'.$params);

        $total = isset($contacts['@odata.count']) ? $contacts['@odata.count'] : 0;

        if (isset($contacts['@odata.nextLink'])) {

            $parts = parse_url($contacts['@odata.nextLink']);
            parse_str($parts['query'], $query);

            $top = isset($query['$top']) ? $query['$top'] : 0;
            $skip = isset($query['$skip']) ? $query['$skip'] : 0;
        }

        return [
            'contacts' => $contacts,
            'total' => $total,
            'top' => $top,
            'skip' => $skip
        ];

    }

    public function contactCreate($data)
    {
        return self::post("me/contacts", $data);
    }

    public function contactGet($id)
    {
        return self::get("me/contacts/$id");
    }

    public function contactUpdate($id, $data)
    {
        return self::patch("me/contacts/$id", $data);
    }

    public function contactDelete($id)
    {
        return self::delete("me/contacts/$id");
    }
}
