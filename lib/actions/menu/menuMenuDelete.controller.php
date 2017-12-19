<?php


class menuMenuDeleteController extends waJsonController
{
    public function execute()
    {
        if($id = waRequest::get('id')) {
            $mim = new menuItemModel();
            $mim->deleteBranch($id);

            $key = $id;
            $group = 'data';

            if($cache = wa('menu')->getCache()) {
                $cache->delete($key, $group);
            }
        } else {
            $this->setError(_w('Empty request.'));
        }
    }

}