<?php

class menuItemActions extends waJsonActions
{
    protected $id;

    public function postExecute()
    {
        if(!$this->id) {
            return;
        }

        $mi = new menuItemModel();
        $key = $mi->getMenuByChild($this->id);
        $group = 'data';

        if($cache = wa('menu')->getCache()) {
            $cache->delete($key, $group);
        }
    }


    public function moveAction()
    {
        $id = waRequest::post('id', null);
        if (!$id) {
            throw new waException("Unknown item id");
        }

        $before_id = waRequest::post('before_id', null);
        $parent_id = waRequest::post('parent_id', 0);

        if ($id == $before_id) {
            throw new waException(_w("Item couldn't be inserted before itself"));
        }

        if ($id == $parent_id) {
            throw new waException(_w("Item couldn't be parent of itself"));
        }

        if (($before_id || $parent_id) && $before_id == $parent_id) {
            throw new waException(_w("Before item couldn't be parent item"));
        }

        $mi = new menuItemModel();
        $mi->move($id, $parent_id, $before_id);
        $this->id = $id;
    }


    public function saveAction()
    {
        $data = waRequest::post('item');
        if(!empty($data['id']) && ($data['id'] !== 'new')) {

            $mi = new menuItemModel();
            $item = $mi->getItem($data['id']);
            $item->save($data, waRequest::post('params'));

        } else {
            $mi = new menuItemModel();
            unset($data['id']);
            $item = $mi->addItem($data, waRequest::post('params'));
        }
        $this->id = $item['id'];
        $this->response = array(

            'id' => $item['id'],
            'icon' => $item->getBackendIcon(),
            'parent_id' => $item['parent_id'],
            'name' => $item['name'],
            'status' => $item['status'],
        );
    }

    public function deleteAction()
    {

        if($id = waRequest::get('id')) {
            $mim = new menuItemModel();

            $menu_id = $mim->getMenuByChild($id);
            $mim->deleteBranch($id);

            $key = $menu_id;
            $group = 'data';

            if($cache = wa('menu')->getCache()) {
                $cache->delete($key, $group);
            }
        } else {
            $this->setError(_w('Empty request.'));
        }
    }
}
