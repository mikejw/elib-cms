<?php

declare(strict_types=1);

namespace Empathy\ELib\DSection;

use Empathy\ELib\Storage\DataItem;
use Empathy\ELib\Storage\SectionItem;
use Empathy\ELib\Tree;
use Empathy\MVC\Config;

class SectionsTree extends Tree
{
    private function normalizeInt(mixed $value): int
    {
        if (is_object($value) && isset($value->id)) {
            return (int) $value->id;
        }

        return (int) ($value ?? 0);
    }

    private SectionItem $section;

    private ?DataItem $data_item = null;

    /** @var array<int, array<string, mixed>> */
    private array $data = [];

    /** @var list<int|string> */
    private array $section_ancestors = [];

    /** @var list<int|string> */
    private array $data_item_ancestors = [];

    private ?bool $detect_hidden = null;

    /**
     * @param list<int|string> $order
     */
    public function __construct(
        SectionItem $section,
        ?DataItem $data_item = null,
        ?bool $current_is_section = null,
        ?bool $collapsed = null,
        ?bool $detect_hidden = null,
        array $order = [],
        bool $asc = true
    ) {

        $this->detect_hidden = $detect_hidden;

        $this->section = $section;

        // allow tree use without building markup
        if ($data_item !== null) {

            $this->data_item = $data_item;

            if ($current_is_section) {
                $current_id = $section->id;
                $parent_id = $section->section_id;
                $active_section = $current_id;
            } else {

                $current_id = $data_item->id;
                $parent_id = $data_item->data_item_id;
            }

            $this->section_ancestors = [0];
            $this->data_item_ancestors = [];
            if (! $current_is_section) {
                if (! $collapsed) {
                    array_push($this->data_item_ancestors, $current_id);
                }
                if (is_numeric($data_item->section_id)) {
                    $active_section = $data_item->section_id;
                } else {
                    $active_section = $this->data_item->findLastSection($this->normalizeInt($parent_id));
                }
            }
            if ($current_id !== 0) {
                $this->section_ancestors = $this->section->getAncestorIDs($active_section, $this->section_ancestors);
            }
            if (! $current_is_section) {
                $this->data_item_ancestors = $this->data_item->getAncestorIDs($current_id, $this->data_item_ancestors);
            }
            if (! $collapsed || ! $current_is_section) {
                array_push($this->section_ancestors, $active_section);
            }

            $this->data = $this->buildTree(0, 1, $this, $order, $asc);
            $this->markup = $this->buildMarkup($this->data, 0, $current_id, 0, 0, $current_is_section);
        }
    }

    /**
     * @param array<int, mixed> $order
     * @return array<int, mixed>
     */
    /**
     * @param list<int|string> $order
     * @return array<int, array<string, mixed>>
     */
    public function buildTree(int $id, int $is_section, self $tree, array $order, bool $asc): array
    {
        $nodes = [];
        if ($is_section) {
            $nodes = $tree->section->buildTree($id, $tree, $order, $asc);
        } else {
            $nodes = $tree->data_item->buildTree($id, $tree, $order, $asc);
        }

        return $nodes;
    }

    /**
     * @param array<int, mixed> $data
     */
    private function buildMarkup(array $data, int $level, int $current_id, int $last_id, int $last_node_data, bool $current_is_section): string
    {
        $markup = "\n<ul";

        if ($last_node_data) {
            $ancestors = $this->data_item_ancestors;
        } else {
            $ancestors = $this->section_ancestors;
        }

        $class = 'clearfix';
        if (! in_array($last_id, $ancestors, true)) {
            $class .= ' hidden_sections';
        }
        $markup .= " class=\"$class\"";

        if ($level === 0) {
            $markup .= ' id="tree"';
            $level++;
        }
        $markup .= ">\n";
        foreach ($data as $index => $value) {

            $toggle = '+';
            $folder = '<i class="far fa-folder"></i>';
            $url = 'dsection';

            if ($value['data'] === 1) {
                $ancestors = $this->data_item_ancestors;
            } else {
                $ancestors = $this->section_ancestors;
            }

            if (in_array($value['id'], $ancestors, true)) {
                $toggle = '-';
                $folder = '<i class="far fa-folder-open"></i>';
            }
            if ($value['data'] === 1) {
                $folder = '<i class="far fa-file"></i>';
                $url = 'dsection/data_item';
                $value['label'] = $this->truncate($value['label'], 10); // trunc
            }
            $children = count($value['children']);
            $class = 'clearfix';
            $markup .= '<li ';
            // if current is section
            if (! $value['data']) {
                $markup .= 'id="section_'.$value['id'].'"';
            } else {
                $markup .= 'id="data_'.$value['id'].'"';
            }
            if ($current_id === $value['id'] && $value['data'] !== $current_is_section) {
                $class .= ' current';
            }

            if (isset($value['hidden']) && $value['hidden']) {
                $class .= ' hidden';
            }

            $markup .= " class=\"$class\"";

            $markup .= ">\n";
            if ($children > 0) {
                $markup .= '<a class="toggle" href="http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id'];
                if ($toggle === '-') {
                    $markup .= '/?collapsed=1';
                }
                $markup .= "\">$toggle</a>";
            } else {
                $markup .= '<span class="toggle">&nbsp;</span>';
            }
            $markup .= $folder;
            if ($current_id === $value['id'] && $value['data'] !== $current_is_section) {
                $markup .= '<span class="label current">'.$value['label'].'</span>';
            } else {
                $markup .= '<span class="label"><a href="http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id'].'">'.$value['label'].'</a></span>';
            }
            if ($children > 0) {
                $markup .= $this->buildMarkup(
                    $value['children'],
                    $level,
                    $current_id,
                    $value['id'],
                    $value['data'],
                    $current_is_section
                );
            }
            $markup .= "</li>\n";
        }
        $markup .= "</ul>\n";

        return $markup;
    }

    public function getDataItem(): ?DataItem
    {
        return $this->data_item;
    }

    public function getDetectHidden(): ?bool
    {
        return $this->detect_hidden;
    }
}
