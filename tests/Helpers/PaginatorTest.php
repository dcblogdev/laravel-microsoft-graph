<?php

use Dcblogdev\MsGraph\Helpers\Paginator;

test('Paginator initializes correctly', function () {
    $paginator = new Paginator(10, 'page');

    expect($paginator)->toBeInstanceOf(Paginator::class);
});

test('get_start calculates correct offset', function () {
    $_GET['page'] = 3;
    $paginator = new Paginator(10, 'page');

    expect($paginator->get_start())->toBe(20);
});

test('setTotal updates totalRows correctly', function () {
    $paginator = new Paginator(10, 'page');
    $paginator->setTotal(50);

    expect($paginator->page_links_array())->toHaveCount(5);
});

test('page_links_array returns correct pagination', function () {
    $paginator = new Paginator(10, 'page');
    $paginator->setTotal(50);

    expect($paginator->page_links_array())->toBe([1, 2, 3, 4, 5]);
});

test('page_links generates correct HTML', function () {
    $paginator = new Paginator(10, 'page');
    $paginator->setTotal(30);

    $html = $paginator->page_links();

    expect($html)->toContain("<ul class='pagination'>")
        ->and($html)->toContain("<li><a href='?page=1'>1</a></li>");
});
