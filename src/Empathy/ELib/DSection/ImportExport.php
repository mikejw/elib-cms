<?php

namespace Empathy\ELib\DSection;

use Empathy\ELib\DSection\SectionsTree;
use Empathy\MVC\Model;
use Empathy\ELib\Storage\SectionItem;
use Empathy\ELib\Storage\DataItem;
use Empathy\ELib\Storage\ContainerImageSize;
use Empathy\MVC\Entity;
use Empathy\MVC\Config;
use Empathy\MVC\DI;

class ImportExport
{
    private function load($sectionId)
    {
        $sectionsData = [];
        $section = Model::load(SectionItem::class, $sectionId);

        $sections = $section->buildTree($section->id, new SectionsTree(
            $section,
            null,
            true,
            null,
            true
        ));

        foreach ($sections as &$item) {
            $item['type'] = 'section';
            $data = Model::load(DataItem::class);
            $data->setExporting();
            $item['data'] = $data->getSectionDataRecursive($item['id']);

            if (isset($item['children']) && sizeof($item['children'])) {
                $item['children'] = $this->load($item['id']);
            }
            $sectionsData[] = $item;
        }
        return $sectionsData;
    }

    private function insertSection($parent_id, $data, $topLevelSection)
    {
        if (isset($data['template'])) {
            $s = Model::load(SectionItem::class);
            $s->section_id = $parent_id;
            $s->label = $data['label'];
            $s->user_id = DI::getContainer()->get('CurrentUser')->getUserID();
            $s->hidden = $data['hidden'];
            $s->template = $data['template'];
            $s->position = $data['position'];
            $s->meta = $data['meta'];
            $s->stamp = $data['stamp'] ?
                is_numeric($data['stamp']) ?
                    date('Y-m-d H:i:s', $data['stamp'])
                    : $data['stamp']
                : 'MYSQLTIME';

            return [true, $s->insert()];
        } else {
            return [false, $this->insertData($parent_id, $data, true, $topLevelSection)];
        }
    }

    private function insertData($parent_id, $data, $sectionParent, $topLevelSection = false)
    {
        $d = Model::load(DataItem::class);
        if (($sectionParent || $topLevelSection) && !isset($data['data_item_id'])) {
            $d->section_id = $parent_id;
        } else {
            $d->data_item_id = $parent_id;
        }

        $d->label = $data['label'];
        $d->user_id = $data['user_id'];
        $d->hidden = $data['hidden'];
        $d->container_id = $data['container_id'];
        $d->heading = $data['heading'];
        $d->body = $data['body'];
        $d->position = $data['position'];
        $d->video = $data['video'];
        $d->meta = $data['meta'];
        $d->stamp = $data['stamp'];
        $d->image = $data['image'];

        if ($data['image']) {
            $data['image'] = trim(escapeshellarg($data['image']), '\'');
        }

        $path = Config::get('DOC_ROOT') . '/public_html/uploads';
        if ($data['image'] && file_exists($path . '/' . $data['image'])) {
            $attempt = 0;
            $success = false;
            while ($success === false) {
                $attempt++;
                $name = $attempt . '__' . $data['image'];
                if (!file_exists($path . '/' . $name)) {
                    $success = copy($path . '/' . $data['image'], $path . '/' . $name);
                }
            }

            $imagePrefixes = ['mid', 'l', 'tn'];
            $parentId = $data['data_item_id'];
            if (isset($parentId)) {
                $parent = Model::load(DataItem::class, $parentId);
                if ($parent->isContainer() && isset($parent->container_id)) {
                    $c = Model::load(ContainerImageSize::class);
                    $imageSizes = $c->getImageSizes($parent->container_id);
                    if (count($imageSizes) > 0) {
                        $imagePrefixes = array_map(function($item) {
                            return $item[0];
                        }, $imageSizes);
                    }
                }
            }
            foreach ($imagePrefixes as $prefix) {
                if (file_exists("$path/${prefix}_" . $data['image'])) {
                    copy("$path/${prefix}_" . $data['image'], "$path/${prefix}_" . $name);
                }
            }

            $d->image = $name;
            $d->label = $name;
        }
        return $d->insert();
    }

    private function populate($sectionsData, $parent_id, $sectionParent = false, $topLevelSection = false)
    {
        foreach ($sectionsData as $item) {

            if ($sectionParent) {
                list($sectionParent, $id) = $this->insertSection($parent_id, $item, $topLevelSection);
            } else {
                $id = $this->insertData($parent_id, $item, $sectionParent, $topLevelSection);
            }
            

            if (isset($item['children']) && sizeof($item['children'])) {
                $this->populate($item['children'], $id, true);
            }


            if (isset($item['data']) && is_array($item['data'])) {
                $this->populateData($item['data'], $id, $sectionParent);
            }
        }
    }

    private function populateData($data, $parent_id, $sectionParent = true)
    {
        foreach ($data as $item) {
            $id = $this->insertData($parent_id, $item, $sectionParent);

            if (sizeof($item['data'])) {
                $this->populateData($item['data'], $id, false);
            }
        }
    }

    public function export($target_id)
    {
        $target_id = (int) $target_id;
        $target = Model::load(SectionItem::class);
        $data = Model::load(DataItem::class);
        $data->setExporting();
        $target->load($target_id);


        $sectionsData = $this->load($target_id);

        if ($target_id === 0) {
            $sectionsData = array(
                'section_id' => null,
                'label' => 'New Section',
                'friendly_url' => null,
                'template' => 'A',
                'position' => 0,
                'hidden' => 0,
                'stamp' => null,
                'meta' => null,
                'user_id' => null,
                'children' => $sectionsData,
                'data' => $data->getSectionDataRecursive(0)
            );
        } else {
            $sectionsData = array(
                'section_id' => $target->section_id,
                'label' => $target->label,
                'friendly_url' => $target->friendly_url,
                'template' => $target->template,
                'position' => $target->position,
                'hidden' => $target->hidden,
                'stamp' => $target->stamp,
                'meta' => $target->meta,
                'user_id' => $target->user_id,
                'children' => $sectionsData,
                'data' => $data->getSectionDataRecursive($target->id)
            );
        }
        
        return json_encode($sectionsData, JSON_PRETTY_PRINT);
    }

    public function import($target_parent_id, $sectionsData)
    {
        $sectionsData = '[' . $sectionsData . ']';
        $this->populate(json_decode($sectionsData, JSON_OBJECT_AS_ARRAY), $target_parent_id, true);
    }

    public function exportContainer($target_id)
    {
        $target_id = (int) $target_id;
        $data = Model::load(DataItem::class);

        $data->load($target_id);
        $data->setExporting(); 
        $data->getSectionDataRecursive();
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public function importContainer($target_parent_id, $data, $topLevelSection)
    {
        $data = '[' . $data . ']';
        $this->populate(json_decode($data, JSON_OBJECT_AS_ARRAY), $target_parent_id, false, $topLevelSection);
    }
}




