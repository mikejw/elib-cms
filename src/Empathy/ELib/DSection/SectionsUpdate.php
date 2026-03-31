<?php

declare(strict_types=1);

namespace Empathy\ELib\DSection;

use Empathy\ELib\Storage\SectionItem;

class SectionsUpdate
{
    public SectionItem $section;

    public function __construct(SectionItem $section, int $section_id)
    {
        $this->section = $section;
        $this->section->id = $section_id;
        $this->update_timestamps();
    }

    public function update_timestamps(): void
    {
        // current section
        $this->section->load($this->section->id);
        $this->section->stamp = date('Y-m-d H:i:s', time());
        $this->section->save();

        // ancestors => make optional?
        $ancestors = [];
        $ancestors = $this->section->getAncestorIDs($this->section->id, $ancestors);
        if (count($ancestors) > 0) {
            $update = $this->section->buildUnionString($ancestors);
            $this->section->updateTimestamps($update);
        }
    }
}
