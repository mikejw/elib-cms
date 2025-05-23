<?php

namespace Empathy\ELib\DSection;

use Empathy\MVC\Model;
use Empathy\ELib\EController;
use Empathy\ELib\Storage\SectionItem;
use Empathy\ELib\Storage\DataItem;


class BaseTemplate extends EController
{
    protected $section;
    protected $data_item;

    public function __construct($boot)
    {
        parent::__construct($boot);
        $this->section = Model::load(SectionItem::class);
        $this->data_item = Model::load(DataItem::class);

        $this->section->load($_GET['section']);
        $this->assign('template', $this->section->template);
    }
}
