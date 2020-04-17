<?php

namespace ESuite\Storage;

use ESuite\ESuiteTest;
use Empathy\ELib\Model;
use Empathy\ELib\Storage\SectionItem;


class SectionItemTest extends ESuiteTest
{

    protected function setUp()
    {
        // set up base database fixures
    }

    public function testGetSectionItemEntity()
    {
        $s = Model::load('SectionItem');
        $this->assertInstanceOf(SectionItem::class, $s);
    }
}
