<?php


class menuShopPluginCategoriesSubtreeItem extends menuItem
{
    protected function getAvailableParams()
    {
        wa()->pushActivePlugin('shop', 'menu');
        $data = array(
            'category_id' => array(
                'value'        => '',
                'title'        => _wp('Category'),
                'description'  => '',
                'control_type' => waHtmlControl::CUSTOM.' menuShopPluginCategoryItem::getCategoryControl',
            ),
            'include_subcategories' => array(
                'value'        => 0,
                'title'        => _wp('Include subcategories'),
                'description'  => '',
                'control_type' => waHtmlControl::SELECT,
                'options'      => array(
                    _wp('All children'),
                    _wp('1 level depth (direct children only)'),
                    _wp('2 level depth'),
                    _wp('3 level depth'),
                )
            )
        );

        waSystem::popActivePlugin();

        return $data;
    }

    public function save($data = null, $params = null)
    {
        if(is_array($data)) {
            $this->data = $data;
        }
        if($params !== null) {
            $this->params = $params;
        }

        if(!empty($this->params['category_id']) && wa()->appExists('shop')) {
            wa('shop');

            $depth = ifempty($this->params['include_subcategories'], 0);
            if($depth < 1) $depth = null;


            $cm = new shopCategoryModel();
            $mi = new menuItemModel();
            $tree = $cm->getTree($this->params['category_id'], $depth);

            $category_to_item = array(
                $this->params['category_id'] => $this->data['id'],
            );

            foreach ($tree as $category) {
                if($category['id'] == $this->params['category_id']) {
                    continue;
                }

                $parent_id = !empty($category_to_item[$category['parent_id']]) ?
                    $category_to_item[$category['parent_id']] :
                    $category_to_item[$this->params['category_id']];

                $item = $mi->addItem(array(
                    'type' => 'shop_plugin_category',
                    'status' => 1,
                    'name' => $category['name'],
                    'parent_id' => $parent_id
                ), array(
                    'category_id' => $category['id'],
                    'include_subcategories' => 0
                ));

                $category_to_item[$category['id']] = $item['id'];
            }
            $this->params['include_subcategories'] = 0; // disable autofill
        }

        $this->data['type'] = 'shop_plugin_category';

        $this->saveData();
        $this->saveParams();
    }
}