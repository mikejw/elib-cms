<?php

namespace ESuite\DSection;

use ESuite\ESuiteTest;
use Empathy\ELib\Model;
use Empathy\ELib\DSection\ImportExport;
use Empathy\ELib\User\CurrentUser;


class ImportExportTest extends ESuiteTest
{

    protected function setUp()
    {
        parent::setUp();
    }

    
    public function testSectionRootHasSectionAndData()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures2.yml');
        $ie = new ImportExport();
        $output = json_decode($ie->export(0), JSON_OBJECT_AS_ARRAY);
        $this->assertEquals('New Section', $output[0]['label']);
        $this->assertEquals('This is a data item.', $output[0]['data'][0]['heading']);
    }

    public function testHasSectionAndData()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures2.yml');
        $ie = new ImportExport();
        $output = json_decode($ie->export(1), JSON_OBJECT_AS_ARRAY);
        $this->assertEquals('New Section', $output[0]['label']);
        $this->assertEquals('This is a data item.', $output[0]['data'][0]['heading']);
    }

    public function testHasSection()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures3.yml');
        $ie = new ImportExport();
        $output = json_decode($ie->export(1), JSON_OBJECT_AS_ARRAY);
        $this->assertEquals('New Section', $output[0]['label']);
    }


    public function testPopulateSection()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures3.yml');
        CurrentUser::detectUser();
        CurrentUser::setUserID(1);
        $ie = new ImportExport();
        $output = $ie->export(1);
        $ie->import(0, $output);

        $output = json_decode($ie->export(0), JSON_OBJECT_AS_ARRAY);
        
        $this->assertEquals(2, sizeof($output));
    }
}