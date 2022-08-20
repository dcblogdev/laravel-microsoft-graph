<?php

namespace Dcblogdev\MsGraph\AdminResources;

use Dcblogdev\MsGraph\Facades\MsGraphAdmin;
use Exception;

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
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        $top  = request('top', $this->top);
        $skip = request('skip', $this->skip);

        if ($params == []) {
            $params = http_build_query([
                '$orderby' => 'displayName',
                '$top'     => $top,
                '$skip'    => $skip,
                '$count'   => 'true',
            ]);
        } else {
            $params = http_build_query($params);
        }

        $contacts = MsGraphAdmin::get('users/'.$this->userId.'/contacts?'.$params);

        $data = MsGraphAdmin::getPagination($contacts, $top, $skip);

        return [
            'contacts' => $contacts,
            'total'    => $data['total'],
            'top'      => $data['top'],
            'skip'     => $data['skip'],
        ];
    }

    public function find($id)
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::get('users/'.$this->userId.'/contacts/'.$id);
    }

    public function store(array $data)
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::post('users/'.$this->userId.'/contacts', $data);
    }

    public function update($id, array $data)
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::patch('users/'.$this->userId.'/contacts/'.$id, $data);
    }

    public function delete($id)
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::delete('users/'.$this->userId.'/contacts/'.$id);
    }
}
