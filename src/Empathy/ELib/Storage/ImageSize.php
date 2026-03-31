<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class ImageSize extends Entity
{
    public const TABLE = 'image_size';

    public int $id;

    public ?string $name = null;

    public ?string $prefix = null;

    public int|string|null $width = null;

    public int|string|null $height = null;

    public function validates(): void
    {
        if (! ctype_alnum(str_replace(' ', '', $this->name))) {
            $this->addValError('Invalid name');
        }
        if (! ctype_alpha($this->prefix)) {
            $this->addValError('Invalid prefix');
        }
        if (! is_numeric($this->width)) {
            $this->addValError('Invalid width');
        }
        if (! is_numeric($this->height)) {
            $this->addValError('Invalid height');
        }
    }

    /**
     * @return array<int, mixed>
     */
    public function getDataFiles(): array
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

        if (count($ids) > 0) {
            [$unionString, $params] = $this->buildUnionString($ids);
            $sql = 'SELECT image FROM '.Model::getTable(DataItem::class).' WHERE data_item_id IN '.$unionString;
            $error = 'Could not got images matching image size.';
            $result = $this->query($sql, $error, $params);
            foreach ($result as $row) {
                $images[] = $row['image'];
            }
        }

        return $images;
    }
}
