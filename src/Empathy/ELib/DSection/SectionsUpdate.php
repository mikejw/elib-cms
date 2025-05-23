<?php

namespace Empathy\ELib\DSection;

use Empathy\ELib\DSection;
use Empathy\MVC\Model;
use Empathy\MVC\Model\SectionItem as SectionItem;

class SectionsUpdate
{
    public $section;

    public function __construct($section, $section_id)
    {
        $this->section = $section;
        $this->section->id = $section_id;
        $this->update_timestamps();
    }

    public function update_timestamps()
    {
        // current section
        $this->section->load();
        $this->section->stamp = date('Y-m-d H:i:s', time());
        $this->section->save();

        // ancestors => make optional?
        $ancestors = array();
        $ancestors = $this->section->getAncestorIDs($this->section->id, $ancestors);
        if (sizeof($ancestors) > 0) {
            $update = $this->section->buildUnionString($ancestors);
            $this->section->updateTimestamps($update);
        }
    }

}
