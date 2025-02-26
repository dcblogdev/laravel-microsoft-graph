<?php

use Dcblogdev\MsGraph\Validators\EmailFolderMoveValidator;

test('valid parameters are accepted', function () {
    $response = EmailFolderMoveValidator::validate([
        'destinationId' => 'demo',
    ]);

    expect($response)->toEqual([
        'destinationId' => 'demo',
    ]);
});

test('throws exception for unrecognized parameters', function () {
    EmailFolderMoveValidator::validate([
        '$top' => 10,
    ]);
})->throws(InvalidArgumentException::class, 'Invalid parameters: $top. Allowed parameters: destinationId.');

test('throws exception if destinationId is not a string', function () {
    EmailFolderMoveValidator::validate(['destinationId' => 1]);
})->throws(InvalidArgumentException::class, 'The destinationId must be a string.');

test('allows empty input without throwing an exception', function () {
    $response = EmailFolderMoveValidator::validate([]);
    expect($response)->toBe([]);
});
