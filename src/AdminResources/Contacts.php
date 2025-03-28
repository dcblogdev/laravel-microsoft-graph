<?php

namespace Dcblogdev\MsGraph\AdminResources;

use Dcblogdev\MsGraph\Facades\MsGraphAdmin;
use Exception;

class Contacts extends MsGraphAdmin
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
                '$orderby' => 'displayName',
                '$top' => $top,
                '$skip' => $skip,
                '$count' => 'true',
            ]);
        } else {
            $params = http_build_query($params);
        }

        $contacts = MsGraphAdmin::get('users/'.$this->userId.'/contacts?'.$params);

        if (isset($contacts->error)) {
            throw new Exception("Graph API Error, code: {$contacts->error->code}, Message: {$contacts->error->message}");
        }

        $data = MsGraphAdmin::getPagination($contacts, $top, $skip);

        return [
            'contacts' => $contacts,
            'total' => $data['total'],
            'top' => $data['top'],
            'skip' => $data['skip'],
        ];
    }

    /**
     * @throws Exception
     */
    public function find(string $id): MsGraphAdmin
    {
        if ($this->userId == null) {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::get('users/'.$this->userId.'/contacts/'.$id);
    }

    /**
     * @throws Exception
     */
    public function store(array $data): MsGraphAdmin
    {
        if ($this->userId === '') {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::post('users/'.$this->userId.'/contacts', $data);
    }

    /**
     * @throws Exception
     */
    public function update(string $id, array $data): MsGraphAdmin
    {
        if ($this->userId === '') {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::patch('users/'.$this->userId.'/contacts/'.$id, $data);
    }

    /**
     * @throws Exception
     */
    public function delete(string $id): MsGraphAdmin
    {
        if ($this->userId === '') {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::delete('users/'.$this->userId.'/contacts/'.$id);
    }
}
