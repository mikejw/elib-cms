<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\ELib\Storage\ContainerImageSize as EContainerImageSize;
use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class ContainerImageSize extends Entity
{
    public const TABLE = 'container_image_size';

    public int $container_id;

    public int $image_size_id;

    /**
     * @return array<int, mixed>
     */
    public function getImageSizes(int $container_id): array
    {
        $params = [];
        $sizes = [];
        $sql = 'SELECT prefix, width, height FROM '.Model::getTable(ImageSize::class).' i, '
            .Model::getTable(EContainerImageSize::class).' c WHERE c.image_size_id = i.id'
            .' AND c.container_id = ?';
        $params[] = $container_id;
        $error = 'Could not get image sizes for container.';

        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $sizes[] = [$row['prefix'], $row['width'], $row['height']];
            }
        }

        return $sizes;
    }

    /**
     * @return array<int, mixed>
     */
    public function getContainerPrefixes(int $container_id): array
    {
        $prefix = [];
        $params = [];
        $sql = 'SELECT prefix FROM '.Model::getTable(ImageSize::class).' i, '
            .Model::getTable(EContainerImageSize::class).' c WHERE c.image_size_id = i.id'
            .' AND c.container_id = ?';
        $params[] = $container_id;
        $error = 'Could not get image sizes for container.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $prefix[] = $row['prefix'].'_';
            }
        }

        return $prefix;
    }
}
