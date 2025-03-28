<?php

use Dcblogdev\MsGraph\Validators\GraphQueryValidator;

test('can validate', function () {
    $response = GraphQueryValidator::validate([
        '$top' => 10,
        '$skip' => 5,
        '$filter' => 'name eq \'test\'',
        '$orderby' => 'name asc',
        '$select' => 'name',
        '$expand' => 'children',
        '$count' => 'true',
        '$search' => 'test',
        '$format' => 'pdf',
    ]);

    expect($response)->toEqual([
        '$top' => 10,
        '$skip' => 5,
        '$filter' => 'name eq \'test\'',
        '$orderby' => 'name asc',
        '$select' => 'name',
        '$expand' => 'children',
        '$count' => 'true',
        '$search' => 'test',
        '$format' => 'pdf',
    ]);
});

test('cannot validate none existing params', function () {
    GraphQueryValidator::validate([
        '$tops' => 10,
        '$skip' => 5,
        '$filter' => 'name eq \'test\'',
        '$orderby' => 'name asc',
        '$select' => 'name',
        '$expand' => 'children',
        '$count' => 'true',
        '$search' => 'test',
        '$format' => 'pdf',
    ]);
})->throws(InvalidArgumentException::class, 'Invalid parameters: $tops. Allowed parameters: $top, $skip, $filter, $orderby, $select, $expand, $count, $search, $format.');
