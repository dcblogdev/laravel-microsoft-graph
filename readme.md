# Box

A Laravel package for working with Microsoft Graph API, this includes authentication use Oauth2.

## Installation

Via Composer

``` bash
$ composer require daveismynamelaravel/msgraph
```

TO Document:
* run migration,
* Publish config
* Edit config
* Add ENV variables

## Usage

A routes example:

```
use DaveismynameLaravel\MsGraph\Facades\MsGraph;

Route::group(['middleware' => ['web', 'auth']], function(){
    Route::get('msgraph', function(){

        if (!is_string(MsGraph::getAccessToken())) {
            return redirect('msgraph/oauth');
        } else {
            //box folders and file list
            return MsGraph::folders();
        }
    });

    Route::get('msgraph/oauth', function(){
        return MsGraph::connect();
    });
});
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.


## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [David Carr][dave@daveismyname.com]

## License

license. Please see the [license file](license.md) for more information.
