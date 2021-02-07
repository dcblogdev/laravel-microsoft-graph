<?php

namespace Dcblogdev\MsGraph\Resources;

use Dcblogdev\MsGraph\Facades\MsGraph;
use GuzzleHttp\Client;

class Files extends MsGraph
{
    public function getFiles($path = null)
    {
        $path = $path === null ? 'me/drive/root/children?$orderby=name%20asc' : 'me/drive/root:'.$this->forceStartingSlash($path).':/children';
        return MsGraph::get($path);
    }

    public function getDrive()
    {
        return MsGraph::get('me/drive');
    }

    public function getDrives()
    {
        return MsGraph::get('me/drives');
    }

    public function search($term)
    {
        return MsGraph::get("me/drive/root/search(q='$term')");
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

    public function createFolder($name, $path)
    {
        $path = $path === null ? 'me/drive/root/children' : 'me/drive/root:'.$this->forceStartingSlash($path).':/children';
        return MsGraph::post($path, [
            'name' => $name,
            'folder' => new \stdClass(),
            "@microsoft.graph.conflictBehavior" => "rename"
        ]);
    }

    public function getItem($id)
    {
        return MsGraph::get("me/drive/items/$id");
    }

    public function rename($name, $id)
    {
        $path = "me/drive/items/$id";
        return MsGraph::patch($path, [
            'name' => $name
        ]);
    }

    public function upload($name, $uploadPath, $path = null)
    {
        $uploadSession = $this->createUploadSession($name, $path);
        $uploadUrl = $uploadSession['uploadUrl'];

        $fragSize = 320 * 1024;
        $file = file_get_contents($uploadPath);
        $fileSize = strlen($file);
        $numFragments = ceil($fileSize / $fragSize);
        $bytesRemaining = $fileSize;
        $i = 0;
        $ch = curl_init($uploadUrl);
        while ($i < $numFragments) {
            $chunkSize = $numBytes = $fragSize;
            $start = $i * $fragSize;
            $end = $i * $fragSize + $chunkSize - 1;
            $offset = $i * $fragSize;
            if ($bytesRemaining < $chunkSize) {
                $chunkSize = $numBytes = $bytesRemaining;
                $end = $fileSize - 1;
            }
            if ($stream = fopen($uploadPath, 'r')) {
                // get contents using offset
                $data = stream_get_contents($stream, $chunkSize, $offset);
                fclose($stream);
            }

            $content_range = " bytes " . $start . "-" . $end . "/" . $fileSize;
            $headers = [
                'Content-Length' => $numBytes,
                'Content-Range' => $content_range
            ];

            $client = new Client;
            $response = $client->put($uploadUrl, [
                'headers' => $headers,
                'body' => $data,
            ]);

            $bytesRemaining = $bytesRemaining - $chunkSize;
            $i++;
        }

    }

    protected function createUploadSession($name, $path = null)
    {
        $path = $path === null ? "me/drive/root:/$name:/createUploadSession" : "me/drive/root:".$this->forceStartingSlash($path)."/$name:/createUploadSession";

        return MsGraph::post($path, [
            'item' => [
                "@microsoft.graph.conflictBehavior" => "rename",
                "name" => $name
            ]
        ]);
    }

    protected function forceStartingSlash($string)
    {
        if (substr($string, 0, 1) !== "/") {
            $string = "/$string";
        }

        return $string;
    }
}