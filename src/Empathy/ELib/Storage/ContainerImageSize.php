<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;
use Empathy\ELib\Storage\ImageSize;
use Empathy\ELib\Storage\ContainerImageSize as EContainerImageSize;


class ContainerImageSize extends Entity
{
    const TABLE = 'container_image_size';

    public $container_id;
    public $image_size_id;

    public function getImageSizes($container_id)
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
                array_push($sizes, array($row['prefix'], $row['width'], $row['height']));
            }
        }

        return $sizes;
    }

    public function getContainerPrefixes($container_id)
    {
        $prefix = array();
        $params = [];
        $sql = 'SELECT prefix FROM '.Model::getTable(ImageSize::class).' i, '
            .Model::getTable(EContainerImageSize::class).' c WHERE c.image_size_id = i.id'
            .' AND c.container_id = ?';
        $params[] = $container_id;
        $error = 'Could not get image sizes for container.';
        $result = $this->query($sql, $error, $params);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($prefix, $row['prefix'].'_');
            }
        }

        return $prefix;
    }

}
