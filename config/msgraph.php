<?php

return [
    'clientId' => env('MSGRAPH_CLIENT_ID'),
    'clientSecret' => env('MSGRAPH_SECRET_ID'),
    'redirectUri' => url('msgraph/oauth'),
    'msgraphLandingUri'  => url('msgraph'),
    'urlAuthorize' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    'urlAccessToken' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
    'scopes' => 'offline_access openid calendars.readwrite contacts.readwrite files.readwrite mail.readwrite mail.send tasks.readwrite mailboxsettings.readwrite user.readwrite',
    'preferTimezone' => env('MSGRAPH_PREFER_TIMEZONE', 'outlook.timezone="Europe/London"'),
];
