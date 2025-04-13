<?php

namespace Dcblogdev\MsGraph\Resources\Emails;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Dcblogdev\MsGraph\Validators\EmailFolderStoreValidator;
use Dcblogdev\MsGraph\Validators\EmailFolderUpdateValidator;
use Dcblogdev\MsGraph\Validators\GraphQueryValidator;

class Folders extends MsGraph
{
    public function get(array $params = [], bool $sort = false, array $priorityOrder = []): array
    {
        GraphQueryValidator::validate($params);

        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $url = 'me/mailFolders'.(! empty($queryString) ? "?$queryString" : '');

        $folders = MsGraph::get($url);

        if (! isset($folders['value'])) {
            return $folders;
        }

        $allFolders = $this->fetchSubfolders($folders['value']);

        if ($sort) {
            $allFolders = $this->sortFolders($allFolders, $priorityOrder);
        }

        return array_merge($folders, ['value' => $allFolders]);
    }

    public function find(string $id): array
    {
        return MsGraph::get('me/mailFolders/'.$id);
    }

    public function findByName(string $name): array
    {
        $response = MsGraph::get("me/mailFolders?\$filter=startswith(displayName,'$name')");

        return $response['value'][0] ?? [];
    }

    public function store(array $data): array
    {
        EmailFolderStoreValidator::validate($data);

        return MsGraph::post('me/mailFolders', $data);
    }

    public function update(array $data, string $id): array
    {
        EmailFolderUpdateValidator::validate($data);

        return MsGraph::patch('me/mailFolders/'.$id, $data);
    }

    public function copy(string $sourceId, string $destinationId): array
    {
        return MsGraph::post('me/mailFolders/'.$sourceId.'/copy', [
            'destinationId' => $destinationId,
        ]);
    }

    public function move(string $sourceId, string $destinationId): array
    {
        return MsGraph::post('me/mailFolders/'.$sourceId.'/copy', [
            'destinationId' => $destinationId,
        ]);
    }

    public function delete(string $id): void
    {
        MsGraph::delete('me/mailFolders/'.$id);
    }

    protected function fetchSubfolders(array $folders): array
    {
        foreach ($folders as &$folder) {
            $subfolders = MsGraph::get("me/mailFolders/{$folder['id']}/childFolders?\$top=500");
            $folder['subfolders'] = ! empty($subfolders['value']) ? $this->fetchSubfolders($subfolders['value']) : [];
        }

        return $folders;
    }

    protected function sortFolders(array $folders, array $priorityOrder = []): array
    {
        // Default folder priority if none provided
        $defaultPriority = [
            'Inbox' => 1,
            'Archive' => 2,
            'Drafts' => 3,
            'Sent Items' => 4,
            'Deleted Items' => 5,
            'Conversation History' => 6,
            'Junk Email' => 7,
        ];

        // Use provided priority order or fallback to default
        $priority = ! empty($priorityOrder) ? $priorityOrder : $defaultPriority;

        usort($folders, function ($a, $b) use ($priority) {
            $aPriority = $priority[$a['displayName']] ?? 100;
            $bPriority = $priority[$b['displayName']] ?? 100;

            return $aPriority === $bPriority
                ? strcmp($a['displayName'], $b['displayName'])
                : $aPriority <=> $bPriority;
        });

        // Sort subfolders recursively
        foreach ($folders as &$folder) {
            if (! empty($folder['subfolders'])) {
                $folder['subfolders'] = $this->sortFolders($folder['subfolders']);
            }
        }

        return $folders;
    }
}
