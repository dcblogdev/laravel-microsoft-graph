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

        $total = $contacts['@odata.count'];
		$previous = null;
		$next = null;
		if (isset($contacts['@odata.nextLink'])) {
			$first = explode('$skip=', $contacts['@odata.nextLink']);
			$skip = explode('&', $first[1]);
			$previous = $skip[0]-$offset;
			$next = $skip[0];

			if ($previous < 0) {
				$previous = 0;
			}

			if ($next == $total) {
				$next = null;
			}
		}

        return [
            'contacts' => $contacts,
            'total' => $total,
            'previous' => $previous,
            'next' => $next,
        ];
    }
}
