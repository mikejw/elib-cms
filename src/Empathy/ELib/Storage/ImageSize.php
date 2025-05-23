<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;
use Empathy\ELib\Storage\DataItem;
use Empathy\ELib\Storage\ContainerImageSize;


class ImageSize extends Entity
{
    const TABLE = 'image_size';

    public $id;
    public $name;
    public $prefix;
    public $width;
    public $height;

    public function validates()
    {
        if (!ctype_alnum(str_replace(' ', '', $this->name))) {
            $this->addValError('Invalid name');
        }
        if (!ctype_alpha($this->prefix)) {
            $this->addValError('Invalid prefix');
        }
        if (!is_numeric($this->width)) {
            $this->addValError('Invalid width');
        }
        if (!is_numeric($this->height)) {
            $this->addValError('Invalid height');
        }
    }

    public function getDataFiles()
    {
        $images = [];
        $ids = [];
        $params = [];
        $sql = 'SELECT id from '.Model::getTable(DataItem::class).' d,'
            .Model::getTable(ContainerImageSize::class)
            .' c WHERE c.image_size_id = ?'
            .' AND c.container_id = d.container_id';
        $params[] = $this->id;
        $error = 'Could not get data item containers that are using selected image size.';
        $result = $this->query($sql, $error, $params);
        foreach ($result as $row) {
            $ids[] = $row['id'];
        }

        if (sizeof($ids) > 0) {
            list($unionString, $params) = $this->buildUnionString($ids);
            $sql = 'SELECT image FROM '.Model::getTable(DataItem::class).' WHERE data_item_id IN '. $unionString;
            $error = 'Could not got images matching image size.';
            $result = $this->query($sql, $error, $params);
            foreach ($result as $row) {
                $images[] = $row['image'];
            }
        }

        return $images;
    }
}
