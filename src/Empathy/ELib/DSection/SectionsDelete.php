<?php

namespace Empathy\ELib\DSection;

use Empathy\ELib\DSection,
//use Empathy\Model\SectionItem as SectionItem;
    Empathy\ELib\File\Image as ImageUpload;

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
            $ids_string = '('.implode(',', $ids).')';
            $images = $this->data_item->getImageFilenames($ids_string);
            $videos = $this->data_item->getVideoFilenames($ids_string);

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

            if (sizeof($images) < 1 || $images_removed) {
                $this->data_item->doDelete($ids_string);
            }
        }
    }

    public function delete($id)
    {
        $ids = array();
        $this->section->buildDelete($id, $ids, $this);
        if (sizeof($ids) > 0) {
            $ids_string = '('.implode(',', $ids).')';
            $this->section->doDelete($ids_string);
        }

    }

}
