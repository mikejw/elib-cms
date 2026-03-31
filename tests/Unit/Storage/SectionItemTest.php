<?php

declare(strict_types=1);

use Empathy\ELib\DSection\SectionsTree;
use Empathy\ELib\Storage\DataItem;
use Empathy\ELib\Storage\SectionItem;
use Empathy\MVC\Model;

test('get section item entity', function () {
    $section = Model::load(SectionItem::class);

    expect($section)->toBeInstanceOf(SectionItem::class);
});

test('has section', function () {
    loadFixtures('fixtures/dd.sql', '/fixtures/fixtures1.yml');
    $section = Model::load(SectionItem::class, 1);
    $data = Model::load(DataItem::class);

    $tree = new SectionsTree($section, $data, true, null, true);
    $sections = $section->buildTree($section->id, $tree);

    expect($sections)->toHaveCount(1);
});

test('root is section', function () {
    loadFixtures('fixtures/dd.sql', '/fixtures/fixtures1.yml');
    $section = Model::load(SectionItem::class);
    $data = Model::load(DataItem::class);

    $tree = new SectionsTree($section, $data, true, null, true);
    $sections = $section->buildTree($section->id, $tree);

    expect($sections[0]['children'])->toHaveCount(1);
});

test('has section and data', function () {
    loadFixtures('fixtures/dd.sql', '/fixtures/fixtures2.yml');
    $section = Model::load(SectionItem::class, 0);
    $data = Model::load(DataItem::class);

    $tree = new SectionsTree($section, $data, true, null, true);
    $sections = $section->buildTree($section->id, $tree);

    expect($sections[0]['children'][0]['label'])->toBe('New Data');
});
