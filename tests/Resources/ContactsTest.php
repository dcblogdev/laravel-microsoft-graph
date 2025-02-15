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
