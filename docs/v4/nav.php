<?php

return [
    [
        'name' => 'Introduction',
        'url' => 'introduction',
    ],
    [
        'name' => 'Install',
        'url' => 'install',
    ],
    [
        'name' => 'MsGraph',
        'url' => 'msgraph',
        'children' => [
            [
                'name' => 'Is Connected',
                'url' => 'msgraph/is-connected',
            ],
            [
                'name' => 'Disconnect',
                'url' => 'msgraph/disconnect',
            ],
            [
                'name' => 'Middleware',
                'url' => 'msgraph/middleware',
            ],
            [
                'name' => 'Queues',
                'url' => 'msgraph/queues',
            ],
            [
                'name' => 'Login with MsGraph',
                'url' => 'msgraph/login-with-msgraph',
            ],
            [
                'name' => 'Contacts',
                'url' => 'msgraph/contacts',
            ],
            [
                'name' => 'Emails',
                'url' => 'msgraph/emails',
            ],
            [
                'name' => 'Files',
                'url' => 'msgraph/files',
            ],
            [
                'name' => 'Filesystem',
                'url' => 'msgraph/filesystem',
            ],
        ],
    ],
    [
        'name' => 'MsGraph Admin',
        'url' => 'msgraphadmin',
        'children' => [
            [
                'name' => 'Middleware',
                'url' => 'msgraphadmin/middleware',
            ],
            [
                'name' => 'Contacts',
                'url' => 'msgraphadmin/contacts',
            ],
            [
                'name' => 'Emails',
                'url' => 'msgraphadmin/emails',
            ],
        ],
    ],
];
