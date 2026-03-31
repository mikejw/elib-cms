<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\ELib\Storage\DataItem as EDataItem;
use Empathy\MVC\DI;
use Empathy\MVC\Entity;
use Empathy\MVC\Model;
use Michelf\Markdown;

/**
 * @implements \Iterator<int, DataItem>
 */
class DataItem extends Entity implements \Iterator, \JsonSerializable
{
    public const TABLE = 'data_item';

    public const FIND_LABEL = 1;

    public const FIND_BODY = 2;

    public const FIND_IMAGE = 3;

    public const FIND_OPT_UNPACK = 4;

    public const FIND_OPT_CONVERT_MD = 5;

    public const FIND_OPT_MATCH_META = 6;

    public const FIND_HEADING = 7;

    public const FIND_ALL = 8;

    public const FIND_DEEP_ALL = 9;

    public int $id;

    public int|string|null $data_item_id = null;

    public int|string|object|null $section_id = null;

    public int|string|null $container_id = null;

    public ?string $label = null;

    public ?string $heading = null;

    public ?string $body = null;

    public ?string $image = null;

    public int|string|null $image_width = null;

    public int|string|null $image_height = null;

    public ?string $video = null;

    public ?string $audio = null;

    public int|string|object|null $user_id = null;

    public int|string|null $position = null;

    public int|bool|string|null $hidden = null;

    public ?string $meta = null;

    public int|string|null $stamp = null;

    /** @var list<DataItem> */
    private array $data = [];

    private bool $export = false;

    public function __construct()
    {
        parent::__construct();
        $this->data = [];
        $this->export = false;
    }

    /**
     */
    public function setExporting(): void
    {
        $this->export = true;
    }

    /**
     */
    /**
     * @param list<DataItem> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function rewind(): void
    {
        reset($this->data);
    }

    public function current(): mixed
    {
        return current($this->data);
    }

    public function key(): mixed
    {
        return key($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    public function valid(): bool
    {
        $key = key($this->data);
        $var = ($key !== null);

        return $var;
    }

    /**
     */
    public function hasData(): int
    {
        return count($this->data);
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    /**
     */
    public function isContainer(): bool
    {
        $container = false;
        if (! isset($this->heading) &&
            ! isset($this->body) &&
            ! isset($this->image) &&
            ! isset($this->video) &&
            ! isset($this->audio)) {
            $container = true;
        }

        return $container;
    }

    /**
     */
    /**
     * @return list<DataItem>
     */
    public function getDataValue(): array
    {
        return $this->data;
    }

    // data is 'pseudo property'
    /**
     */
    /**
     * @return list<array<string, scalar|null>>
     */
    public function getData(bool $recursive = false, int|string|null $section_id = null, bool $disconnect = true): array
    {
        if (is_numeric($section_id)) {
            $data_set = [];
            $data_set_sections = $this->getSectionData($section_id);
            foreach ($data_set_sections as $d) {
                array_push($data_set, ['id' => $d]);
            }
        } else {
            $data_set = $this->getAllCustom(' where data_item_id = '.$this->id
                .' and hidden != 1 order by position');
        }

        if ($recursive) {
            $i = 0;
            foreach ($data_set as $index => $item) {

                $data = Model::load(EDataItem::class);
                if ($this->export) {
                    $data->setExporting();
                }
                $data->load($item['id']);

                if ($data->isContainer()) {
                    $data->getData(true);
                }
                if ($disconnect) {
                    $data->dbDisconnect();
                }

                if ($this->export) {
                    if ($data->body) {
                        $data->body = preg_replace("!\r?\n!", "\n", $data->body);
                        $data->body = preg_replace('!&nbsp;!', '', $data->body);
                    }
                    if ($data->meta) {
                        $data->meta = preg_replace("!\r?\n!", "\n", $data->meta);
                        $data->meta = preg_replace('!&nbsp;!', '', $data->meta);
                    }
                }

                $this->data[$i] = $data;
                $i++;
            }
        }

        return $data_set;
    }

    /**
     */
    /**
     * @return list<int|string>
     */
    public function getSectionData(int|string $section_id): array
    {
        $ids = [];
        $sql = 'SELECT id FROM '.Model::getTable(EDataItem::class).' WHERE section_id = '.$section_id
            .' and hidden != 1'
            .' ORDER BY position';
        $error = 'Could not get data item id based on section id.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($ids, $row['id']);
            }
        }

        return $ids;
    }

    /**
     */
    /**
     * @return list<DataItem>|null
     */
    public function getSectionDataRecursive(int|string|null $section_id = null, bool $disconnect = true): ?array
    {
        $this->getData(true, $section_id, $disconnect);

        if ($disconnect) {
            $this->dbDisconnect();
        }
        return $this->data;
    }

    /**
     */
    public function convertToMarkdown(): void
    {
        if ($this->body) {
            $this->body = Markdown::defaultTransform($this->body);
        }
    }

    /**
     */
    public function findAndConvertAllToMD(): void
    {
        foreach ($this as $d) {
            if (isset($d->body)) {
                $d->convertToMarkdown();
            }
            $d->findAndConvertAllToMD();
        }
    }

    /**
     */
    /**
     * @param list<DataItem> $found
     */
    public function findContainers(array &$found = [], bool $recursive = true): void
    {
        foreach ($this as $d) {
            if ($d->isContainer()) {
                $found[] = $d;
            }
            if ($recursive) {
                $d->findContainers($found);
            }
        }
    }

    /**
     */
    /**
     * @param list<int> $options
     */
    public function find(int $type, ?string $pattern = null, array $options = []): mixed
    {
        $item = null;
        $lastDataItem = null;
        foreach ($this as $d) {
            $lastDataItem = $d;

            if ($pattern !== null) {
                if (isset($d->label)) {
                    $match_to = $d->label;
                }
                if (isset($d->meta) && in_array(self::FIND_OPT_MATCH_META, $options, true)) {
                    $match_to = $d->meta;
                }
                if (isset($match_to) && ! preg_match($pattern, $match_to)) {
                    continue;
                }
            }

            switch ($type) {
                case self::FIND_LABEL:
                    if (isset($d->label)) {
                        if (
                            in_array(self::FIND_ALL, $options, true) ||
                            in_array(self::FIND_DEEP_ALL, $options, true)
                        ) {
                            if (! is_array($item)) {
                                $item = [];
                            }
                            array_push($item, $d);

                            continue 2;
                        } else {
                            $item = $d;
                        }
                    }
                    break;
                case self::FIND_HEADING:
                    if (isset($d->heading)) {
                        $item = $d;
                    }
                    break;
                case self::FIND_BODY:
                    if (isset($d->body)) {
                        $item = $d;
                    }
                    break;
                case self::FIND_IMAGE:
                    if (isset($d->image)) {
                        $item = $d;
                    }
                    break;
                default:
                    throw new \Exception('No valid find type.');
            }

            if ($item !== null && in_array(self::FIND_OPT_CONVERT_MD, $options, true)) {
                $item->convertToMarkdown();
            }

            if ($item !== null) {
                break;
            } elseif ($d->hasData()) {
                $item = $d->find($type, $pattern, $options);
            }
        }

        if (in_array(self::FIND_DEEP_ALL, $options, true) && $lastDataItem !== null && $lastDataItem->hasData()) {
            $deepItem = $lastDataItem->find($type, $pattern, $options);
            if ($deepItem !== null) {
                $item = array_merge((array) $item, (array) $deepItem);
            }
        }

        if ($item !== null && in_array(self::FIND_OPT_UNPACK, $options, true) && $item->hasData()) {
            if (in_array(self::FIND_OPT_CONVERT_MD, $options, true)) {
                foreach ($item as $d) {
                    if (isset($d->body)) {
                        $d->convertToMarkdown();
                    }
                }
            }

            return $item->getLocalData();
        } else {
            return $item;
        }
    }

    /**
     */
    /**
     * @return list<DataItem>
     */
    public function getLocalData(): array
    {
        return $this->data;
    }

    /**
     */
    public function validates(): void
    {
        if ($this->label === '' || ! ctype_alnum(str_replace(' ', '', $this->label))) {
            $this->addValError('Invalid label');
        }
    }

    /**
     */
    public function findLastSection(int|string $id): int
    {
        $section_id = 0;
        $params = [];
        $sql = 'SELECT id,section_id,data_item_id FROM '.Model::getTable(EDataItem::class).' WHERE id = ?';
        $params[] = $id;
        $error = 'Could not find last section.';

        $result = $this->query($sql, $error, $params);
        $row = $result->fetch();
        if (! is_numeric($row['section_id'])) {
            $section_id = $this->findLastSection($row['data_item_id']);
        } else {
            $section_id = $row['section_id'];
        }

        return $section_id;
    }

    /**
     */
    /**
     * @param list<int|string> $ancestors
     * @return list<int|string>
     */
    public function getAncestorIDs(int|string $id, array $ancestors): array
    {
        $data_item_id = 0;
        $params = [];
        $sql = 'SELECT data_item_id FROM '.Model::getTable(EDataItem::class).' WHERE id = ?';
        $params[] = $id;
        $error = 'Could not get parent id.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $data_item_id = $row['data_item_id'];
        }
        if ($data_item_id !== 0) {
            array_push($ancestors, $data_item_id);
            $ancestors = $this->getAncestorIDs($data_item_id, $ancestors);
        }

        return $ancestors;
    }

    /**
     */
    /**
     * @param list<int|string> $ids
     */
    public function buildDelete(int|string $id, array &$ids, int|bool $section_start): void
    {
        $params = [];
        if ($section_start) {
            $sql = 'SELECT id FROM '.Model::getTable(EDataItem::class).' WHERE section_id = ?';
            $params[] = $id;
        } else {
            $sql = 'SELECT id FROM '.Model::getTable(EDataItem::class).' WHERE data_item_id = ?';
            $params[] = $id;
            array_push($ids, $id);
        }
        $error = 'Could not find data items for deletion.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $this->buildDelete($row['id'], $ids, 0);
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
        $sql = 'DELETE FROM '.Model::getTable(EDataItem::class).' WHERE id IN '.$idsString;
        $error = 'Could not remove data item(s).';
        $this->query($sql, $error, $params);
    }

    /**
     */
    /**
     * @param list<int|string> $order
     * @return array<int, array<string, mixed>>
     */
    public function buildTree(int|string $current, object $tree, array $order = [], bool $asc = true): array
    {
        $i = 0;
        $nodes = [];
        $params = [];
        $sql = 'SELECT id,label FROM '.Model::getTable(EDataItem::class).' WHERE data_item_id = ?';
        $params[] = $current;

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

        return $nodes;
    }

    /**
     */
    /**
     * @param list<int|string> $params
     * @return list<string>
     */
    public function getImageFilenames(string $sql, array $params): array
    {
        $images = [];
        $sql = 'SELECT image FROM '.Model::getTable(EDataItem::class).' WHERE image IS NOT NULL'
            .' AND id IN'.$sql;
        $error = 'Could not get matching data item image filenames.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($images, $row['image']);
            }
        }

        return $images;
    }

    /**
     */
    /**
     * @param list<int|string> $params
     * @return list<string>
     */
    public function getVideoFilenames(string $sql, array $params): array
    {
        $videos = [];
        $sql = 'SELECT video FROM '.Model::getTable(EDataItem::class).' WHERE video IS NOT NULL'
            .' AND id IN'.$sql;
        $error = 'Could not get matching data item video filenames.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($videos, $row['video']);
            }
        }

        return $videos;
    }

    /**
     */
    /**
     * @param list<int|string> $params
     * @return list<string>
     */
    public function getAudioFilenames(string $sql, array $params): array
    {
        $audioFiles = [];
        $sql = 'SELECT audio FROM '.Model::getTable(EDataItem::class).' WHERE audio IS NOT NULL'
            .' AND id IN'.$sql;
        $error = 'Could not get matching data item audio filenames.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($audioFiles, $row['audio']);
            }
        }

        return $audioFiles;
    }

    /**
     */
    public function getMostRecentVideoID(): int
    // ammended to perform search by position value
    {
        $id = 0;
        $row = ['id' => 0];
        $sql = 'SELECT id FROM '.Model::getTable(EDataItem::class).
            ' WHERE video IS NOT NULL ORDER BY position LIMIT 0,1';
        $error = 'Could not get most recent video.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
        }
        $id = $row['id'];

        return $id;
    }

    /**
     * @param array<int, mixed> $filter
     */
    public function insert(array $filter = [], bool $includeAutoIdColumn = true): int
    {
        if ($this->user_id === null) {
            $this->user_id = DI::getContainer()->get('CurrentUser')->getUserID();
        }

        if ($this->stamp === null) {
            $this->stamp = 'MYSQLTIME';
        }

        return parent::insert($filter, $includeAutoIdColumn);
    }
}
