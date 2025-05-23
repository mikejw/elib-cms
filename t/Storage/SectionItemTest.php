<?php

namespace ESuite\Storage;

use ESuite\ESuiteTest;
use Empathy\MVC\Model;
use Empathy\ELib\Storage\SectionItem;
use Empathy\ELib\Storage\DataItem;
use Empathy\ELib\DSection\SectionsTree;


class SectionItemTest extends ESuiteTest
{

    protected function setUp()
    {
        parent::setUp();
    }

    public function testGetSectionItemEntity()
    {
        $s = Model::load(SectionItem::class);
        $this->assertInstanceOf(SectionItem::class, $s);
    }

    public function testHasSection()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures1.yml');
        $s = Model::load(SectionItem::class, 1);
        $d = Model::load(DataItem::class);

        $tree = new SectionsTree(
            $s, $d, true, null, true
        );
        $sections = $s->buildTree($s->id, $tree);
        $this->assertEquals(1, sizeof($sections));
    }

    public function testRootIsSection()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures1.yml');
        $s = Model::load(SectionItem::class);
        $d = Model::load(DataItem::class);

        $tree = new SectionsTree(
            $s, $d, true, null, true
        );
        $sections = $s->buildTree($s->id, $tree);
        $this->assertTrue(sizeof($sections[0]['children']) === 1);
    }

    public function testHasSectionAndData()
    {
        $this->loadFixtures('fixtures/dd.sql', '/fixtures/fixtures2.yml');
        $s = Model::load(SectionItem::class, 0);
        $d = Model::load(DataItem::class);

        $tree = new SectionsTree(
            $s, $d, true, null, true
        );

        $sections = $s->buildTree($s->id, $tree);
        $this->assertEquals('New Data', $sections[0]['children'][0]['label']);
    }
}
