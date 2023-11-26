<?php

namespace Dcblogdev\MsGraph\AdminResources;

use Dcblogdev\MsGraph\Facades\MsGraphAdmin;
use Exception;
use Illuminate\Http\RedirectResponse;

class Files extends MsGraphAdmin
{
    private string $userId = '';

    public function userid(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function getDrives(): MsGraphAdmin
    {
        if ($this->userId === '') {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::get('users/'.$this->userId.'/drives');
    }

    /**
     * @throws Exception
     */
    public function downloadFile(string $id): RedirectResponse
    {
        if ($this->userId === '') {
            throw new Exception('userId is required.');
        }

        $id = MsGraphAdmin::get('users/'.$this->userId.'/drive/items/'.$id);

        return redirect()->away($id['@microsoft.graph.downloadUrl']);
    }

    /**
     * @throws Exception
     */
    public function deleteFile(string $id): MsGraphAdmin
    {
        if ($this->userId === '') {
            throw new Exception('userId is required.');
        }

        return MsGraphAdmin::delete('users/'.$this->userId.'/drive/items/'.$id);
    }
}
