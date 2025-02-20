<?php

namespace Dcblogdev\MsGraph\Resources;

use Dcblogdev\MsGraph\Facades\MsGraph;
use GuzzleHttp\Client;

class Files extends MsGraph
{
    public function getFiles(string $path = '', string $type = 'me'): array
    {
        $path = blank($path)
            ? $type.'/drive/root/children?$orderby=name%20asc'
            : $type.'/drive/root:'.$this->forceStartingSlash($path).':/children';

        if (is_array($msGraph = MsGraph::get($path))) {
            return $msGraph;
        }

        return get_object_vars($msGraph);
    }

    public function getDrive(string $type = 'me'): array
    {
        return MsGraph::get($type.'/drive');
    }

    public function getDrives(string $type = 'me'): array
    {
        return MsGraph::get($type.'/drives');
    }

    public function search(string $term, string $type = 'me'): array
    {
        return MsGraph::get($type."/drive/root/search(q='$term')");
    }

    public function downloadFile(string $id, string $type = 'me'): object
    {
        $id = MsGraph::get($type."/drive/items/$id");

        return redirect()->away($id['@microsoft.graph.downloadUrl']);
    }

    public function deleteFile(string $id, string $type = 'me'): string
    {
        return MsGraph::delete($type."/drive/items/$id");
    }

    public function createFolder(string $name, string $path = '', string $type = 'me', string $behavior = 'rename'): array
    {
        $path = $path === '' ? $type.'/drive/root/children' : $type.'/drive/root:'.$this->forceStartingSlash($path).':/children';

        return MsGraph::post($path, [
            'name' => $name,
            'folder' => new \stdClass,
            '@microsoft.graph.conflictBehavior' => $behavior,
        ]);
    }

    public function getItem(string $id, string $type = 'me'): array
    {
        return MsGraph::get($type."/drive/items/$id");
    }

    public function rename(string $name, string $id, string $type = 'me'): array
    {
        $path = $type."/drive/items/$id";

        return MsGraph::patch($path, [
            'name' => $name,
        ]);
    }

    public function upload(string $name, string $uploadPath, string $path = '', string $type = 'me', string $behavior = 'rename'): void
    {
        $uploadSession = $this->createUploadSession($name, $path, $type, $behavior);
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

            $content_range = ' bytes '.$start.'-'.$end.'/'.$fileSize;
            $headers = [
                'Content-Length' => $numBytes,
                'Content-Range' => $content_range,
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

    protected function createUploadSession(string $name, string $path = '', string $type = 'me', string $behavior = 'rename'): array
    {
        $path = $path === '' ? $type."/drive/root:/$name:/createUploadSession" : $type.'/drive/root:'.$this->forceStartingSlash($path)."/$name:/createUploadSession";

        return MsGraph::post($path, [
            'item' => [
                '@microsoft.graph.conflictBehavior' => $behavior,
                'name' => $name,
            ],
        ]);
    }

    protected function forceStartingSlash(string $string): string
    {
        if (! str_starts_with($string, '/')) {
            $string = "/$string";
        }

        return $string;
    }
}
