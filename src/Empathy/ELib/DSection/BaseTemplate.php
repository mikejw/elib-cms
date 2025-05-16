<?php

namespace Empathy\ELib\DSection;

use Empathy\ELib\Model,
    Empathy\ELib\EController;

class BaseTemplate extends EController
{
    protected $section;
    protected $data_item;

    public function __construct($boot)
    {
        parent::__construct($boot);
        $this->section = Model::load('SectionItem');
        $this->data_item = Model::load('DataItem');

        $this->section->load($_GET['section']);
        $this->assign('template', $this->section->template);
    }

}
