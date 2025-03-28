<?php

use Dcblogdev\MsGraph\Validators\EmailFolderStoreValidator;

test('valid parameters are accepted', function () {
    $response = EmailFolderStoreValidator::validate([
        'displayName' => 'demo',
        'isHidden' => false,
    ]);

    expect($response)->toEqual([
        'displayName' => 'demo',
        'isHidden' => false,
    ]);
});

test('throws exception for unrecognized parameters', function () {
    EmailFolderStoreValidator::validate([
        '$top' => 10,
    ]);
})->throws(InvalidArgumentException::class, 'Invalid parameters: $top. Allowed parameters: displayName, isHidden.');

test('throws exception if displayName is not a string', function () {
    EmailFolderStoreValidator::validate(['displayName' => 1]);
})->throws(InvalidArgumentException::class, 'The displayName must be a string.');

test('throws exception if isHidden is not a boolean', function () {
    EmailFolderStoreValidator::validate(['isHidden' => 'true']);
})->throws(InvalidArgumentException::class, 'The isHidden must be a boolean.');

test('throws exception if isHidden is an integer', function () {
    EmailFolderStoreValidator::validate(['isHidden' => 1]);
})->throws(InvalidArgumentException::class, 'The isHidden must be a boolean.');

test('allows empty input without throwing an exception', function () {
    $response = EmailFolderStoreValidator::validate([]);
    expect($response)->toBe([]);
});
