<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait Contacts {

    public function contacts($limit = 25, $offset = 50, $skip = 0)
    {
		$skip = request('next', $skip);

		$messageQueryParams = array (
		    "\$orderby" => "displayName",
		    "\$skip" => $skip,
		    "\$top" => $limit,
		    "\$count" => "true",
		);

		$contacts = self::get('me/contacts?'.http_build_query($messageQueryParams));

		$data = self::getPagination($contacts, $offset);

        return [
            'contacts' => $contacts,
            'total' => $data['total'],
            'previous' => $data['previous'],
            'next' => $data['next'],
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
