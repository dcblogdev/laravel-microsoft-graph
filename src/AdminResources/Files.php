<?php

namespace Daveismyname\MsGraph\AdminResources;

use Daveismyname\MsGraph\Facades\MsGraphAdmin;

class Files extends MsGraphAdmin
{
    private $userId;

    public function userid(string $userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function getDrives()
    {
        return MsGraph::get('me/drives');
    }

    public function downloadFile($id)
    {
        $id = MsGraph::get("me/drive/items/$id");

        return redirect()->away($id['@microsoft.graph.downloadUrl']);
    }

    public function deleteFile($id)
    {
        return MsGraph::delete("me/drive/items/$id");
    }
}