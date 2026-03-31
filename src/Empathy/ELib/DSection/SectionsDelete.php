<?php

declare(strict_types=1);

namespace Empathy\ELib\DSection;

use Empathy\ELib\File\Image as ImageUpload;
use Empathy\ELib\File\Upload as AudioUpload;
use Empathy\ELib\Storage\DataItem;
use Empathy\ELib\Storage\SectionItem;

class SectionsDelete
{
    private SectionItem $section;

    private DataItem $data_item;

    public function __construct(SectionItem $section, DataItem $data_item, bool $current_is_section)
    {
        $this->section = $section;
        $this->data_item = $data_item;
        if ($current_is_section) {
            $this->delete($this->section->id);
        } else {
            $this->deleteData($this->data_item->id, 0);
        }
    }

    public function deleteData(int $id, int $section_start): void
    {
        $ids = [];
        $this->data_item->buildDelete($id, $ids, $section_start);
        if (count($ids) > 0) {
            $queryParams = [];
            foreach ($ids as $id) {
                $queryParams[] = '?';
            }
            $ids_string = '('.implode(',', $queryParams).')';
            $images = $this->data_item->getImageFilenames($ids_string, $ids);
            $videos = $this->data_item->getVideoFilenames($ids_string, $ids);
            $audioFiles = $this->data_item->getAudioFilenames($ids_string, $ids);

            $all_files = [];
            if (count($videos) > 0) {
                // take care of video thumbnails
                $all_videos = [];
                foreach ($videos as $video) {
                    array_push($all_videos, $video);
                    array_push($all_videos, $video.'.jpg');
                }
                $all_files = array_merge($all_videos, $images);
            } else {
                $all_files = $images;
            }

            $images_removed = false;
            if (count($all_files) > 0) {
                $u = new ImageUpload('data', false, []);
                $images_removed = $u->remove($all_files);
            }

            $audioFiles_removed = false;
            if (count($audioFiles) > 0) {
                $au = new AudioUpload(false);
                $audioFiles_removed = $au->remove($audioFiles);
            }

            if (
                (count($images) < 1 || $images_removed) ||
                (count($audioFiles) < 1 || $audioFiles_removed)
            ) {
                $this->data_item->doDelete($ids_string, $ids);
            }
        }
    }

    public function delete(int $id): void
    {
        $ids = [];
        $this->section->buildDelete($id, $ids, $this);
        if (count($ids) > 0) {
            $params = [];
            foreach ($ids as $id) {
                $params[] = '?';
            }
            $ids_string = '('.implode(',', $params).')';
            $this->section->doDelete($ids_string, $ids);
        }
    }
}
