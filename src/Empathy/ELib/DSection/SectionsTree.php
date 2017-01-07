<?php

namespace Empathy\ELib\DSection;


use Empathy\ELib\Tree;
use Empathy\MVC\Config;

class SectionsTree extends Tree
{
    private $section;
    private $data_item;
    private $data;
    private $section_ancestors;
    private $data_item_ancestors;
    private $detect_hidden;

    public function __construct(
        $section,
        $data_item=NULL,
        $current_is_section=NULL,
        $collapsed=NULL,
        $detect_hidden=NULL) {
        
        $this->detect_hidden = $detect_hidden;

        $this->section = $section;

        // allow tree use without building markup
        if ($data_item !== NULL) {
            
            $this->data_item = $data_item;

            if ($current_is_section) {
                $current_id = $section->id;
                $parent_id = $section->section_id;
                $active_section = $current_id;
            } else {

                $current_id = $data_item->id;
                $parent_id = $data_item->data_item_id;
            }

            $this->section_ancestors = array(0);
            $this->data_item_ancestors = array();
            if (!$current_is_section) {
                if (!$collapsed) {
                    array_push($this->data_item_ancestors, $current_id);
                }
                if (is_numeric($data_item->section_id)) {
                    $active_section = $data_item->section_id;
                } else {
                    $active_section = $this->data_item->findLastSection($parent_id);
                }
            }
            if ($current_id != 0) {
                $this->section_ancestors = $this->section->getAncestorIDs($active_section, $this->section_ancestors);
            }
            if (!$current_is_section) {
                $this->data_item_ancestors = $this->data_item->getAncestorIDs($current_id, $this->data_item_ancestors);
            }
            if (!$collapsed || !$current_is_section) {
                array_push($this->section_ancestors, $active_section);
            }

            $this->data = $this->buildTree(0, 1, $this);
            $this->markup = $this->buildMarkup($this->data, 0, $current_id, 0, 0, $current_is_section);
        }
    }

    public function buildTree($id, $is_section, $tree)
    {
        $nodes = array();
        if ($is_section) {
            $nodes = $tree->section->buildTree($id, $tree);
        } else {
            $nodes = $tree->data_item->buildTree($id, $tree);
        }

        return $nodes;
    }

    private function buildMarkup($data, $level, $current_id, $last_id, $last_node_data, $current_is_section)
    {
        $markup = "\n<ul";

        if ($last_node_data) {
            $ancestors = $this->data_item_ancestors;
        } else {
            $ancestors = $this->section_ancestors;
        }

        if (!in_array($last_id, $ancestors)) {
            $markup .= " class=\"hidden_sections\"";
        }
        if ($level == 0) {
            $markup .= " id=\"tree\"";
            $level++;
        }
        $markup .=">\n";
        foreach ($data as $index => $value) {
            $toggle = '+';
            $folder = 't_folder_closed.gif';
            $url = 'dsection';

            if ($value['data'] == 1) {
                $ancestors = $this->data_item_ancestors;
            } else {
                $ancestors = $this->section_ancestors;
            }

            if (in_array($value['id'], $ancestors)) {
                $toggle = '-';
                $folder = 't_folder_open.gif';
            }
            if ($value['data'] == 1) {
                $folder = 'data.gif';
                $url = 'dsection/data_item';
                $value['label'] = $this->truncate($value['label'], 10); // trunc
            }
            $children = sizeof($value['children']);
            $markup .= "<li ";
            // if current is section
            if (!$value['data']) {
                $markup .= "id=\"section_".$value['id']."\"";    
            } else {
                $markup .= "id=\"data_".$value['id']."\"";    
            }            
            if ($current_id == $value['id'] && $value['data'] != $current_is_section) {
                $markup .= " class=\"current\"";
            }
            $markup .= ">\n";
            if ($children > 0) {
                $markup .= "<a class=\"toggle\" href=\"http://".Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id'];
                if ($toggle == '-') {
                    $markup .= '/?collapsed=1';
                }
                $markup .= "\">$toggle</a>";
            } else {
                $markup .= "<span class=\"toggle\">&nbsp;</span>";
            }
            $markup .= "<img src=\"http://".Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/elib/$folder\" alt=\"\" />\n";
            if ($current_id == $value['id'] && $value['data'] != $current_is_section) {
                $markup .= "<span class=\"label current\">".$value['label']."</span>";
            } else {
                $markup .= "<span class=\"label\"><a href=\"http://".Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id']."\">".$value['label']."</a></span>";
            }
            if ($children > 0) {
                $markup .= $this->buildMarkup(
                    $value['children'],
                    $level,
                    $current_id,
                    $value['id'],
                    $value['data'],
                    $current_is_section,
                    $this->detect_hidden
                );
            }
            $markup .= "<span class=\"clear\"/></li>\n";
        }
        $markup .= "</ul><div class=\"clear\">&nbsp;</div>\n";

        return $markup;
    }


    public function getDataItem()
    {
        return $this->data_item;
    }

    public function getDetectHidden() {
        return $this->detect_hidden;
    }


}
