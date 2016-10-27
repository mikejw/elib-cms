<?php

namespace Empathy\ELib\DSection;

use Empathy\ELib\AdminController,
//use Empathy\ELib\File\GImage as ImageUpload;
    Empathy\ELib\File\Image as ImageUpload,
    Empathy\ELib\DSection\SectionsUpdate,
    Empathy\ELib\DSection\SectionsDelete,
    Empathy\ELib\DSection\SectionsTree,
    Empathy\ELib\Model;


class Controller extends AdminController
{

    private function clearCache()
    {        
        $cache = $this->stash->get('cache');
        if (is_object($cache)) {
            $cache->clear();
        }
    }


    // functions that are similar to those in data_item
    public function getDataTypes()
    {
        return array('Heading', 'Body', 'Image', 'Video', 'Container');
    }

    public function add_data()
    {
        $this->buildNav();
        $this->presenter->assign('add_data_menu', 1);

        if(isset($_GET['data_type']) && is_numeric($_GET['data_type'])
           && isset($_GET['add']))
        {
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
                $this->redirect('admin/dsection/add_data_video/'.$_GET['id']);
                break;
            case 4:
                $this->addDataContainer();
                break;
            default:
                $this->redirect('admin/dsection/'.$_GET['id']);
                break;
            }
        } elseif (isset($_GET['cancel'])) {
            $this->redirect('admin/dsection/'.$_GET['id']);
        } else {
            $s = Model::load('SectionItem');
            $s->id = $_GET['id'];
            $s->load();
            $this->presenter->assign('section_item', $s);
            $this->presenter->assign('section_item_id', $s->id);
            $this->presenter->assign('data_types', $this->getDataTypes());

            $c = Model::load('Container');
            $containers = $c->getAllCustom('', Model::getTable('Container'));
            $containers_arr = array();
            $containers_arr[0] = 'Default';
            foreach ($containers as $item) {
                $id = $item['id'];
                $containers_arr[$id] = $item['name'];
            }
            $this->presenter->assign('container_types', $containers_arr);
            //	$this->presenter->assign('class', 'section');
        }        
    }

    public function addDataContainer()
    {
        if (isset($_GET['container_type']) && is_numeric($_GET['container_type'])) {
            $d = Model::load('DataItem');
            $d->section_id = $_GET['id'];
            if ($_GET['container_type'] > 0) {
                $d->container_id = $_GET['container_type'];
            }
            $d->label = 'Container';
            $d->position = 'DEFAULT';
            $d->hidden = 'DEFAULT';
            $su = Model::load('SectionItem');
            $u = new SectionsUpdate($su, $d->section_id);
            $id = $d->insert(Model::getTable('DataItem'), 1, array(), 0);

            $this->clearCache();
        }
        $this->redirect('admin/dsection/data_item/'.$id);
    }

    public function add_data_heading()
    {
        if (isset($_POST['save'])) {
            $d = Model::load('DataItem');
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
                $su = Model::load('SectionItem');
                $u = new SectionsUpdate($su, $d->section_id);
                $d->insert(Model::getTable('DataItem'), 1, array(), 1);
                $this->clearCache();
                $this->redirect('admin/dsection/'.$d->section_id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        }

        $this->buildNav();
        $this->presenter->assign('section_id', $_GET['id']);
    }

    public function add_data_body()
    {
        if (isset($_POST['save'])) {
            $d = Model::load('DataItem');
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
                $su = Model::load('SectionItem');
                $u = new SectionsUpdate($su, $d->section_id);
                $d->insert(Model::getTable('DataItem'), 1, array(), 1);
                $this->clearCache();
                $this->redirect('admin/dsection/'.$d->section_id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        }

        $this->buildNav();
        $this->presenter->assign('section_id', $_GET['id']);
    }

    public function add_data_image()
    {
        if (isset($_POST['save'])) {

            $_GET['id'] = $_POST['id'];
            $u = new ImageUpload('data', true, array());

            if ($u->error != '') {
                $this->presenter->assign('error', $u->error);
            } else {
                $d = Model::load('DataItem');
                $d->label = $u->getFileEncoded();
                $d->section_id = $_GET['id'];
                $d->image = $u->getFile();
                $d->position = 'DEFAULT';
                $d->hidden = 'DEFAULT';
                $su = Model::load('SectionItem');
                $u = new SectionsUpdate($su, $d->section_id);
                $id = $d->insert(Model::getTable('DataItem'), 1, array(), 1);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$id);
            }
            

        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        }

        $this->buildNav();
        $this->presenter->assign('section_id', $_GET['id']);
    }

    public function default_event()
    {

        $ui_array = array('id');
        $this->loadUIVars('ui_section', $ui_array);
        if (!isset($_GET['id']) || $_GET['id'] == '') {
            $_GET['id'] = 0;
        }

        $this->buildNav();
        $this->presenter->assign('section_id', $_GET['id']);

    }

    public function assertID()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
    }

    public function buildNav()
    {
        $this->setTemplate('elib:/admin/section.tpl');
        $this->assertID();
        $s = Model::load('SectionItem');
        $d = Model::load('DataItem');

        if (isset($_GET['collapsed']) && $_GET['collapsed'] == 1) {
            $collapsed = 1;
        } else {
            $collapsed = 0;
        }

        $s->id = $_GET['id'];
        $s->load();

        $st = new SectionsTree($s, $d, 1, $collapsed);
        $this->presenter->assign('sections', $st->getMarkup());
        $this->presenter->assign('section', $s);
    }

    public function add_section()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $s = Model::load('SectionItem');
            $s->section_id = $_GET['id'];
            $s->label = 'New Section';
            $s->template = 'DEFAULT';
            $s->position = 'DEFAULT';
            $s->hidden = 'DEFAULT';
            $s->insert(Model::getTable('SectionItem'), 1, array(), 0);
            $this->clearCache();
        }
        $this->redirect('admin/dsection/'.$_GET['id']);
    }

    public function delete()
    {
        $this->assertID();
        $s = Model::load('SectionItem');
        $d = Model::load('DataItem');
        $s->id = $_GET['id'];
        $s->load();
        $sd = new SectionsDelete($s, $d, 1);
        $this->clearCache();
        $this->redirect('admin/dsection/'.$s->section_id);
    }

    public function rename()
    {
        $this->buildNav();
        if (isset($_POST['save'])) {
            $s = Model::load('SectionItem');
            $s->id = $_POST['id'];
            $s->load();
            $s->label = $_POST['label'];
            $s->validates();
            if ($s->hasValErrors()) {
                $this->presenter->assign('section', $s);
                $this->presenter->assign('errors', $s->getValErrors());
            } else {
                $s->save(Model::getTable('SectionItem'), array(), 1);
                $su = Model::load('SectionItem');
                $u = new SectionsUpdate($su, $s->id);
                $this->clearCache();
                $this->redirect('admin/dsection/'.$s->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        } else {
            $s = Model::load('SectionItem');
            $s->id = $_GET['id'];
            $s->load();
            $this->presenter->assign('section', $s);
        }
    }

    public function change_template()
    {
        $this->buildNav();
        if (isset($_POST['save'])) {
            $s = Model::load('SectionItem');
            $s->id = $_POST['id'];
            $s->load();
            $s->template = $_POST['template'];
            $s->validates();
            if ($s->hasValErrors()) {
                $this->presenter->assign('section', $s);
                $this->presenter->assign('errors', $s->getValErrors());
            } else {
                $s->save(Model::getTable('SectionItem'), array(), 2);
                $su = Model::load('SectionItem');
                $u = new SectionsUpdate($su, $s->id);
                $this->clearCache();
                $this->redirect('admin/dsection/'.$s->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/'.$_POST['id']);
        } else {
            $s = Model::load('SectionItem');
            $s->id = $_GET['id'];
            $s->load();

            $t = array('0' => '0', 'A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F');

            $this->presenter->assign('templates', $t);
            $this->presenter->assign('section', $s);
        }
    }

    public function toggle_hidden()
    {
        $s = Model::load('SectionItem');
        $s->id = $_GET['id'];
        $s->load();
        $s->hidden = ($s->hidden)? 0 : 1;



        $s->save(Model::getTable('SectionItem'), array(), 2);
        $this->clearCache();
        $this->redirect('admin/dsection/'.$s->id);
    }

    // data item stuff
    public function buildNavData()
    {
        //    $this->setTemplate('admin/data_item.tpl');
        $this->assertID();
        $s = Model::load('SectionItem');
        $d = Model::load('DataItem');

        $d->id = $_GET['id'];
        $d->load();
        $is_section = 0;
        if (isset($_GET['collapsed']) && $_GET['collapsed'] == 1) {
            $collapsed = 1;
        } else {
            $collapsed = 0;
        }

        $st = new SectionsTree($s, $d, 0, $collapsed);
        $this->presenter->assign('sections', $st->getMarkup());
        $this->presenter->assign('data_item', $d);
        $this->presenter->assign('is_container', $d->isContainer());
    }

    public function data_item()
    {
        $ui_array = array('id');
        $this->loadUIVars('ui_data_item', $ui_array);
        if (!isset($_GET['id']) || $_GET['id'] == '') {
            $_GET['id'] = 0;
        }

        $this->buildNavData();
        $this->presenter->assign('data_item_id', $_GET['id']);
        $this->setTemplate('elib:/admin/section.tpl');
    }

    public function delete_data_item()
    {
        $this->assertID();
        $this->setTemplate('section.tpl');
        $s = Model::load('SectionItem');
        $d = Model::load('DataItem');
        $d->id = $_GET['id'];
        $d->load();
        $this->update_timestamps($d->id);
        $sd = new SectionsDelete($s, $d, 0);
        $this->clearCache();
        if (!is_numeric($d->data_item_id)) {
            $this->redirect('admin/dsection/'.$d->section_id);
        } else {
            $this->redirect('admin/dsection/data_item/'.$d->data_item_id);
        }
    }

    public function update_timestamps($id)
    {
        $d = Model::load('DataItem');
        $ancestors = array();
        $ancestors = $d->getAncestorIDs($id, $ancestors);

        if (sizeof($ancestors) > 0) {
            $d->id = min($ancestors);
        } else {
            $d->id = $id;
        }
        $d->load();
        $u = new SectionsUpdate(Model::load('SectionItem'), $d->section_id);
    }

    public function rename_data_item()
    {
        if (isset($_POST['save'])) {
            $d = Model::load('DataItem');
            $d->id = $_POST['id'];
            $d->load();
            $d->label = $_POST['label'];
            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $d->save(Model::getTable('DataItem'), array(), 2);
                $this->update_timestamps($d->id);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$d->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_POST['id']);
        }

        $this->buildNavData();
        $this->setTemplate('elib:/admin/section.tpl');
        $d = Model::load('DataItem');
        $d->id = $_GET['id'];
        $d->load();
        $this->presenter->assign('data_item', $d);
    }

    public function edit_data_item_meta()
    {
        $this->assign('event', 'edit_meta');
        if (isset($_POST['save'])) {
            $d = Model::load('DataItem');
            $d->id = $_POST['id'];
            $d->load();
            $d->meta = $_POST['meta'];

            $d->validates();
            //if($d->hasValErrors())
            if (0) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $d->save(Model::getTable('DataItem'), array(), 1);
                $this->update_timestamps($d->id);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$d->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_POST['id']);
        }

        $this->buildNavData();
        $this->setTemplate('elib:/admin/section.tpl');
        $d = Model::load('DataItem');
        $d->id = $_GET['id'];
        $d->load();
        $this->presenter->assign('data_item', $d);
    }

    public function data_item_toggle_hidden()
    {
        $d = Model::load('DataItem');
        $d->id = $_GET['id'];
        $d->load();
        $d->hidden = ($d->hidden)? 0 : 1;
        $d->save(Model::getTable('DataItem'), array(), 2);
        $this->clearCache();
        $this->redirect('admin/dsection/data_item/'.$d->id);
    }

    public function data_add_data()
    {
        $this->buildNavData();
        $this->setTemplate('elib:/admin/section.tpl');

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
                $this->redirect('admin/dsection/data_add_data_video/'.$_GET['id']);
                break;
            case 4:
                $this->dataAddDataContainer();
                break;
            default:
                $this->redirect('admin/dsection/data_item/'.$_GET['id']);
                break;
            }
        } else {
            $d = Model::load('DataItem');
            $d->id = $_GET['id'];
            $d->load();
            $this->presenter->assign('data_item', $d);
            $this->presenter->assign('data_item_id', $d->id);
            //$this->presenter->assign('add_data_menu', 1);
            $this->presenter->assign('data_types', $this->getDataTypes());

            $c = Model::load('Container');
            $containers = $c->getAllCustom('',  Model::getTable('Container'));
            $containers_arr = array();
            $containers_arr[0] = 'Default';
            foreach ($containers as $item) {
                $id = $item['id'];
                $containers_arr[$id] = $item['name'];
            }
            $this->presenter->assign('container_types', $containers_arr);
        }
    }

    public function data_add_data_body()
    {
        if (isset($_POST['save'])) {
            $d = Model::load('DataItem');
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
                $d->insert(Model::getTable('DataItem'), 1, array(), 1);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$_GET['id']);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        }

        $this->buildNavData();
        $this->setTemplate('elib:/admin/section.tpl');
    }

    public function data_add_data_heading()
    {
        if (isset($_POST['save'])) {
            $d = Model::load('DataItem');
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
                $d->insert(Model::getTable('DataItem'), 1, array(), 1);                
                $this->update_timestamps($d->data_item_id);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$d->data_item_id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        }
        $this->buildNavData();
        $this->setTemplate('elib:/admin/section.tpl');
    }

    public function data_add_data_image()
    {
        if (isset($_POST['save'])) {

            $_GET['id'] = $_POST['id'];

            $p = Model::load('DataItem');
            $p->id = $_GET['id'];
            $p->load();

            if (is_numeric($p->container_id)) {
                $c = Model::load('ContainerImageSize');
                $sizes = $c->getImageSizes($p->container_id);
            } else {
                $sizes = array();
            }

            $images = array(); 
            if (!is_array($_FILES['file']['name'])) {
                 
                $images[0] = $_FILES['file'];
            } else {
                $images = ImageUpload::reArrayFiles($_FILES['file']);                
            }
           
            foreach($images as $img) {
                $_FILES['file'] = $img;

                $u = new ImageUpload('data', true, $sizes);

                if ($u->error != '') {
                    $this->presenter->assign('error', $u->error);

                } else {
                    $d = Model::load('DataItem');
                    $d->label = $u->getFileEncoded();
                    $d->data_item_id = $_GET['id'];
                    $d->image = $u->getFile();
                    $d->position = 'DEFAULT';
                    $d->hidden = 'DEFAULT';
                    $new_id = $d->insert(Model::getTable('DataItem'), 1, array(), 1);
                    // $this->update_timestamps($d->data_item_id);
                    // $this->clearCache();
                    //$this->redirect('admin/dsection/data_item/'.$new_id);
                }
            }
            
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        }
        $this->buildNavData();
        $this->assign('class', 'data_item');
        $this->assign('event', 'add_data_image');
        $this->setTemplate('elib:/admin/section.tpl');
    }

    public function data_add_data_video()
    {
        if (isset($_GET['iframe']) && $_GET['iframe'] == true) {
            $this->setTemplate('elib:/admin/video_upload.tpl');
        } else {
            $this->setTemplate('elib:/admin/section.tpl');
        }

        if (isset($_POST['id'])) {
            echo 1;
            $v = Model::load('VideoUpload');
            $v->upload();

            if ($v->error == '') {
                $v->make_flv();
            }

            if ($v->error != '') {
                $this->presenter->assign('error', $v->error);
            } else {
                $d = Model::load('DataItem');
                $d->label = $v->file;
                $d->data_item_id = $_GET['id'];
                $d->image = 'DEFAULT';
                $d->video = $v->file;
                $d->position = 'DEFAULT';
                $d->hidden = 'DEFAULT';
                $d->insert(Model::getTable('DataItem'), 1, array(), 1);
                $this->update_timestamps($d->data_item_id);                
                $v->generateThumb();
                $this->clearCache();
                //$this->redirect('admin/data_item/'.mysql_insert_id());
            }
        }
        $this->buildNavData();
    }

    public function dataAddDataContainer()
    {
        if (isset($_GET['container_type']) && is_numeric($_GET['container_type'])) {
            $d = Model::load('DataItem');
            $d->data_item_id = $_GET['id'];
            if ($_GET['container_type'] > 0) {
                $d->container_id = $_GET['container_type'];
            }
            $d->label = 'Container';
            $d->position = 'DEFAULT';
            $d->hidden = 'DEFAULT';
            $this->update_timestamps($d->data_item_id);
            $id = $d->insert(Model::getTable('DataItem'), 1, array(), 0);
            $this->clearCache();
            $this->redirect('admin/dsection/data_item/'.$id);
        }
    }

    public function edit_heading()
    {
        if (isset($_POST['save'])) {
            $d = Model::load('DataItem');
            $d->id = $_POST['id'];
            $d->load();
            $d->heading = $_POST['heading'];
            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $this->update_timestamps($d->id);
                $d->save(Model::getTable('DataItem'), array(), 1);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$_GET['id']);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        }

        $this->buildNavData();
        $this->setTemplate('elib:/admin/section.tpl');
        $d = Model::load('DataItem');
        $d->id = $_GET['id'];
        $d->load();
        $this->presenter->assign('data_item', $d);
    }

    public function edit_body()
    {
        if (isset($_POST['save'])) {
            $d = Model::load('DataItem');
            $d->id = $_POST['id'];
            $d->load();
            $d->body = $_POST['body'];
            $d->validates();
            if ($d->hasValErrors()) {
                $this->presenter->assign('data_item', $d);
                $this->presenter->assign('errors', $d->getValErrors());
            } else {
                $d->save(Model::getTable('DataItem'), array(), 1);
                $this->update_timestamps($d->id);
                $this->clearCache();
                $this->redirect('admin/dsection/data_item/'.$_GET['id']);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/data_item/'.$_GET['id']);
        }

        $this->buildNavData();
        $this->setTemplate('elib:/admin/section.tpl');
        $d = Model::load('DataItem');
        $d->id = $_GET['id'];
        $d->load();
        $this->presenter->assign('data_item', $d);
    }

    public function edit_body_raw()
    {
        $this->presenter->assign('event', 'edit_body');
        $this->presenter->assign('raw_mode', true);
        $this->edit_body();
    }

    // containers
    public function add_container()
    {
        $c = Model::load('Container');
        $c->name = '#New Container';
        $c->insert(Model::getTable('Container'), 1, array(), 0);
        $this->clearCache();
        $this->redirect('admin/dsection/containers');
    }

    public function containers()
    {
        if (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection');
        } elseif (isset($_POST['save'])) {
            foreach ($_POST['image_size'] as $index => $value) {
                $c = Model::load('Container');
                $c->update($index, $value);
            }
            $this->clearCache();
            $this->redirect('admin/dsection');
        }

        $this->setTemplate('elib:/admin/containers.tpl');
        $c = Model::load('Container');
        $containers = $c->getAll();
        $this->assign('containers', $containers);
        $i = Model::load('ImageSize');
        $image_sizes = $i->loadAsOptions(Model::getTable('ImageSize'), 'name');
        $this->presenter->assign('image_sizes', $image_sizes);
    }

    public function remove_container()
    {
        $c = Model::load('Container');
        $c->id = $_GET['id'];
        $c->remove();
        $this->clearCache();
        $this->redirect('admin/dsection/containers');
    }

    public function rename_container()
    {
        $this->setTemplate('elib:/admin/containers.tpl');
        if (isset($_POST['cancel'])) {
            $this->redirect('admin/dsection/containers');
        } elseif (isset($_POST['save'])) {
            $c = Model::load('Container');
            $c->id = $_GET['id'];
            $c->load();
            $c->name = $_POST['name'];
            $c->validates();
            if (!$c->hasValErrors()) {
                $c->save(Model::getTable('Container'), array(), 1);
                $this->clearCache();
                $this->redirect('admin/dsection/containers');
            } else {
                $this->assign('container', $c);
                $this->presenter->assign('errors', $c->getValErrors());
            }
        } else {
            $c = Model::load('Container');
            $c->id = $_GET['id'];
            $c->load();
            $this->assign('container', $c);
        }
    }

    // image sizes
    public function add_image_size()
    {
        $i = Model::load('ImageSize');
        $i->name = 'New Image Size';
        $i->width = 0;
        $i->height = 0;
        $i->prefix = 'new';
        $i->insert(Model::getTable('ImageSize'), 1, array(), 0);
        $this->clearCache();
        $this->redirect('admin/dsection/image_sizes');
    }

    public function image_sizes()
    {
        if ($this->isXMLHttpRequest()) {
            $return_code = 1;
            if (isset($_POST['id']) && is_numeric($_POST['id'])) {
                $i = Model::load('ImageSize');
                $i->id = $_POST['id'];
                $i->load();
                $field = $_POST['field'];
                $i->$field = $_POST['value'];
                $i->validates();
                if ($i->hasValErrors()) {
                    //$this->logMe($i->getValErrors());
                    $return_code = 2;
                } else {
                    $i->save(Model::getTable('ImageSize'), array(), 1);
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
                $c = Model::load('Container');
                $c->update($index, $value);
            }
            $this->clearCache();
            $this->redirect('admin/dsection');
        }

        $this->setTemplate('elib:/admin/image_sizes.tpl');

        $i = Model::load('ImageSize');
        $sql = ' ORDER BY name';
        $image_sizes = $i->getAllCustom(Model::getTable('ImageSize'), $sql);

        $this->presenter->assign('image_sizes', $image_sizes);
    }

    public function remove_image_size()
    {
        $i = Model::load('ImageSize');
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
      $c = Model::load('Container');
      $c->id = $_GET['id'];
      $c->load();
      $c->name = $_POST['name'];
      $c->validates();
      if (!$c->hasValErrors()) {
      $c->save(Model::getTable('Container'), array(), 1);
      $this->redirect('admin/containers');
      } else {
      $this->assign('container', $c);
      $this->presenter->assign('errors', $c->getValErrors());
      }
      } else {
      $c = Model::load('Container');
      $c->id = $_GET['id'];
      $c->load();
      $this->assign('container', $c);
      }
      }
    */

    public function update_image_sizes()
    {
        $i = Model::load('ImageSize');
        $i->id = $_GET['id'];
        $i->load();
        $images = $i->getDataFiles();

        $d = array(array($i->prefix.'_', $i->width, $i->height));
        $u = new ImageUpload('', false, $d);
        set_time_limit(300);
        $u->resize($images);
        $this->clearCache();
        $this->redirect('admin/dsection/image_sizes');
    }



    public function sort() {

        $position = 1;


        foreach($_POST as $type => $value) {

            if ($type == 'section') {
                $model = 'SectionItem';
            } else {
                $model = 'DataItem';
            }

            foreach ($value as $id) {

                $object = Model::load($model);
                $object->id = $id;
                $object->load();
                $object->position = $position;
                $object->save();
                $position++;
            }
        }

        header('Content-type: application/json');
        echo json_encode(1);
        return false;
    }
}
