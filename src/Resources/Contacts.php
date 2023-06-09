<?php

namespace Dcblogdev\MsGraph\Resources;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Dcblogdev\MsGraph\Helpers\Paginator;

class Contacts extends MsGraph
{
    public function get($params = [], $perPage = 25)
    {
        $perPage  = $params['$top'] ?? $perPage;
        $params   = $this->getParams($params, $perPage);
        $contacts = MsGraph::get('me/contacts?'.$params);
        $total    = $contacts['@odata.count'] ?? $perPage;
        $pages    = new Paginator($perPage, 'p');
        $pages->setTotal($total);

        return [
            'contacts'    => $contacts,
            'total'       => $total,
            'links'       => $pages->page_links(),
            'links_array' => $pages->page_links_array(),
        ];
    }

    public function find($id)
    {
        return MsGraph::get("me/contacts/$id");
    }

    public function store(array $data)
    {
        return MsGraph::post('me/contacts', $data);
    }

    public function update($id, array $data)
    {
        return MsGraph::patch("me/contacts/$id", $data);
    }

    public function delete($id)
    {
        return MsGraph::delete("me/contacts/$id");
    }

    protected function getParams($params, $perPage)
    {
        $skip = $params['skip'] ?? 0;
        $page = request('p', $skip);
        if ($page > 0) {
            $page--;
        }

        if ($params == []) {
            $params = http_build_query([
                '$orderby' => 'displayName',
                '$top'     => $perPage,
                '$skip'    => $page,
                '$count'   => "true"
            ]);
        } else {
            //ensure $top, $skip and $count are part of params
            if (!in_array('$top', $params)) {
                $params['$top'] = $perPage;
            }

            if (!in_array('$skip', $params)) {
                $params['$skip'] = $page;
            }

            if (!in_array('$count', $params)) {
                $params['$count'] = "true";
            }

            $params = http_build_query($params);
        }

        return $params;
    }
}
