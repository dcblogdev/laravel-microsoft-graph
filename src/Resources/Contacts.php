<?php

namespace Daveismyname\MsGraph\Resources;

use Daveismyname\MsGraph\Facades\MsGraph;
use Exception;

class Contacts extends MsGraph
{
    private $top;
    private $skip;

    public function top(string $top)
    {
        $this->top = $top;
        return $this;
    }

    public function skip(string $skip)
    {
        $this->skip = $skip;
        return $this;
    }

	public function get($params = [])
	{
        if ($params == []) {

            $top = request('top', $this->top);
            $skip = request('skip', $this->skip);

            $params = http_build_query([
                "\$orderby" => "displayName",
                "\$top" => $top,
                "\$skip" => $skip,
                "\$count" => "true",
            ]);
        } else {
           $params = http_build_query($params);
        }   

        $contacts = MsGraph::get('me/contacts?'.$params);

        $data = MsGraph::getPagination($contacts, $top, $skip);

        return [
            'contacts' => $contacts,
            'total' => $data['total'],
            'top' => $data['top'],
            'skip' => $data['skip']
        ];
	}

    public function find($id)
    {
        return MsGraph::get("me/contacts/$id");
    }

    public function store(array $data)
    {
        return MsGraph::post("me/contacts", $data);
    }

    public function update(string $id, array $data)
    {
        return MsGraph::patch("me/contacts/$id", $data);
    }

    public function delete($id)
    {
        return MsGraph::delete("me/contacts/$id");
    }
}