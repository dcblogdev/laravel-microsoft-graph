<?php

use Dcblogdev\MsGraph\MsGraphAdmin;

test('can initalise', function () {
    $msGraphAdmin = new MsGraphAdmin();

    $this->assertInstanceOf(MsGraphAdmin::class, $msGraphAdmin);
});