<?php

namespace Dcblogdev\MsGraph\AdminResources;

use Dcblogdev\MsGraph\Facades\MsGraphAdmin;
use Exception;

class Calendars extends MsGraphAdmin
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
                '$orderby' => 'name',
                '$top'     => $top,
                '$skip'    => $skip,
                '$count'   => 'true',
            ]);
        } else {
            $params = http_build_query($params);
        }

        $calendars = MsGraphAdmin::get("users/$this->userId/calendars?$params");

        $data = MsGraphAdmin::getPagination($calendars, $top, $skip);

        return [
            'calendars' => $calendars,
            'total'     => $data['total'],
            'top'       => $data['top'],
            'skip'      => $data['skip'],
        ];
    }

    public function find($id)
    {
        return MsGraphAdmin::get("users/$this->userId/calendar/$id");
    }

    public function store(array $data)
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::post("users/$this->userId/calendars", $data);
    }

    public function update($id, $data)
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::patch("users/$this->userId/calendars/$id", $data);
    }

    public function delete($id)
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::delete("users/$this->userId/calendars/$id");
    }
}
