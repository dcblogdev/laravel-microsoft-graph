<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait Drive {

    public function drive($top = 25, $skip = 0, $params = [])
    {
        if ($params == []) {

            $top = request('top', $top);
            $skip = request('skip', $skip);

            $params = http_build_query([
                "\$top" => $top,
                "\$skip" => $skip,
                "\$count" => "true",
            ]);
        } else {
           $params = http_build_query($params);
        }


        $files = self::get('me/drive/root/children'.$params);

        $total = isset($files['@odata.count']) ? $files['@odata.count'] : 0;

        if (isset($files['@odata.nextLink'])) {

            $parts = parse_url($files['@odata.nextLink']);
            parse_str($parts['query'], $query);

            $top = isset($query['$top']) ? $query['$top'] : 0;
            $skip = isset($query['$skip']) ? $query['$skip'] : 0;
        }

        return [
            'files' => $files,
            'total' => $total,
            'top' => $top,
            'skip' => $skip
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
