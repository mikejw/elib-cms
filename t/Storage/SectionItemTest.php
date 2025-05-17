<?php

namespace ESuite\Storage;

use ESuite\ESuiteTest;
use Empathy\ELib\Model;
use Empathy\ELib\Storage\SectionItem;
use Empathy\ELib\DSection\SectionsTree;


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

    public function testHasSection()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures1.yml');
        $s = Model::load('SectionItem');
        $s->load(1);
        $d = Model::load('DataItem');

        $tree = new SectionsTree(
            $s, $d, true, null, true
        );
        $sections = $s->buildTree($s->id, $tree);
        $this->assertEquals(1, sizeof($sections));
    }

    public function testRootIsSection()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures1.yml');
        $s = Model::load('SectionItem');
        $s->id = 0;
        $d = Model::load('DataItem');

        $tree = new SectionsTree(
            $s, $d, true, null, true
        );
        $sections = $s->buildTree($s->id, $tree);
        $this->assertTrue(sizeof($sections[0]['children']) === 1);
    }

    public function testHasSectionAndData()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures2.yml');
        $s = Model::load('SectionItem');
        $s->load(0);
        $d = Model::load('DataItem');

        $tree = new SectionsTree(
            $s, $d, true, null, true
        );

        $sections = $s->buildTree($s->id, $tree);
        $this->assertEquals('New Data', $sections[0]['children'][0]['label']);
    }
}
