<?php

declare(strict_types=1);

use Empathy\ELib\DSection\ImportExport;
use Empathy\MVC\DI;

test('section root has section and data', function () {
    $this->loadFixtures('fixtures/dd.sql', 'fixtures/fixtures2.yml');
    $ie = new ImportExport();
    $output = json_decode($ie->export(0), true);

    expect($output['label'])->toBe('New Section');
    expect($output['children'][0]['data'][0]['heading'])->toBe('This is a data item.');
});

test('has section and data', function () {
    $this->loadFixtures('fixtures/dd.sql', 'fixtures/fixtures2.yml');
    $ie = new ImportExport();
    $output = json_decode($ie->export(1), true);

    expect($output['label'])->toBe('New Section');
    expect($output['data'][0]['heading'])->toBe('This is a data item.');
});

test('has section', function () {
    $this->loadFixtures('fixtures/dd.sql', 'fixtures/fixtures3.yml');
    $ie = new ImportExport();
    $output = json_decode($ie->export(1), true);

    expect($output['label'])->toBe('New Test Section');
});

test('populate section from root', function () {
    $this->loadFixtures('fixtures/dd.sql', 'fixtures/fixtures3.yml');
    $currentUser = DI::getContainer()->get('CurrentUser');
    $currentUser->detectUser();
    $currentUser->setUserID(1);

    $ie = new ImportExport();
    $output = $ie->export(0);
    $ie->import(0, $output);
    $output = json_decode($ie->export(2), true);

    expect($output['children'][0]['label'])->toBe('New Test Section');
});

test('populate section and data', function () {
    $this->loadFixtures('fixtures/dd.sql', 'fixtures/fixtures2.yml');
    $currentUser = DI::getContainer()->get('CurrentUser');
    $currentUser->detectUser();
    $currentUser->setUserID(1);

    $ie = new ImportExport();
    $output = $ie->export(1);
    $ie->import(0, $output);
    $output = json_decode($ie->export(0), true);

    expect($output['children'][1]['data'][0]['heading'])->toBe('This is a data item.');
});
