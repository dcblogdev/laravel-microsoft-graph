<?php

use Dcblogdev\MsGraph\Validators\EmailFolderUpdateValidator;

test('valid parameters are accepted', function () {
    $response = EmailFolderUpdateValidator::validate([
        'displayName' => 'demo',
    ]);

    expect($response)->toEqual([
        'displayName' => 'demo',
    ]);
});

test('throws exception for unrecognized parameters', function () {
    EmailFolderUpdateValidator::validate([
        '$top' => 10,
    ]);
})->throws(InvalidArgumentException::class, 'Invalid parameters: $top. Allowed parameters: displayName.');

test('throws exception if displayName is not a string', function () {
    EmailFolderUpdateValidator::validate(['displayName' => 1]);
})->throws(InvalidArgumentException::class, 'The displayName must be a string.');

test('allows empty input without throwing an exception', function () {
    $response = EmailFolderUpdateValidator::validate([]);
    expect($response)->toBe([]);
});
