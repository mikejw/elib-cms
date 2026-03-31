<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\ELib\DSection\SectionsDelete;
use Empathy\ELib\DSection\SectionsTree;
use Empathy\ELib\Storage\SectionItem as ESectionItem;
use Empathy\MVC\DI;
use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class SectionItem extends Entity
{
    public const TABLE = 'section_item';

    public int $id;

    public int|string|object|null $section_id = null;

    public ?string $label = null;

    public ?string $friendly_url = null;

    public ?string $template = null;

    public int|string|null $position = null;

    public int|bool|string|null $hidden = null;

    public int|string|null $stamp = null;

    public ?string $meta = null;

    public int|string|object|null $user_id = null;

    /**
     */
    /**
     * @param array{0: string, 1: list<int|string>} $update
     */
    public function updateTimeStamps(array $update): void
    {
        $sql = 'UPDATE '.Model::getTable(ESectionItem::class)
            .' SET stamp = NOW() WHERE id IN '.$update[0];
        $error = 'Could not update timestamps.';
        $this->query($sql, $error, $update[1]);
    }

    /**
     */
    /**
     * @return list<array<string, scalar|null>>
     */
    public function getContactCountries(int|string $section_id): array
    {
        $country = [];
        $params = [];
        $sql = 'SELECT  d1.label, d3.body FROM '.Model::getTable(DataItem::class).' d1, '
            .Model::getTable(DataItem::class).' d2, '.Model::getTable(DataItem::class).' d3,'
            .' '.Model::getTable(ESectionItem::class).' s WHERE s.id =  ?'
            .' AND d2.section_id = s.id AND d1.data_item_id = d2.id'
            .' AND d3.data_item_id = d1.id'
            .' ORDER BY d1.label';
        $params[] = $section_id;
        $error = 'Could not get counties data.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($country, $row);
            }
        }

        return $country;
    }

    /**
     */
    public function validates(): void
    {
        if ($this->label === '' || ! ctype_alnum(str_replace([' ', '-'], '', $this->label))) {
            $this->addValError('Invalid label');
        }
    }

    /**
     */
    /**
     * @param list<int> $ancestors
     * @return list<int>
     */
    public function getAncestorIDs(int|string $id, array $ancestors): array
    {
        $params = [];
        $section_id = 0;
        $sql = 'SELECT section_id FROM '.Model::getTable(ESectionItem::class).' WHERE id = ?';
        $params[] = $id;
        $error = 'Could not get parent id.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $section_id = (int) $row['section_id'];
        }

        if ($section_id !== 0) {
            array_push($ancestors, $section_id);
            $ancestors = $this->getAncestorIDs($section_id, $ancestors);
        }

        return $ancestors;
    }

    /**
     */
    /**
     * @param list<int|string> $ids
     */
    public function buildDelete(int|string $id, array &$ids, SectionsDelete $tree): void
    {
        array_push($ids, $id);
        $tree->deleteData((int) $id, 1);
        $params = [];
        $sql = 'SELECT id FROM '.Model::getTable(ESectionItem::class).' WHERE section_id = ?';
        $params[] = $id;
        $error = 'Could not find section items for deletion.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $this->buildDelete($row['id'], $ids, $tree);
            }
        }
    }

    /**
     */
    /**
     * @param list<int|string> $params
     */
    public function doDelete(string $idsString, array $params): void
    {
        $sql = 'DELETE FROM '.Model::getTable(ESectionItem::class).' WHERE id IN '.$idsString;
        $error = 'Could not remove section item(s).';
        $this->query($sql, $error, $params);
    }

    /**
     */
    /**
     * @param list<int|string> $order
     * @return array<int, array<string, mixed>>
     */
    public function buildTree(int|string $current, SectionsTree $tree, array $order = [], bool $asc = true): array
    {
        $i = 0;
        $nodes = [];
        $params = [];
        $sql = 'SELECT id,label, position, template, hidden, meta, UNIX_TIMESTAMP(stamp) as stamp FROM '
            .Model::getTable(ESectionItem::class).' WHERE section_id = ?';
        $params[] = $current;

        if ($tree->getDetectHidden()) {
            $sql .= ' and hidden != true';
        }

        $orderParams = [];
        if (count($order)) {
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

        if ($tree->getDataItem() !== null) {

            $params = [];
            $sql = 'SELECT id,label FROM '.Model::getTable(DataItem::class).' WHERE section_id = ?'
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

    /**
     */
    /**
     * @return list<string>
     */
    public function buildURL(int|string $id): array
    {
        $i = 0;
        $build = 1;
        $params = [];
        $url = [];
        while ($build) {
            $sql = 'SELECT section_id, label  FROM '.Model::getTable(ESectionItem::class)
                .' WHERE id = ?';
            $params[] = $id;
            $error = 'Could not build URL.';
            $result = $this->query($sql, $error, $params);
            $row = $result->fetch();

            $url[$i] = (string) $row['label'];

            $id = $row['section_id'];
            if ($id === 0) {
                $build = 0;
            }

            $i++;
        }

        return array_values($url);
    }

    /**
     */
    /**
     * @param list<int> $ignore
     * @return list<array<string, scalar|null>>
     */
    public function getAllForSitemap(array $ignore): array
    {
        $sections = [];
        [$unionSql, $params] = $this->buildUnionString($ignore);
        $sql = 'SELECT *, UNIX_TIMESTAMP(stamp) AS stamp FROM '.Model::getTable(ESectionItem::class)
            .' WHERE id NOT IN '.$unionSql;
        $error = 'Could not get sections for sitemap.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {

                // old bestival code
                $url = ESectionItem::buildURL($row['id']);
                $j = (count($url) - 1);
                $k = 0;

                $full_url = '';
                while ($j >= $k) {
                    $full_url .= str_replace(' ', '', strtolower($url[$j]));
                    if ($j !== $k) {
                        $full_url .= '/';
                    }
                    $j--;
                }
                $row['url'] = $full_url;
                array_push($sections, $row);
            }
        }

        return $sections;
    }

    /**
     * @param list<string> $filter
     */
    public function insert(array $filter = [], bool $includeAutoIdColumn = true): int
    {
        if ($this->user_id === null) {
            $this->user_id = DI::getContainer()->get('CurrentUser')->getUserID();
        }

        return parent::insert($filter, $includeAutoIdColumn);
    }
}
