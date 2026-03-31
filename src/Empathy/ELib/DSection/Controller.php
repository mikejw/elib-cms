<?php

declare(strict_types=1);

namespace Empathy\ELib\DSection;

use Empathy\ELib\AdminController;
use Empathy\ELib\File\Image as ImageUpload;
use Empathy\ELib\File\Upload as AudioUpload;
use Empathy\ELib\Storage\Container;
use Empathy\ELib\Storage\ContainerImageSize;
use Empathy\ELib\Storage\DataItem;
use Empathy\ELib\Storage\ImageSize;
use Empathy\ELib\Storage\SectionItem;
use Empathy\ELib\Storage\VideoUpload;
use Empathy\MVC\Model;
use Empathy\MVC\RequestException;

class Controller extends AdminController
{
    private function normalizeInt(mixed $value): int
    {
        if (is_object($value) && isset($value->id)) {
            return (int) $value->id;
        }

        return (int) ($value ?? 0);
    }

    /**
     */
    private function clearCache(): void
    {
        $cache = $this->stash->get('cache');
        if (is_object($cache) && method_exists($cache, 'clear')) {
            $cache->clear();
        }
    }

    // functions that are similar to those in data_item
    /**
     */
    /**
     * @return list<string>
     */
    public function getDataTypes(): array
    {
        return ['Heading', 'Body', 'Image', 'Audio', 'Video', 'Container'];
    }

    /**
     */
    public function add_data(): void
    {
        $this->buildNav();
        $this->presenter->assign('add_data_menu', 1);

        if (isset($_GET['data_type']) && is_numeric($_GET['data_type'])
            && isset($_GET['add'])) {
            switch ($_GET['data_type']) {
                case 0:
                    $this->redirect('admin/dsection/add_data_heading/'.$_GET['id']);
                    break;
                case 1:
                    $this->redirect('admin/dsection/add_data_body/'.$_GET['id']);
                    break;
                case 2:
                    $this->redirect('admin/dsection/add_data_image/'.$_GET['id']);
                    break;
                case 3:
                    $this->redirect('admin/dsection/add_data_audio/'.$_GET['id']);
                    break;
                case 4:
                    $this->redirect('admin/dsection/add_data_video/'.$_GET['id']);
                    break;
                case 5:
                    $this->addDataContainer();
                    break;
                default:
                    $this->redirect('admin/dsection/'.$_GET['id']);
                    break;
            }
        } elseif (isset($_GET['cancel'])) {
            $this->redirect('admin/dsection/'.$_GET['id']);
        } else {
            $s = Model::load(SectionItem::class);
            $s->load($_GET['id']);
            $this->presenter->assign('section_item', $s);
            $this->presenter->assign('section_item_id', $s->id);
            $this->presenter->assign('data_types', $this->getDataTypes());

            $c = Model::load(Container::class);
            $containers = $c->getAllCustom('');
            $containers_arr = [];
            $containers_arr[0] = 'Default';
            foreach ($containers as $item) {
                $id = $item['id'];
                $containers_arr[$id] = $item['name'];
            }
            $this->presenter->assign('container_types', $containers_arr);
        }
    }

    /**
     */
    public function addDataContainer(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (isset($_GET['container_type']) && is_numeric($_GET['container_type'])) {
            $d = Model::load(DataItem::class);
            $d->section_id = $_GET['id'];
            if ($_GET['container_type'] > 0) {
                $d->container_id = (int) $_GET['container_type'];
            }
            $d->label = 'Container';
            $d->position = 'DEFAULT';
            $d->hidden = 'DEFAULT';
            $su = Model::load(SectionItem::class);
            $u = new SectionsUpdate($su, $d->section_id);
            $id = $d->insert();

            $this->clearCache();
        }
        $this->redirect('admin/dsection/data_item/'.$id);
    }

    /**
     */
    public function add_data_heading(): void
    {
        if (isset($_POST['save'])) {
            $d = Model::load(DataItem::class);
            $d->label = 'Heading';
            $d->section_id = $_GET['id'];
            $d->heading = $_POST['heading'];
            $d->position = 'DEFAULT';
            $d->hidden = 'DEFAULT';
            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $su = Model::load(SectionItem::class);
                $u = new SectionsUpdate($su, $d->section_id);
                $d->insert();
                $this->clearCache();
                $this->redirect('admin/dsection/'.$this->normalizeInt($d->section_id));
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        }

        $this->buildNav();
        $this->presenter->assign('section_id', $_GET['id']);
    }

    /**
     */
    public function add_data_body(): void
    {
        if (isset($_POST['save'])) {
            $d = Model::load(DataItem::class);
            $d->label = 'Body';
            $d->section_id = $_GET['id'];
            $d->body = $_POST['body'];
            $d->position = 'DEFAULT';
            $d->hidden = 'DEFAULT';
            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $su = Model::load(SectionItem::class);
                $u = new SectionsUpdate($su, $d->section_id);
                $d->insert();
                $this->clearCache();
                $this->redirect('admin/dsection/'.$this->normalizeInt($d->section_id));
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        }

        $this->buildNav();
        $this->presenter->assign('section_id', $_GET['id']);
    }

    /**
     */
    public function add_data_image(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (isset($_POST['save'])) {

            $_GET['id'] = $_POST['id'];

            $images = [];
            if (! is_array($_FILES['file']['name'])) {

                $images[0] = $_FILES['file'];
            } else {
                $images = ImageUpload::reArrayFiles($_FILES['file']);
            }

            $success = 0;
            foreach ($images as $img) {
                $_FILES['file'] = $img;
                $u = new ImageUpload('data', true, []);

                if ($u->error !== '') {
                    $this->presenter->assign('error', $u->error);
                } else {
                    $d = Model::load(DataItem::class);
                    $d->label = $u->getFileEncoded();
                    $d->section_id = $_GET['id'];
                    $d->image = $u->getFile();
                    $d->image_width = $u->getDimensions()[0];
                    $d->image_height = $u->getDimensions()[1];
                    $d->position = 'DEFAULT';
                    $d->hidden = 'DEFAULT';
                    $su = Model::load(SectionItem::class);
                    $u = new SectionsUpdate($su, $d->section_id);
                    $id = $d->insert();
                    $this->clearCache();
                    $success++;
                }
            }
            if ($success === count($images)) {
                $this->redirect('admin/dsection/data_item/'.$id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        }

        $this->buildNav();
        $this->presenter->assign('section_id', $_GET['id']);
    }

    /**
     */
    public function add_data_video(): void
    {
        if (isset($_GET['iframe']) && $_GET['iframe'] === true) {
            $this->setTemplate('elib:admin/video_upload.tpl');
        } else {
            $this->setTemplate('elib:admin/section.tpl');
        }

        if (isset($_POST['id'])) {
            echo 1;
            /** @var VideoUpload $v */
            $v = Model::load(VideoUpload::class);
            $v->upload();

            if ($v->error === '') {
                $v->make_flv();
            }

            if ($v->error !== '') {
                $this->presenter->assign('error', $v->error);
            } else {
                $d = Model::load(DataItem::class);
                $d->label = $v->file;
                $d->data_item_id = $_GET['id'];
                $d->image = 'DEFAULT';
                $d->video = $v->file;
                $d->position = 'DEFAULT';
                $d->hidden = 'DEFAULT';
                $d->insert();
                $this->update_timestamps($d->data_item_id);
                $v->generateThumb();
                $this->clearCache();
                // $this->redirect('admin/data_item/'.mysql_insert_id());
            }
        }
        $this->buildNav();
        $this->presenter->assign('section_id', $_GET['id']);
    }

    /**
     */
    public function add_data_audio(): void
    {
        if (isset($_POST['save'])) {
            $_GET['id'] = $_POST['id'];

            $u = new AudioUpload();
            if ($u->error !== '') {
                $this->presenter->assign('error', $u->error);
            } else {
                $d = Model::load(DataItem::class);
                $d->label = $u->getFileNameEncoded();
                $d->section_id = $_GET['id'];
                $d->audio = $u->getFile();
                $d->position = 'DEFAULT';
                $d->hidden = 'DEFAULT';
                $su = Model::load(SectionItem::class);
                $u = new SectionsUpdate($su, $d->section_id);
                $id = $d->insert();
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        }
        $this->buildNav();
        $this->presenter->assign('section_id', $_GET['id']);
    }

    public function default_event(): void
    {
        $ui_array = ['id'];
        $this->loadUIVars('ui_section', $ui_array);
        if (! isset($_GET['id']) || $_GET['id'] === '') {
            $_GET['id'] = 0;
        }

        $this->buildNav();
        $this->presenter->assign('section_id', $_GET['id']);

    }

    /**
     */
    public function assertID(): void
    {
        if (! isset($_GET['id']) || ! is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
    }

    /**
     */
    public function buildNav(): void
    {
        $this->setTemplate('elib:admin/section.tpl');
        $this->assertID();
        $s = Model::load(SectionItem::class);
        $d = Model::load(DataItem::class);

        if (isset($_GET['collapsed']) && $_GET['collapsed'] === 1) {
            $collapsed = 1;
        } else {
            $collapsed = 0;
        }

        if (! $s->load($_GET['id']) && $_GET['id'] !== 0) {
            throw new RequestException('Section item not found.');
        }
        $st = new SectionsTree($s, $d, true, (bool) $collapsed);
        $this->presenter->assign('sections', $st->getMarkup());
        $this->presenter->assign('section', $s);
    }

    /**
     */
    public function add_section(): void
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $s = Model::load(SectionItem::class);
            $s->section_id = (int) $_GET['id'];
            $s->label = 'New Section';
            $s->template = 'DEFAULT';
            $s->position = 'DEFAULT';
            $s->hidden = 'DEFAULT';
            $s->stamp = 'MYSQLTIME';
            $s->insert();
            $this->clearCache();
        }
        $this->redirect('admin/dsection/'.$_GET['id']);
    }

    /**
     */
    public function delete(): void
    {
        $this->assertID();
        $s = Model::load(SectionItem::class);
        $d = Model::load(DataItem::class);
        $s->load($_GET['id']);
        $sd = new SectionsDelete($s, $d, true);
        $this->clearCache();
        $this->redirect('admin/dsection/'.$this->normalizeInt($s->section_id));
    }

    /**
     */
    public function rename(): void
    {
        $this->buildNav();
        if (isset($_POST['save'])) {
            $s = Model::load(SectionItem::class);
            $s->load($_POST['id']);
            $s->label = $_POST['label'];
            $s->validates();
            if ($s->hasValErrors()) {
                $this->presenter->assign('section', $s);
                $this->presenter->assign('errors', $s->getValErrors());
            } else {
                $s->save();
                $su = Model::load(SectionItem::class);
                $u = new SectionsUpdate($su, $s->id);
                $this->clearCache();
                $this->redirect('admin/dsection/'.$s->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        } else {
            $s = Model::load(SectionItem::class);
            $s->load($_GET['id']);
            $this->presenter->assign('section', $s);
        }
    }

    /**
     */
    public function change_template(): void
    {
        $this->buildNav();
        if (isset($_POST['save'])) {
            $s = Model::load(SectionItem::class);
            $s->load($_POST['id']);
            $s->template = $_POST['template'];
            $s->validates();
            if ($s->hasValErrors()) {
                $this->presenter->assign('section', $s);
                $this->presenter->assign('errors', $s->getValErrors());
            } else {
                $s->save();
                $su = Model::load(SectionItem::class);
                $u = new SectionsUpdate($su, $s->id);
                $this->clearCache();
                $this->redirect('admin/dsection/'.$s->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        } else {
            $s = Model::load(SectionItem::class);
            $s->load($_GET['id']);

            $t = [
                '0' => '0',
                'A' => 'A',
                'B' => 'B',
                'C' => 'C',
                'D' => 'D',
                'E' => 'E',
                'F' => 'F',
                'G' => 'G',
                'H' => 'H',
                'I' => 'I',
                'J' => 'J',
                'K' => 'K',
                'L' => 'L',
                'M' => 'M',
                'N' => 'N',
                'O' => 'O',
                'P' => 'P',
                'Q' => 'Q',
                'R' => 'R',
                'S' => 'S',
                'T' => 'T',
                'U' => 'U',
                'V' => 'V',
                'W' => 'W',
                'X' => 'X',
                'Y' => 'Y',
                'Z' => 'Z',
            ];

            $this->presenter->assign('templates', $t);
            $this->presenter->assign('section', $s);
        }
    }

    /**
     */
    public function toggle_hidden(): void
    {
        $s = Model::load(SectionItem::class);
        $s->load($_GET['id']);
        $s->hidden = ($s->hidden) ? 0 : 1;
        $s->save();
        $this->clearCache();
        $this->redirect('admin/dsection/'.$s->id);
    }

    // data item stuff
    /**
     */
    public function buildNavData(): DataItem
    {
        //    $this->setTemplate('admin/data_item.tpl');
        $this->assertID();
        $s = Model::load(SectionItem::class);
        $d = Model::load(DataItem::class);

        if (! $d->load($_GET['id'])) {
            throw new RequestException('Data item not found.');
        }
        $is_section = 0;
        if (isset($_GET['collapsed']) && $_GET['collapsed'] === 1) {
            $collapsed = 1;
        } else {
            $collapsed = 0;
        }

        $st = new SectionsTree($s, $d, false, (bool) $collapsed);
        $this->presenter->assign('sections', $st->getMarkup());
        $this->presenter->assign('data_item', $d);
        $this->presenter->assign('is_container', $d->isContainer());

        return $d;
    }

    /**
     */
    public function data_item(): void
    {
        $ui_array = ['id'];
        $this->loadUIVars('ui_data_item', $ui_array);
        if (! isset($_GET['id']) || $_GET['id'] === '') {
            $_GET['id'] = 0;
        }

        $d = $this->buildNavData();

        $imagePrefix = 'mid';
        if ($d->image !== '') {
            $parentId = $d->data_item_id;
            if (isset($parentId)) {
                $parent = Model::load(DataItem::class);
                $parent->load($parentId);
                if ($parent->isContainer() && isset($parent->container_id)) {
                    $c = Model::load(ContainerImageSize::class);
                    $imageSizes = $c->getImageSizes((int) $parent->container_id);
                    if (count($imageSizes) > 0) {
                        $imagePrefix = $imageSizes[0][0];
                    }
                }
            }
        }

        $this->assign('image_prefix', $imagePrefix);
        $this->presenter->assign('data_item_id', $_GET['id']);
        $this->setTemplate('elib:admin/section.tpl');
    }

    /**
     */
    public function delete_data_item(): void
    {
        $this->assertID();
        $this->setTemplate('elib:admin/section.tpl');
        $s = Model::load(SectionItem::class);
        $d = Model::load(DataItem::class);
        $d->load($_GET['id']);
        $this->update_timestamps($d->id);
        $sd = new SectionsDelete($s, $d, false);
        $this->clearCache();

        if (! is_numeric($d->data_item_id)) {
            $this->redirect('admin/dsection/'.$this->normalizeInt($d->section_id));
        } else {
            $this->redirect('admin/dsection/data_item/'.$d->data_item_id);
        }
    }

    /**
     */
    public function update_timestamps(int|string|null $id): void
    {
        $id = $this->normalizeInt($id);
        $d = Model::load(DataItem::class);
        $ancestors = [];
        $ancestors = $d->getAncestorIDs($id, $ancestors);

        if (count($ancestors) > 0) {
            $d->id = (int) min($ancestors);
        } else {
            $d->id = $id;
        }
        $d->load($d->id);
        $u = new SectionsUpdate(Model::load(SectionItem::class), $this->normalizeInt($d->section_id));
    }

    /**
     */
    public function rename_data_item(): void
    {
        if (isset($_POST['save'])) {
            $d = Model::load(DataItem::class);
            $d->load($_POST['id']);
            $d->label = $_POST['label'];
            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $d->save();
                $this->update_timestamps($d->id);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$d->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_POST['id']);
        }

        $this->buildNavData();
        $this->setTemplate('elib:admin/section.tpl');
        $this->assign('class', 'data_item');
        $d = Model::load(DataItem::class);
        $d->load($_GET['id']);
        $this->assign('data_item', $d);
        $this->assign('data_item_id', $d->id);
    }

    /**
     */
    public function edit_data_item_meta(): void
    {
        $this->assign('event', 'edit_meta');
        if (isset($_POST['save'])) {
            $d = Model::load(DataItem::class);
            $d->load($_POST['id']);
            $d->meta = $_POST['meta'];

            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $d->save();
                $this->update_timestamps($d->id);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$d->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_POST['id']);
        }

        $this->buildNavData();
        $this->setTemplate('elib:admin/section.tpl');
        $this->assign('class', 'data_item');
        $d = Model::load(DataItem::class);
        $d->load($_GET['id']);
        $this->assign('data_item', $d);
        $this->assign('data_item_id', $d->id);
    }

    /**
     */
    public function edit_section_item_meta(): void
    {
        if (isset($_POST['save'])) {
            $s = Model::load(SectionItem::class);
            $s->load($_POST['id']);
            $s->meta = $_POST['meta'];

            $s->validates();
            if ($s->hasValErrors()) {
                $this->presenter->assign('section_item', $s);
                $this->presenter->assign('errors', $s->getValErrors());
            } else {
                $s->save();
                $this->clearCache();
                $this->redirect('admin/dsection/'.$s->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        }

        $this->buildNav();
        $s = Model::load(SectionItem::class);
        $s->load($_GET['id']);
        $this->assign('section_item', $s);
        $this->assign('section_id', $s->id);
    }

    /**
     */
    public function data_item_toggle_hidden(): void
    {
        $d = Model::load(DataItem::class);
        $d->load($_GET['id']);
        $d->hidden = ($d->hidden) ? 0 : 1;
        $d->save();
        $this->clearCache();
        $this->redirect('admin/dsection/data_item/'.$d->id);
    }

    /**
     */
    public function data_add_data(): void
    {
        $this->buildNavData();
        $this->setTemplate('elib:admin/section.tpl');

        if (isset($_GET['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        } elseif (isset($_GET['data_type']) && is_numeric($_GET['data_type'])) {
            switch ($_GET['data_type']) {
                case 0:
                    $this->redirect('admin/dsection/data_add_data_heading/'.$_GET['id']);
                    break;
                case 1:
                    $this->redirect('admin/dsection/data_add_data_body/'.$_GET['id']);
                    break;
                case 2:
                    $this->redirect('admin/dsection/data_add_data_image/'.$_GET['id']);
                    break;
                case 3:
                    $this->redirect('admin/dsection/data_add_data_audio/'.$_GET['id']);
                    break;
                case 4:
                    $this->redirect('admin/dsection/data_add_data_video/'.$_GET['id']);
                    break;
                case 5:
                    $this->dataAddDataContainer();
                    break;
                default:
                    $this->redirect('admin/dsection/data_item/'.$_GET['id']);
                    break;
            }
        } else {
            $d = Model::load(DataItem::class);
            $d->load($_GET['id']);
            $this->presenter->assign('class', 'data_item');
            $this->presenter->assign('data_item', $d);
            $this->presenter->assign('data_item_id', $d->id);
            // $this->presenter->assign('add_data_menu', 1);
            $this->presenter->assign('data_types', $this->getDataTypes());

            $c = Model::load(Container::class);
            $containers = $c->getAllCustom('');
            $containers_arr = [];
            $containers_arr[0] = 'Default';
            foreach ($containers as $item) {
                $id = $item['id'];
                $containers_arr[$id] = $item['name'];
            }
            $this->presenter->assign('container_types', $containers_arr);
        }
    }

    /**
     */
    public function data_add_data_body(): void
    {
        if (isset($_POST['save'])) {
            $d = Model::load(DataItem::class);
            $d->label = 'Body';
            $d->data_item_id = $_GET['id'];
            $d->body = $_POST['body'];
            $d->position = 'DEFAULT';
            $d->hidden = 'DEFAULT';
            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $this->update_timestamps($d->data_item_id);
                $d->insert();
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$_GET['id']);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        }

        $this->buildNavData();
        $this->setTemplate('elib:admin/section.tpl');
        $this->presenter->assign('data_item_id', $_GET['id']);
        $this->presenter->assign('class', 'data_item');
    }

    /**
     */
    public function data_add_data_heading(): void
    {
        if (isset($_POST['save'])) {
            $d = Model::load(DataItem::class);
            $d->label = 'Heading';
            $d->data_item_id = $_GET['id'];
            $d->heading = $_POST['heading'];
            $d->position = 'DEFAULT';
            $d->hidden = 'DEFAULT';
            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $d->insert();
                $this->update_timestamps($d->data_item_id);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$d->data_item_id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        }
        $this->buildNavData();
        $this->setTemplate('elib:admin/section.tpl');
        $this->presenter->assign('data_item_id', $_GET['id']);
        $this->presenter->assign('class', 'data_item');
    }

    /**
     */
    public function data_add_data_image(): void
    {
        if (isset($_POST['save'])) {

            $_GET['id'] = $_POST['id'];

            $p = Model::load(DataItem::class);
            $p->load($_GET['id']);

            if (is_numeric($p->container_id)) {
                $c = Model::load(ContainerImageSize::class);
                $sizes = $c->getImageSizes((int) $p->container_id);
                $sizes = array_map(
                    static fn (array $size): array => [(string) $size[0], (int) $size[1], (int) $size[2]],
                    $sizes
                );
            } else {
                $sizes = [];
            }

            $images = [];
            if (! is_array($_FILES['file']['name'])) {

                $images[0] = $_FILES['file'];
            } else {
                $images = ImageUpload::reArrayFiles($_FILES['file']);
            }

            $new_id = null;
            $success = 0;
            foreach ($images as $img) {
                $_FILES['file'] = $img;

                $u = new ImageUpload('data', true, array_values($sizes));

                if ($u->error !== '') {
                    $this->presenter->assign('error', $u->error);
                } else {
                    $d = Model::load(DataItem::class);
                    $d->label = $u->getFileEncoded();
                    $d->data_item_id = $_GET['id'];
                    $d->image = $u->getFile();
                    $d->image_width = $u->getDimensions()[0];
                    $d->image_height = $u->getDimensions()[1];
                    $d->position = 'DEFAULT';
                    $d->hidden = 'DEFAULT';
                    $id = $d->insert();
                    if ($new_id === null) {
                        $new_id = $id;
                    }
                    $success++;
                    // $this->update_timestamps($d->data_item_id);
                    // $this->clearCache();
                }
            }
            if ($success === count($images)) {
                $this->redirect('admin/dsection/data_item/'.$new_id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        }
        $this->buildNavData();
        $this->assign('class', 'data_item');
        $this->setTemplate('elib:admin/section.tpl');
        $this->assign('data_item_id', $_GET['id']);
    }

    /**
     */
    public function data_add_data_audio(): void
    {
        if (isset($_POST['save'])) {
            $_GET['id'] = $_POST['id'];

            $u = new AudioUpload();
            if ($u->error !== '') {
                $this->presenter->assign('error', $u->error);
            } else {
                $d = Model::load(DataItem::class);
                $d->label = $u->getFileNameEncoded();
                $d->data_item_id = $_GET['id'];
                $d->audio = $u->getFile();
                $d->position = 'DEFAULT';
                $d->hidden = 'DEFAULT';
                $id = $d->insert();
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_POST['id']);
        }
        $this->buildNavData();
        $this->assign('data_item_id', $_GET['id']);
        $this->assign('class', 'data_item');
        $this->setTemplate('elib:admin/section.tpl');
    }

    /**
     */
    public function data_add_data_video(): void
    {
        if (isset($_GET['iframe']) && $_GET['iframe'] === true) {
            $this->setTemplate('elib:admin/video_upload.tpl');
        } else {
            $this->setTemplate('elib:admin/section.tpl');
        }

        if (isset($_POST['id'])) {
            echo 1;
            /** @var VideoUpload $v */
            $v = Model::load(VideoUpload::class);
            $v->upload();

            if ($v->error === '') {
                $v->make_flv();
            }

            if ($v->error !== '') {
                $this->presenter->assign('error', $v->error);
            } else {
                $d = Model::load(DataItem::class);
                $d->label = $v->file;
                $d->data_item_id = $_GET['id'];
                $d->image = 'DEFAULT';
                $d->video = $v->file;
                $d->position = 'DEFAULT';
                $d->hidden = 'DEFAULT';
                $d->insert();
                $this->update_timestamps($d->data_item_id);
                $v->generateThumb();
                $this->clearCache();
                // $this->redirect('admin/data_item/'.mysql_insert_id());
            }
        }
        $this->buildNavData();
        $this->assign('data_item_id', $_GET['id']);
        $this->assign('class', 'data_item');
        $this->setTemplate('elib:admin/section.tpl');
    }

    /**
     */
    public function dataAddDataContainer(): void
    {
        if (isset($_GET['container_type']) && is_numeric($_GET['container_type'])) {
            $d = Model::load(DataItem::class);
            $d->data_item_id = $_GET['id'];
            if ($_GET['container_type'] > 0) {
                $d->container_id = (int) $_GET['container_type'];
            }
            $d->label = 'Container';
            $d->position = 'DEFAULT';
            $d->hidden = 'DEFAULT';
            $this->update_timestamps($d->data_item_id);
            $id = $d->insert();
            $this->clearCache();
            $this->redirect('admin/dsection/data_item/'.$id);
        }
    }

    /**
     */
    public function edit_heading(): void
    {
        if (isset($_POST['save'])) {
            $d = Model::load(DataItem::class);
            $d->load($_POST['id']);
            $d->heading = $_POST['heading'];
            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $this->update_timestamps($d->id);
                $d->save();
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$_GET['id']);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        }

        $this->buildNavData();
        $this->setTemplate('elib:admin/section.tpl');
        $d = Model::load(DataItem::class);
        $d->load($_GET['id']);
        $this->presenter->assign('data_item', $d);
    }

    /**
     */
    public function edit_body(): void
    {
        if (isset($_POST['save'])) {
            $d = Model::load(DataItem::class);
            $d->load($_POST['id']);
            $d->body = $_POST['body'];
            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $d->save();
                $this->update_timestamps($d->id);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$_GET['id']);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        }

        $this->buildNavData();
        $this->setTemplate('elib:admin/section.tpl');
        $this->assign('class', 'data_item');
        $d = Model::load(DataItem::class);
        $d->load($_GET['id']);
        $this->presenter->assign('data_item', $d);
        $this->assign('data_item_id', $d->id);
    }

    /**
     */
    public function edit_body_raw(): void
    {
        $this->presenter->assign('event', 'edit_body');
        $this->presenter->assign('raw_mode', true);
        $this->edit_body();
    }

    // containers
    /**
     */
    public function add_container(): void
    {
        $c = Model::load(Container::class);
        $c->name = '#New Container';
        $c->insert();
        $this->clearCache();
        $this->redirect('admin/dsection/containers');
    }

    /**
     */
    public function containers(): void
    {
        if (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection');
        } elseif (isset($_POST['save'])) {
            foreach ($_POST['image_size'] as $index => $value) {
                $c = Model::load(Container::class);
                $c->update($index, $value);
            }
            $this->clearCache();
            $this->redirect('admin/dsection');
        }

        $this->setTemplate('elib:admin/containers.tpl');
        $c = Model::load(Container::class);
        $containers = $c->getAll();
        $this->assign('containers', $containers);
        $i = Model::load(ImageSize::class);
        $image_sizes = $i->loadAsOptions('name');
        $this->presenter->assign('image_sizes', $image_sizes);
    }

    /**
     */
    public function remove_container(): void
    {
        $c = Model::load(Container::class);
        $c->id = $_GET['id'];
        $c->remove();
        $this->clearCache();
        $this->redirect('admin/dsection/containers');
    }

    /**
     */
    public function rename_container(): void
    {
        $this->setTemplate('elib:admin/containers.tpl');
        if (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/containers');
        } elseif (isset($_POST['save'])) {
            $c = Model::load(Container::class);
            $c->load($_GET['id']);
            $c->name = $_POST['name'];
            $c->validates();
            if (! $c->hasValErrors()) {
                $c->save();
                $this->clearCache();
                $this->redirect('admin/dsection/containers');
            } else {
                $this->assign('container', $c);
                $this->presenter->assign('errors', $c->getValErrors());
            }
        } else {
            $c = Model::load(Container::class);
            $c->load($_GET['id']);
            $this->assign('container', $c);
        }
    }

    // image sizes
    /**
     */
    public function add_image_size(): void
    {
        $i = Model::load(ImageSize::class);
        $i->name = 'New Image Size';
        $i->width = 0;
        $i->height = 0;
        $i->prefix = 'new';
        $i->insert();
        $this->clearCache();
        $this->redirect('admin/dsection/image_sizes');
    }

    /**
     */
    public function image_sizes(): void
    {
        if ($this->isXMLHttpRequest()) {
            $return_code = 1;
            if (isset($_POST['id']) && is_numeric($_POST['id'])) {
                $i = Model::load(ImageSize::class);
                $i->load($_POST['id']);
                $field = $_POST['field'];
                $i->$field = $_POST['value'];
                $i->validates();
                if ($i->hasValErrors()) {
                    // $this->logMe($i->getValErrors());
                    $return_code = 2;
                } else {
                    $i->save();
                    $return_code = 0;
                }
            }
            header('Content-type: application/json');
            echo json_encode($return_code);
            exit();
        }

        if (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection');
        } elseif (isset($_POST['save'])) {
            foreach ($_POST['image_size'] as $index => $value) {
                $c = Model::load(Container::class);
                $c->update($index, $value);
            }
            $this->clearCache();
            $this->redirect('admin/dsection');
        }

        $this->setTemplate('elib:admin/image_sizes.tpl');

        $i = Model::load(ImageSize::class);
        $sql = ' ORDER BY name';
        $image_sizes = $i->getAllCustom($sql);

        $this->presenter->assign('image_sizes', $image_sizes);
    }

    /**
     */
    public function remove_image_size(): void
    {
        $i = Model::load(ImageSize::class);
        $i->id = $_GET['id'];
        $i->delete();
        $this->clearCache();
        $this->redirect('admin/dsection/image_sizes');
    }

    /*
      public function rename_image_size()
      {
      if (isset($_POST['cancel'])) {
      $this->redirect('admin/dsection/containers');
      } elseif (isset($_POST['save'])) {
      $c = Model::load(Container::class);
      $c->id = $_GET['id'];
      $c->load();
      $c->name = $_POST['name'];
      $c->validates();
      if (!$c->hasValErrors()) {
      $c->save();
      $this->redirect('admin/containers');
      } else {
      $this->assign('container', $c);
      $this->presenter->assign('errors', $c->getValErrors());
      }
      } else {
      $c = Model::load(Container::class);
      $c->id = $_GET['id'];
      $c->load();
      $this->assign('container', $c);
      }
      }
    */

    /**
     */
    public function update_image_sizes(): void
    {
        $i = Model::load(ImageSize::class);
        $i->load($_GET['id']);
        $images = $i->getDataFiles();

        $d = [[$i->prefix.'_', (int) $i->width, (int) $i->height]];
        $u = new ImageUpload('', false, $d);
        set_time_limit(300);
        $u->resize($images);
        $this->clearCache();
        $this->redirect('admin/dsection/image_sizes');
    }

    /**
     */
    public function sort(): bool
    {
        $position = 1;
        foreach ($_POST as $type => $value) {

            if ($type === 'section') {
                $model = SectionItem::class;
            } else {
                $model = DataItem::class;
            }

            foreach ($value as $id) {
                $object = Model::load($model);
                $object->load($id);
                $object->position = $position;
                $object->save();
                $position++;
            }
        }

        header('Content-type: application/json');
        echo json_encode(1);

        return false;
    }

    /**
     */
    public function export_section(): void
    {
        $this->buildNav();
        $output = '';
        $target_id = $_GET['id'];

        if (isset($_POST['submit'])) {
            $ie = new ImportExport();
            $output = $ie->export($_POST['target_id']);
            $target_id = $_POST['target_id'];
        }

        $this->assign('target_id', $target_id);
        $this->assign('output', $output);
    }

    /**
     */
    public function import_section(): void
    {
        $this->buildNav();
        $this->assertID();
        $content = '';
        $parent_id = $_GET['id'];

        if (isset($_POST['submit'])) {
            $parent_id = $_POST['parent_id'];
            $ie = new ImportExport();
            $content = $_POST['content'];
            $ie->import($parent_id, $content);
            $this->redirect('admin/dsection');
        }

        $this->assign('parent_id', $parent_id);
        $this->assign('content', $content);
    }

    /**
     */
    public function export_container(): void
    {
        $this->buildNavData();
        $this->setTemplate('elib:admin/section.tpl');
        $output = '';
        $target_id = $_GET['id'];

        if (isset($_POST['submit'])) {
            $ie = new ImportExport();
            $output = $ie->exportContainer($_POST['target_id']);
            $target_id = $_POST['target_id'];
        }

        $this->assign('target_id', $target_id);
        $this->assign('output', $output);
    }

    /**
     */
    public function import_container(): void
    {

        $topLevelSection = false;
        if (isset($_GET['section']) && $_GET['section']) {
            $topLevelSection = true;
            $this->buildNav();
        } else {
            $this->buildNavData();
        }

        $this->setTemplate('elib:admin/section.tpl');
        $this->assertID();
        $content = '';
        $parent_id = $_GET['id'];

        if (isset($_POST['submit'])) {
            $parent_id = $_POST['parent_id'];
            $ie = new ImportExport();
            $content = $_POST['content'];
            $ie->importContainer($parent_id, $content, $topLevelSection);
            $this->redirect('admin/dsection');
        }

        $this->assign('parent_id', $parent_id);
        $this->assign('content', $content);
    }
}
