<?php

namespace Dcblogdev\MsGraph\AdminResources;

use Dcblogdev\MsGraph\Facades\MsGraphAdmin;
use Exception;

class Calendars extends MsGraphAdmin
{
    private string $userId = '';

    private string $top = '100';

    private string $skip = '0';

    public function userid(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function top(string $top): static
    {
        $this->top = $top;

        return $this;
    }

    public function skip(string $skip): static
    {
        $this->skip = $skip;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function get(array $params = []): array
    {
        if ($this->userId === '') {
            throw new Exception('userId is required.');
        }

        $top = request('top', $this->top);
        $skip = request('skip', $this->skip);

        if ($params == []) {
            $params = http_build_query([
                '$orderby' => 'name',
                '$top' => $top,
                '$skip' => $skip,
                '$count' => 'true',
            ]);
        } else {
            $params = http_build_query($params);
        }

        $calendars = MsGraphAdmin::get("users/$this->userId/calendars?$params");

        if (isset($calendars->error)) {
            throw new Exception("Graph API Error, code: {$calendars->error->code}, Message: {$calendars->error->message}");
        }

        $data = MsGraphAdmin::getPagination($calendars, $top, $skip);

        return [
            'calendars' => $calendars,
            'total' => $data['total'],
            'top' => $data['top'],
            'skip' => $data['skip'],
        ];
    }

    public function find(string $id): MsGraphAdmin
    {
        return MsGraphAdmin::get("users/$this->userId/calendar/$id");
    }

    /**
     * @throws Exception
     */
    public function store(array $data): MsGraphAdmin
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::post("users/$this->userId/calendars", $data);
    }

    /**
     * @throws Exception
     */
    public function update(string $id, array $data): MsGraphAdmin
    {
        if ($this->userId === '') {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::patch("users/$this->userId/calendars/$id", $data);
    }

    /**
     * @throws Exception
     */
    public function delete(string $id): MsGraphAdmin
    {
        if ($this->userId === '') {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::delete("users/$this->userId/calendars/$id");
    }
}
