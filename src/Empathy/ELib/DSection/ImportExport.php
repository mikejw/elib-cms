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

        if (sizeof($sections) < 1) {
            $sections = $section->getAllCustom(
                Model::getTable('SectionItem'),
                ' where id = ' . $section_id
             );
        }

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

    private function insertSection($target_id, $parent_id = null)
    {
        $target = Model::load('SectionItem');
        $target->id = $target_id;
        $target->load();

        $section = Model::load('SectionItem');

        if ($parent_id) {
            $section->section_id = $parent_id;
        } else {
            if ($target->secttion_id = 0) {
                $section->section_id = 0;
            } else {
                $section->section_id = $target->section_id;
            }
        }

        $section->label = $target->label;
        $section->user_id = CurrentUser::getUserID();
        $section->hidden = $target->hidden;
        $section->template = $target->template;
        $section->position = $target->position;
        $section->meta = $target->meta;
        return $section->insert(Model::getTable('SectionItem'), true, array(), Entity::SANITIZE_NO_POST);
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
            $id = $this->insertSection($item->id, $parent_id);

            if (sizeof($item->children)) {
                $this->populate($item->children, $id);
            }

            if (sizeof($item->data)) {
                $this->populateData($item->data, $id, true);
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
               'hidden' => false,
               'stamp' => null,
               'meta' => null,
               'user_id' => null,
               'children' => $sectionsData
            );
        } else {
            $sectionsData = $sectionsData[0];
        }

        return json_encode($sectionsData, JSON_PRETTY_PRINT);
    }

    public function import($target_parent_id, $sectionsData)
    {

        //$root_parent_id = $this->insertSection($target_id);
        //$this->populate(json_decode($sectionsData, JSON_OBJECT_AS_ARRAY), $root_id);
    }
}




