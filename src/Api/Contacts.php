<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait Contacts {

    public function getContacts($top = 25, $skip = 0, $params = [])
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

        $data = self::getPagination($contacts, $top, $skip);

        return [
            'contacts' => $contacts,
            'total' => $data['total'],
            'top' => $data['top'],
            'skip' => $data['skip']
        ];

    }

    public function createContact($data)
    {
        return self::post("me/contacts", $data);
    }

    public function getContact($contactId)
    {
        return self::get("me/contacts/$contactId");
    }

    public function updateContact($contactId, $data)
    {
        return self::patch("me/contacts/$contactId", $data);
    }

    public function deleteContact($contactId)
    {
        return self::delete("me/contacts/$contactId");
    }
}
