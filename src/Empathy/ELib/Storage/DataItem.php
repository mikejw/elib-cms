<?php

namespace Empathy\ELib\Storage;

use Empathy\ELib\Model;
use Empathy\MVC\Entity;
use Empathy\ELib\User\CurrentUser;

define('PERLBIN', '/opt/local/bin/perl');
define('MD', '/opt/local/bin/Markdown.pl');


class DataItem extends Entity
{
    const TABLE = 'data_item';

    const FIND_LABEL = 1;
    const FIND_BODY = 2;
    const FIND_IMAGE = 3;
    const FIND_OPT_UNPACK = 4;
    const FIND_OPT_CONVERT_MD = 5;
    const FIND_OPT_MATCH_META = 6;


    public $id;
    public $data_item_id;
    public $section_id;
    public $container_id;
    public $label;
    public $heading;
    public $body;
    public $image;
    public $video;
    public $user_id;
    public $position;
    public $hidden;
    public $meta;
    public $stamp;

    public function isContainer()
    {
        $container = false;
        if(!isset($this->heading) &&
           !isset($this->body) &&
           !isset($this->image) &&
           !isset($this->video))
        {
            $container = true;
        }

        return $container;
    }


    // data is 'pseudo property'
    public function getData($recursive=false, $section_id=null, $disconnect=true)
    {
        if (is_numeric($section_id)) {
            $data_set = array();
            $data_set_sections = $this->getSectionData($section_id);
            foreach ($data_set_sections as $d) {
                array_push($data_set, array('id' => $d)); 
            }
        } else {
            $data_set = $this->getAll(self::TABLE. ' where data_item_id = '.$this->id
                .' and hidden != 1');
        }

        if ($recursive) {
            foreach ($data_set as $index => $item) {

                

                $data = Model::load('DataItem');
                $data->id = $item['id'];
                $data->load();                

                $props = $data->getProperties();
                foreach ($props as $p) {
                    if ($data->$p === null || $data->$p == '0') {
                        unset($data->$p);
                    }
                }
                if ($data->isContainer()) {
                    $data->getData(true);
                }
                if ($disconnect) {                    
                    $data->dbDisconnect();
                }            
                $this->data[$data->id] = $data; 
            }
        }
        return $data_set;
    }


    public function getSectionData($section_id)
    {
        $ids = array();
        $sql = 'SELECT id FROM '.Model::getTable('DataItem').' WHERE section_id = '.$section_id
            .' and hidden != 1'
            .' ORDER BY label';
        $error = 'Could not get data item id based on section id.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($ids, $row['id']);
            }
        }

        return $ids;
    }

    public function getSectionDataRecursive($section_id=null, $disconnect=true)
    {
        $this->getData(true, $section_id, $disconnect);
        return $this->data;
    }


    public function convertToMarkdown()
    {
        $output = array();
        $tmp_file = DOC_ROOT.'/tmp/content.md';
        if (!is_writable($tmp_file)) {
            throw new \Exception('Could not write to md cache file.');
        }
        file_put_contents($tmp_file, $this->body);
        exec(PERLBIN.' '.MD.' '.$tmp_file, $output);
        $this->body = implode("\n", $output);
    }

    public function find($data, $type, $pattern = NULL, $options = array())
    {
        $item = null;
        foreach ($data as $d) {

            if ($pattern !== NULL) {
                if (isset($d->label)) {
                    $match_to = $d->label;   
                }                
                if (isset($d->meta) && in_array(self::FIND_OPT_MATCH_META, $options)) {
                    $match_to = $d->meta;
                }
                if (isset($match_to) && !preg_match($pattern, $match_to)) {
                    continue;
                }                
            }

            switch ($type) {
                case self::FIND_LABEL:                  
                    if (isset($d->label)) {
                        $item = $d;
                    }
                    break;
                case self::FIND_BODY:
                    if (isset($d->body)) {
                        $item = $d;
                        if (in_array(self::FIND_OPT_CONVERT_MD, $options)) {
                            $item->convertToMarkdown();
                        }
                    }
                    break;
                case self::FIND_IMAGE:
                    if (isset($d->image)) {
                        $item = $d;
                    }
                    break;                    
                default:
                    break;
            }
            if ($item !== null) {
                break;
            }
        }
        if ($item === null && isset($data->data)) {
                $item = $this->find($data->data, $type, $pattern, $options);
        }
        if (in_array(self::FIND_OPT_UNPACK, $options) && isset($item->data)) {
            if (in_array(self::FIND_OPT_CONVERT_MD, $options)) {
                foreach ($item->data as $d) {
                    if (isset($d->body)) {
                        $d->convertToMarkdown();
                    }
                }
            }
            return $item->data;
        } else {
            return $item;
        }
    }





    public function validates()
    {
        if ($this->label == '' || !ctype_alnum(str_replace(' ', '', $this->label))) {
            $this->addValError('Invalid label');
        }
    }

    public function findLastSection($id)
    {
        $section_id = 0;
        $sql = 'SELECT id,section_id,data_item_id FROM '.Model::getTable('DataItem').' WHERE id = '.$id;
        $error = 'Could not find last section.';

        $result = $this->query($sql, $error);
        $row = $result->fetch();
        if (!is_numeric($row['section_id'])) {
            $section_id = $this->findLastSection($row['data_item_id']);
        } else {
            $section_id = $row['section_id'];
        }

        return $section_id;
    }

    public function getAncestorIDs($id, $ancestors)
    {
        $data_item_id = 0;
        $sql = 'SELECT data_item_id FROM '.Model::getTable('DataItem').' WHERE id = '.$id;
        $error = 'Could not get parent id.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $data_item_id = $row['data_item_id'];
        }
        if ($data_item_id != 0) {
            array_push($ancestors, $data_item_id);
            $ancestors = $this->getAncestorIDs($data_item_id, $ancestors);
        }

        return $ancestors;
    }

    public function buildDelete($id, &$ids, $section_start)
    {
        if ($section_start) {
            $sql = 'SELECT id FROM '.Model::getTable('DataItem').' WHERE section_id = '.$id;
        } else {
            $sql = 'SELECT id FROM '.Model::getTable('DataItem').' WHERE data_item_id = '.$id;
            array_push($ids, $id);
        }
        $error = 'Could not find data items for deletion.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $this->buildDelete($row['id'], $ids, 0);
            }
        }
    }

    public function doDelete($ids)
    {
        $sql = 'DELETE FROM '.Model::getTable('DataItem').' WHERE id IN'.$ids;
        $error = 'Could not remove data item(s).';
        $this->query($sql, $error);
    }

    public function buildTree($current, $tree)
    {
        $i = 0;
        $nodes = array();
        $sql = 'SELECT id,label FROM '.Model::getTable('DataItem').' WHERE data_item_id = '.$current
            .' ORDER BY id';
        $error = 'Could not get child data items.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];
                $nodes[$i]['id'] = $id;
                $nodes[$i]['data'] = 1;
                $nodes[$i]['label'] = $row['label'];
                $nodes[$i]['children'] = $tree->buildTree($id, 0, $tree);
                $i++;
            }
        }

        return $nodes;
    }

    public function getImageFilenames($ids)
    {
        $images = array();
        $sql = 'SELECT image FROM '.Model::getTable('DataItem').' WHERE image IS NOT NULL'
            .' AND id IN'.$ids;
        $error = 'Could not get matching data item image filenames.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($images, $row['image']);
            }
        }

        return $images;
    }

    public function getVideoFilenames($ids)
    {
        $videos = array();
        $sql = 'SELECT video FROM '.Model::getTable('DataItem').' WHERE video IS NOT NULL'
            .' AND id IN'.$ids;
        $error = 'Could not get matching data item video filenames.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($videos, $row['video']);
            }
        }

        return $videos;
    }

    public function getMostRecentVideoID()
    // ammended to perform search by position value
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable('DataItem').' WHERE video IS NOT NULL ORDER BY position LIMIT 0,1';
        $error = 'Could not get most recent video.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
        }
        $id = $row['id'];

        return $id;
    }


    public function insert($table, $id, $format, $sanitize, $force_id=false)
    {
        $this->user_id = CurrentUser::getUserID();
        parent::insert($table, $id, $format, $sanitize, $force_id);
    }




}