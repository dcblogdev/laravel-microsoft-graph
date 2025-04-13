<?php

use Dcblogdev\MsGraph\Validators\Validator;

test('can validate', function () {
    $response = Validator::validate([]);

    expect($response)->toEqual([]);
});

test('cannot validate none existing params', function () {
    Validator::validate([
        '$top' => 10,
    ]);
})->throws(InvalidArgumentException::class, 'Invalid parameters: $top. Allowed parameters: .');
