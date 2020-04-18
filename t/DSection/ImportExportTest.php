<?php

namespace ESuite\DSection;

use ESuite\ESuiteTest;
use Empathy\ELib\Model;
use Empathy\ELib\DSection\ImportExport;



class ImportExportTest extends ESuiteTest
{

    protected function setUp()
    {
        parent::setUp();
    }

    public function testHasSection()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures2.yml');
        $ie = new ImportExport();
        $output = json_decode($ie->export(0), JSON_OBJECT_AS_ARRAY);
        
        $this->assertEquals('New Section', $output[0]['label']);
        $this->assertEquals('This is a data item.', $output[0]['data'][0]['heading']);
    }
}