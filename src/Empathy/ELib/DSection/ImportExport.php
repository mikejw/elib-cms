<?php

namespace Empathy\ELib\DSection;

use Empathy\ELib\DSection\SectionsTree;
use Empathy\ELib\Model;
use Empathy\MVC\Entity;
use Empathy\MVC\Config;
use Empathy\MVC\DI;

class ImportExport
{
    private function load($section_id)
    {
        $sectionsData = [];
        $section = Model::load('SectionItem');
        $section->id = $section_id;
        $section->load();

        $sections = $section->buildTree($section->id, new SectionsTree(
            $section,
            null,
            true,
            null,
            true
        ));

        foreach ($sections as &$item) {
            $item['type'] = 'section';
            $data = Model::load('DataItem');
            $data->setExporting();
            $item['data'] = $data->getSectionDataRecursive($item['id']);

            if (isset($item['children']) && sizeof($item['children'])) {
                $item['children'] = $this->load($item['id']);
            }
            $sectionsData[] = $item;
        }
        return $sectionsData;
    }

    private function insertSection($parent_id, $section)
    {
        $s = Model::load('SectionItem');
        $s->section_id = $parent_id;
        $s->label = $section['label'];
        $s->user_id = DI::getContainer()->get('CurrentUser')->getUserID();
        $s->hidden = $section['hidden'];
        $s->template = $section['template'];
        $s->position = $section['position'];
        $s->meta = $section['meta'];
        return $s->insert(Model::getTable('SectionItem'), true, array(), Entity::SANITIZE_NO_POST);
    }

    private function insertData($parent_id, $data, $sectionParent)
    {
        $d = Model::load('DataItem');
        if ($sectionParent) {
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
        $d->positino = $data['position'];
        $d->meta = $data['meta'];
        $d->stamp = $data['stamp'];
        $d->image = $data['image'];

        if ($data['image']) {
            $data['image'] = escapeshellarg($data['image']);
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
            if (file_exists($path . '/' . 'l_' . $data['image'])) {
                copy($path . '/' . 'l_' . $data['image'], $path . '/' . 'l_' . $name);
            }
            if (file_exists($path . '/' . 'mid_' . $data['image'])) {
                copy($path . '/' . 'mid_' . $data['image'], $path . '/' . 'mid_' . $name);
            }
            if (file_exists($path . '/' . 'tn_' . $data['image'])) {
                copy($path . '/' . 'tn_' . $data['image'], $path . '/' . 'tn_' . $name);
            }

            $d->image = $name;
            $d->label = $name;
        }
        return $d->insert(Model::getTable('DataItem'), true, array(), Entity::SANITIZE_NO_POST);
    }

    private function populate($sectionsData, $parent_id)
    {
        foreach ($sectionsData as $item) {
            $id = $this->insertSection($parent_id, $item);

            if (isset($item['children']) && sizeof($item['children'])) {
                $this->populate($item['children'], $id);
            }

            if (isset($item['data']) && is_array($item['data'])) {
                $this->populateData($item['data'], $id);
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
        $target = Model::load('SectionItem');
        $data = Model::load('DataItem');
        $target->id = $target_id;
        $target->load();


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
        $this->populate(json_decode($sectionsData, JSON_OBJECT_AS_ARRAY), $target_parent_id);
    }
}




