# Microsoft Graph API

A Laravel package for working with Microsoft Graph API, this includes authentication use Oauth2.

## Installation

Via Composer

``` bash
$ composer require daveismynamelaravel/msgraph
```

To Document:
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

Contributions are welcome and will be fully credited.

Contributions are accepted via Pull Requests on [Github](https://github.com/daveismynamelaravel/msgrapth).

## Pull Requests

- **Document any change in behaviour** - Make sure the `readme.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

## Security

If you discover any security related issues, please email dave@daveismyname.com email instead of using the issue tracker.

## Credits

- [David Carr][dave@daveismyname.com]

## License

license. Please see the [license file](license.md) for more information.
