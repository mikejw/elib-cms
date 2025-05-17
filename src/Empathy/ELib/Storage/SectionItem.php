<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Model;
use Empathy\MVC\Entity;
use Empathy\MVC\DI;

class SectionItem extends Entity
{
    const TABLE = 'section_item';

    public $id;
    public $section_id;
    public $label;
    public $friendly_url;
    public $template;
    public $position;
    public $hidden;
    public $stamp;
    public $meta;
    public $user_id;

    public function updateTimeStamps($update)
    {
        $sql = 'UPDATE '.Model::getTable('SectionItem')
            .' SET stamp = NOW() WHERE id IN'.$update;
        $error = 'Could not update timestamps.';
        $this->query($sql, $error);
    }

    public function getContactCountries($section_id)
    {
        $country = array();
        $sql = 'SELECT  d1.label, d3.body FROM '.Model::getTable('DataItem').' d1, '.Model::getTable('DataItem').' d2, '.Model::getTable('DataItem').' d3,'
            .' '.Model::getTable('SectionItem').' s WHERE s.id =  '.$section_id
            .' AND d2.section_id = s.id AND d1.data_item_id = d2.id'
            .' AND d3.data_item_id = d1.id'
            .' ORDER BY d1.label';
        $error = 'Could not get counties data.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($country, $row);
            }
        }

        return $country;
    }

    public function validates()
    {
        if ($this->label == '' || !ctype_alnum(str_replace(array(' ', '-'), '', $this->label))) {
           $this->addValError('Invalid label');
        }
    }

    public function getAncestorIDs($id, $ancestors)
    {
        $section_id = 0;
        $sql = 'SELECT section_id FROM '.Model::getTable('SectionItem').' WHERE id = '.$id;
        $error = 'Could not get parent id.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $section_id = (int) $row['section_id'];
        }

        if ($section_id != 0) {
            array_push($ancestors, $section_id);
            $ancestors = $this->getAncestorIDs($section_id, $ancestors);
        }

        return $ancestors;
    }

    public function buildDelete($id, &$ids, $tree)
    {
        array_push($ids, $id);
        $tree->deleteData($id, 1);

        $sql = 'SELECT id FROM '.Model::getTable('SectionItem').' WHERE section_id = '.$id;
        $error = 'Could not find section items for deletion.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $this->buildDelete($row['id'], $ids, $tree);
            }
        }
    }

    public function doDelete($ids)
    {
        $sql = 'DELETE FROM '.Model::getTable('SectionItem').' WHERE id IN'.$ids;
        $error = 'Could not remove section item(s).';
        $this->query($sql, $error);
    }

    public function buildTree($current, $tree, $order = [], $asc = true)
    {
        $i = 0;
        $nodes = [];
        $params = [];
        $sql = 'SELECT id,label, position, template, hidden, meta, UNIX_TIMESTAMP(stamp) as stamp FROM '
            .Model::getTable('SectionItem').' WHERE section_id = ?';
        $params[] = $current;

        if ($tree->getDetectHidden()) {
            $sql .= ' and hidden != true';
        }

        $orderParams = [];
        if (sizeof($order)) {
            foreach ($order as $key => $value) {
                $orderParams[] = '?';
                $params[] = $value;
            }
            $orderBy = implode(',', $orderParams);

        } else {
            $orderBy = 'position';
        }
        $sql .= " order by $orderBy";

        $sql .= $asc ? ' ASC' : ' DESC';

        $error = 'Could not get child sections.';

        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];
                $nodes[$i]['id'] = $id;
                $nodes[$i]['data'] = 0;
                $nodes[$i]['hidden'] = $row['hidden'];
                $nodes[$i]['label'] = $row['label'];
                $nodes[$i]['meta'] = $row['meta'];
                $nodes[$i]['stamp'] = $row['stamp'];
                $nodes[$i]['template'] = $row['template'];
                $nodes[$i]['position'] = $row['position'];
                $nodes[$i]['children'] = $tree->buildTree($id, 1, $tree, $order, $asc);
                $i++;
            }
        }

        if ($tree->getDataItem() !== NULL) {

            $params = [];
            $sql = 'SELECT id,label FROM '.Model::getTable('DataItem').' WHERE section_id = ?'
                .' order by position';
            $params[] = $current;
            $error = 'Could not get child data items.';
            $result = $this->query($sql, $error, $params);
            if ($result->rowCount() > 0) {
                foreach ($result as $row) {
                    $id = $row['id'];
                    $nodes[$i]['id'] = $id;
                    $nodes[$i]['data'] = 1;
                    $nodes[$i]['label'] = $row['label'];
                    $nodes[$i]['children'] = $tree->buildTree($id, 0, $tree, $order, $asc);
                    $i++;
                }
            }
        }

        return $nodes;
    }

    public function buildURL($id)
    {
        $i = 0;
        $build = 1;
        while ($build) {
            $sql = "SELECT section_id, label  FROM ".Model::getTable('SectionItem')
                ." WHERE id = $id";
            $error = "Could not build URL.";
            $result = $this->query($sql, $error);
            $row = $result->fetch();

            $url[$i] = $row['label'];

            $id = $row['section_id'];
            if ($id == 0) {
                $build = 0;
            }

            $i++;
        }

        return $url;
    }

    public function getAllForSitemap($ignore)
    {
        $sections = array();
        $sql = 'SELECT *, UNIX_TIMESTAMP(stamp) AS stamp FROM '.Model::getTable('SectionItem')
            .' WHERE id NOT IN'.$this->buildUnionString($ignore);
        $error = 'Could not get sections for sitemap.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {

                // old bestival code
                $url = SectionItem::buildURL($row['id']);
                $j = (sizeof($url) - 1);
                $k = 0;

                $full_url = "";
                while ($j >= $k) {
                    $full_url .= str_replace(" ", "", strtolower($url[$j]));
                    if ($j != $k) {
                        $full_url .= "/";
                    }
                    $j--;
                }
                $row['url'] = $full_url;
                array_push($sections, $row);
            }
        }

        return $sections;
    }

    public function insert($filter = [], $id = true)
    {
        if ($this->user_id === null) {
            $this->user_id = DI::getContainer()->get('CurrentUser')->getUserID();
        }
        return parent::insert($filter, $id);
    }
}
