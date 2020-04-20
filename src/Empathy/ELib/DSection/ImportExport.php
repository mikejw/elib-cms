<?php

namespace Empathy\ELib\DSection;

use Empathy\ELib\DSection\SectionsTree;
use Empathy\ELib\Model;
use Empathy\MVC\Entity;
use Empathy\MVC\Config;
use Empathy\ELib\User\CurrentUser;

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
        $s->user_id = CurrentUser::getUserID();
        $s->hidden = $section['hidden'];
        $s->template = $section['template'];
        $s->position = $section['position'];
        $s->meta = $section['meta'];
        return $s->insert(Model::getTable('SectionItem'), true, array(), Entity::SANITIZE_NO_POST);
    }

    private function insertData($target_id, $parent_id, $sectionParent = false)
    {
        $target = Model::load('DataItem');
        $target->id = $target_id;
        $target->load();

        $data = Model::load('DataItem');

        if ($sectionParent) {
            $data->section_id = $parent_id;
        } else {
            $data->data_item_id = $parent_id;
        }

        $data->label = $target->label;
        $data->user_id = $target->user_id;
        $data->hidden = $target->hidden;

        if ($target->image) {
            $attempt = 1;
            $success = false;
            $path = Config::get('DOC_ROOT') . '/public_html/uploads';
            while ($success === false) {
                $name = $attempt . '___' . $target->image;
                $success = copy($path . '/' . $target->image, $path . '/' . $name);
                $attempt++;
            }
            copy($path . '/' . 'l_' . $target->image, $path . '/' . 'l_' . $name);
            copy($path . '/' . 'mid_' . $target->image, $path . '/' . 'mid_' . $name);
            copy($path . '/' . 'tn_' . $target->image, $path . '/' . 'tn_' . $name);

            $data->image = $name;
            $data->label = $name;
        } else {
            $data->image = $target->image;
        }

        $data->body = $target->body;
        $data->position = $target->position;
        return $data->insert(Model::getTable('DataItem'), true, array(), Entity::SANITIZE_NO_POST);
    }

    private function populate($sectionsData, $parent_id)
    {
        foreach ($sectionsData as $item) {
            $id = $this->insertSection($parent_id, $item);

            if (isset($item['children']) && sizeof($item['children'])) {
                $this->populate($item['children'], $id);
            }

            if (isset($item['data']) && sizeof($item['data'])) {
                $this->populateData($item['data'], $id);
            }
        }
    }

    private function populateData($data, $parent_id, $sectionParent = false)
    {
        foreach ($data as $item) {
            $id = $this->insertData($item->id, $parent_id, $sectionParent);

            if (sizeof($item->data)) {
                $this->populateData($item->data, $id);
            }
        }
    }

    public function export($target_id)
    {
        $target_id = (int) $target_id;
        $target = Model::load('SectionItem');
        $target->id = $target_id;
        $target->load();
        $target_parent_id = $target->section_id;

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
               'children' => $sectionsData
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
                'children' => $sectionsData
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




