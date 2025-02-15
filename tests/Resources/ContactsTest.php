<?php

use Dcblogdev\MsGraph\Facades\MsGraph;
use Dcblogdev\MsGraph\Resources\Contacts;

beforeEach(function () {
    MsGraph::shouldReceive('isConnected')->andReturn(true);
    $this->contacts = new Contacts;
});

test('get contacts with params', function () {

    $messageQueryParams = [
        '$orderby' => 'displayName',
        '$skip' => 0,
        '$top' => 5,
        '$count' => 'true',
    ];

    MsGraph::shouldReceive('get')
        ->with('me/contacts?'.http_build_query($messageQueryParams))
        ->andReturn([
            '@odata.count' => 10,
            'value' => [
                ['id' => '1', 'displayName' => 'John Doe'],
                ['id' => '2', 'displayName' => 'Jane Doe'],
            ],
        ]);

    $response = (new Contacts)->get($messageQueryParams);

    expect($response)->toHaveKeys(['contacts', 'total', 'links', 'links_array'])
        ->and($response['contacts']['value'])->toBeArray()
        ->and($response['total'])->toBe(10);
});

test('getParams returns default values when no params provided', function () {

    $contacts = new Contacts;
    $reflection = new ReflectionMethod(Contacts::class, 'getParams');
    $response = $reflection->invoke($contacts, [], 25);

    parse_str($response, $parsedParams);

    expect($parsedParams)->toMatchArray([
        '$orderby' => 'displayName',
        '$top' => '25',
        '$skip' => '0',
        '$count' => 'true',
    ]);
});

test('getParams includes custom top parameter', function () {
    $contacts = new Contacts;
    $reflection = new ReflectionMethod(Contacts::class, 'getParams');
    $response = $reflection->invoke($contacts, ['$top' => 10], 25);

    parse_str($response, $parsedParams);

    expect($parsedParams)->toMatchArray([
        '$top' => '10',
        '$skip' => '0',
        '$count' => 'true',
    ]);
});

test('getParams includes custom skip parameter', function () {
    $contacts = new Contacts;
    $reflection = new ReflectionMethod(Contacts::class, 'getParams');
    $response = $reflection->invoke($contacts, ['$skip' => 15], 25);

    parse_str($response, $parsedParams);

    expect($parsedParams)->toMatchArray([
        '$top' => '25',
        '$skip' => '15',
        '$count' => 'true',
    ]);
});

test('getParams forces count to be true when missing', function () {
    $contacts = new Contacts;
    $reflection = new ReflectionMethod(Contacts::class, 'getParams');
    $response = $reflection->invoke($contacts, ['$top' => 10, '$skip' => 5], 25);

    parse_str($response, $parsedParams);

    expect($parsedParams)->toMatchArray([
        '$top' => '10',
        '$skip' => '5',
        '$count' => 'true',
    ]);
});

test('find method retrieves a specific contact', function () {

    MsGraph::shouldReceive('get')
        ->with('me/contacts/1')
        ->andReturn([
            'id' => '1',
            'displayName' => 'John Doe',
            'email' => 'johndoe@example.com',
        ]);

    $response = (new Contacts)->find('1');

    expect($response)
        ->toHaveKeys(['id', 'displayName', 'email'])
        ->and($response['id'])->toBe('1')
        ->and($response['displayName'])->toBe('John Doe')
        ->and($response['email'])->toBe('johndoe@example.com');
});

test('can create contact', function () {

    $data = [
        'displayName' => 'John Doe',
        'givenName' => 'John Doe',
        'emailAddresses' => [
            [
                'address' => 'john@doe.com',
                'name' => 'John Doe',
            ],
        ],
    ];

    MsGraph::shouldReceive('post')
        ->with('me/contacts', $data)
        ->andReturn([
            'id' => '1',
            'displayName' => 'John Doe',
            'email' => 'johndoe@example.com',
        ]);

    $response = (new Contacts)->store($data);

    expect($response)
        ->toHaveKeys(['id', 'displayName', 'email'])
        ->and($response['id'])->toBe('1')
        ->and($response['displayName'])->toBe('John Doe')
        ->and($response['email'])->toBe('johndoe@example.com');
});

test('can update contact', function () {

    $data = [
        'displayName' => 'John Doe',
        'givenName' => 'John Doe',
        'emailAddresses' => [
            [
                'address' => 'john@doe.com',
                'name' => 'John Doe',
            ],
        ],
    ];

    MsGraph::shouldReceive('patch')
        ->with('me/contacts/1', $data)
        ->andReturn([
            'id' => '1',
            'displayName' => 'John Doe',
            'email' => 'johndoe@example.com',
        ]);

    $response = (new Contacts)->update(1, $data);

    expect($response)
        ->toHaveKeys(['id', 'displayName', 'email'])
        ->and($response['id'])->toBe('1')
        ->and($response['displayName'])->toBe('John Doe')
        ->and($response['email'])->toBe('johndoe@example.com');
});

test('can delete contact', function () {

    MsGraph::shouldReceive('delete')
        ->with('me/contacts/1')
        ->andReturn('');

    $response = (new Contacts)->delete(1);

    expect($response)->toBe('');
});
