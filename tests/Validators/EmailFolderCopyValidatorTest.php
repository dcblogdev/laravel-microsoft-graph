<?php

use Dcblogdev\MsGraph\Validators\EmailFolderCopyValidator;

test('valid parameters are accepted', function () {
    $response = EmailFolderCopyValidator::validate([
        'destinationId' => 'demo'
    ]);

    expect($response)->toEqual([
        'destinationId' => 'demo'
    ]);
});

test('throws exception for unrecognized parameters', function () {
    EmailFolderCopyValidator::validate([
        '$top' => 10,
    ]);
})->throws(InvalidArgumentException::class, 'Invalid parameters: $top. Allowed parameters: destinationId.');

test('throws exception if destinationId is not a string', function () {
    EmailFolderCopyValidator::validate(['destinationId' => 1]);
})->throws(InvalidArgumentException::class, 'The destinationId must be a string.');

test('allows empty input without throwing an exception', function () {
    $response = EmailFolderCopyValidator::validate([]);
    expect($response)->toBe([]);
});
