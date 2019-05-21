<?php

namespace Daveismyname\MsGraph\AdminResources;

use Daveismyname\MsGraph\Facades\MsGraphAdmin;

class Contacts extends MsGraphAdmin
{
    private $userId;
    private $top;
    private $skip;

    public function userid($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function top($top)
    {
        $this->top = $top;
        return $this;
    }

    public function skip($skip)
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

        $contacts = MsGraphAdmin::get('users/'.$this->userId.'/contacts?'.$params);

        $data = MsGraphAdmin::getPagination($contacts, $top, $skip);

        return [
            'contacts' => $contacts,
            'total' => $data['total'],
            'top' => $data['top'],
            'skip' => $data['skip']
        ];
	}

    public function find($id)
    {
        return MsGraphAdmin::get('users/'.$this->userId.'/contacts/'.$id);
    }

    public function store(array $data)
    {
        return MsGraphAdmin::post('users/'.$this->userId.'/contacts', $data);
    }

    public function update($id, array $data)
    {
        return MsGraphAdmin::patch('users/'.$this->userId.'/contacts/'.$id, $data);
    }

    public function delete($id)
    {
        return MsGraphAdmin::delete('users/'.$this->userId.'/contacts/'.$id);
    }
}