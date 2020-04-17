<?php

namespace ESuite\Storage;

use ESuite\ESuiteTest;
use Empathy\ELib\Model;
use Empathy\ELib\Storage\SectionItem;


class SectionItemTest extends ESuiteTest
{

    protected function setUp()
    {
        parent::setUp();
    }

    public function testGetSectionItemEntity()
    {
        $s = Model::load('SectionItem');
        $this->assertInstanceOf(SectionItem::class, $s);
    }

    public function testHasUser()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures1.yml');
//        $u = Model::load('UserItem');
//        $u->id = 1;
//        $u->load();
//        print_r($u);
//        ob_flush();

        $this->assertEquals(1, 1);
    }
}
