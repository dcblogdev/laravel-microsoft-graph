<?php

namespace Daveismyname\MsGraph\Resources;

use Daveismyname\MsGraph\Facades\MsGraph;
use Exception;

class Files extends MsGraph
{
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