<?php

namespace DaveismynameLaravel\MsGraph\Api;

trait Drive {

    public function getDrives()
    {
        return self::get('me/drives');
    }

    public function downloadFile($fileId)
    {
        $fileId = self::get("me/drive/items/$fileId");

        return redirect()->away($fileId['@microsoft.graph.downloadUrl']);
    }

    public function deleteFile($fileId)
    {
        return self::delete("me/drive/items/$fileId");
    }
}
