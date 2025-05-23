<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;
use Empathy\ELib\Storage\Container as EContainer;
use Empathy\ELib\Storage\ContainerImageSize;

class Container extends Entity
{
    const TABLE = 'container';

    public $id;
    public $name;
    public $description;

    public function getAll()
    {
        $container = [];
        $sql = 'SELECT'
            .' c.id AS container_id, i.id AS image_size_id, '
            .' i.name AS image_size_name, c.name AS container_name'
            .' FROM '.Model::getTable(EContainer::class).' c'
            .' LEFT JOIN '.Model::getTable(ContainerImageSize::class).' ci'
            .' ON ci.container_id = c.id'
            .' LEFT JOIN '.Model::getTable(ImageSize::class).' i'
            .' ON i.id = ci.image_size_id';
        $error = 'Could not get containers.';
        $result = $this->query($sql, $error);

        $last_id = 0;
        foreach ($result as $row) {
            $id = $row['container_id'];
            if ($last_id != $id) {
                $container[$id]['name'] = $row['container_name'];
            }

            if (is_numeric($row['image_size_id'])) {
                $image_size_id = $row['image_size_id'];
                $container[$id]['image_sizes'][$image_size_id] = $row['image_size_name'];
            }

            $last_id = $id;
        }

        foreach ($container as $index => $item) {
            if (isset($container[$index]['image_sizes'])) {
                $container[$index]['image_size_ids'] =
                    array_keys($container[$index]['image_sizes']);
            }
        }

        return $container;
    }

    public function remove()
    {
        $sql = 'DELETE FROM '.Model::getTable(ContainerImageSize::class)
            .' WHERE container_id = '.$this->id;
        $this->delete();
    }

    public function validates()
    {
        if ($this->name == '' || !ctype_alnum(str_replace(' ', '', $this->name))) {
            $this->addValError('Invalid container name');
        }
    }

    public function update($id, $new_sizes)
    {
        $params = [];
        $sql = 'DELETE FROM '.Model::getTable(ContainerImageSize::class)
            .' WHERE container_id = ?';
        $params[] = $id;
        $error = 'Could not clear old image sizes from container.';
        $this->query($sql, $error, $params);
        foreach ($new_sizes as $index => $size_id) {
            $params = [];
            $sql = 'INSERT INTO '.Model::getTable(ContainerImageSize::class)
                .' VALUES(?, ?)';
            $params[] = $id;
            $params[] = $size_id;
            $error = 'Could not inert new image size';
            $this->query($sql, $error, $params);
        }
    }
}
