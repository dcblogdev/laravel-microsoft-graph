<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait Drive {

    public function drive($limit = 25, $skip = 0, $messageQueryParams = [])
    {
		$skip = request('next', $skip);

		if ($messageQueryParams != []) {
			$messageQueryParams = [
			    "\$skip" => $skip,
			    "\$top" => $limit,
			    "\$count" => "true",
			];
		}

		$files = self::get('me/drive/root/children'.http_build_query($messageQueryParams));

		$data = self::getPagination($files, $skip);

        return [
            'files' => $files,
            'total' => $data['total'],
            'previous' => $data['previous'],
            'next' => $data['next'],
        ];
    }

    public function driveDownload($id)
    {
    	$id = self::get("me/drive/items/$id");

        return redirect()->away($id['@microsoft.graph.downloadUrl']);
    }

    public function driveDelete($id)
    {
    	return self::delete("me/drive/items/$id");
    }
}
