<?php

namespace Empathy\ELib\DSection;

use Empathy\ELib\DSection;
use Empathy\ELib\File\Image as ImageUpload;
use Empathy\ELib\File\Upload as AudioUpload;

class SectionsDelete
{
    private $section;
    private $data_item;

    public function __construct($section, $data_item, $current_is_section)
    {
        $this->section = $section;
        $this->data_item = $data_item;
        if ($current_is_section) {
            $this->delete($this->section->id);
        } else {
            $this->deleteData($this->data_item->id, 0);
        }
    }

    public function deleteData($id, $section_start)
    {
        $ids = array();
        $this->data_item->buildDelete($id, $ids, $section_start);
        if (sizeof($ids) > 0) {
            $queryParams = [];
            foreach ($ids as $id) {
                $queryParams[] = '?';
            }
            $ids_string = '('.implode(',', $queryParams).')';
            $images = $this->data_item->getImageFilenames($ids_string, $ids);
            $videos = $this->data_item->getVideoFilenames($ids_string, $ids);
            $audioFiles = $this->data_item->getAudioFilenames($ids_string, $ids);

            $all_files = array();
            if (sizeof($videos) > 0) {
                // take care of video thumbnails
                $all_videos = array();
                foreach ($videos as $video) {
                    array_push($all_videos, $video);
                    array_push($all_videos, $video.'.jpg');
                }
                $all_files = array_merge($all_videos, $images);
            } else {
                $all_files = $images;
            }

            $images_removed = false;
            if (sizeof($all_files) > 0) {
                $u = new ImageUpload('data', false, array());
                $images_removed = $u->remove($all_files);
            }

            $audioFiles_removed = false;
            if (sizeof($audioFiles) > 0) {
                $au = new AudioUpload(false);
                $audioFiles_removed = $au->remove($audioFiles);
            }

            if (
                (sizeof($images) < 1 || $images_removed) ||
                (sizeof($audioFiles) < 1 || $audioFiles_removed)
            ){
                $this->data_item->doDelete($ids_string, $ids);
            }
        }
    }

    public function delete($id)
    {
        $ids = array();
        $this->section->buildDelete($id, $ids, $this);
        if (sizeof($ids) > 0) {
            $params = [];
            foreach ($ids as $id) {
                $params[] = '?';
            }
            $ids_string = '('.implode(',', $params).')';
            $this->section->doDelete($ids_string, $ids);
        }
    }
}
