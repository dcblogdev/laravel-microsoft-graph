<?php

namespace Dcblogdev\MsGraph\Resources;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Dcblogdev\MsGraph\Helpers\Paginator;

class Contacts extends MsGraph
{
    public function get(array $params = [], int $perPage = 25): array
    {
        $perPage = $params['$top'] ?? $perPage;
        $params = $this->getParams($params, $perPage);

        $contacts = MsGraph::get('me/contacts?'.$params);
        $total = $contacts['@odata.count'] ?? $perPage;
        $pages = new Paginator($perPage, 'p');
        $pages->setTotal($total);

        return [
            'contacts' => $contacts,
            'total' => $total,
            'links' => $pages->page_links(),
            'links_array' => $pages->page_links_array(),
        ];
    }

    public function find(string $id): array
    {
        return MsGraph::get("me/contacts/$id");
    }

    public function store(array $data): array
    {
        return MsGraph::post('me/contacts', $data);
    }

    public function update(string $id, array $data): array
    {
        return MsGraph::patch("me/contacts/$id", $data);
    }

    public function delete(string $id): string
    {
        return MsGraph::delete("me/contacts/$id");
    }

    protected function getParams(array $params, int $perPage): string
    {
        $skip = $params['skip'] ?? 0;
        $page = request('p', $skip);

        if ($params == []) {
            $params = http_build_query([
                '$orderby' => 'displayName',
                '$top' => $perPage,
                '$skip' => $page,
                '$count' => 'true',
            ]);
        } else {
            // ensure $top, $skip and $count are part of params
            if (! array_key_exists('$top', $params)) {
                $params['$top'] = $perPage;
            }

            if (! array_key_exists('$skip', $params)) {
                $params['$skip'] = $page;
            }

            if (! array_key_exists('$count', $params)) {
                $params['$count'] = 'true';
            }

            $params = http_build_query($params);
        }

        return $params;
    }
}
