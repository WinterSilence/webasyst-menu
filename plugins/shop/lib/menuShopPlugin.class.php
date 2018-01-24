<?php

class menuShopPlugin extends waPlugin
{

    public function registerControls()
    {
        $registered_controls = array();

        if(wa()->appExists('shop')) {
            $registered_controls['shop_plugin_category'] = array(
                'name' => _wp('Link to a category'),
                'class' => 'menuShopPluginCategoryItem'
            );
            $registered_controls['shop_plugin_category_subtree'] = array(
                'name' => _wp('Categories subtree'),
                'class' => 'menuShopPluginCategoriesSubtreeItem',
            );
            /* Пока отключим.
            $registered_controls['shop_plugin_product'] = array(
                'name' => _wp('Link to a product'),
                'class' => 'menuShopPluginProductItem'
            );
            $registered_controls['shop_plugin_page'] = array(
                'name' => _wp('Link to a shop page'),
                'class' => 'menuShopPluginPageItem'
            );
             */
        }

        return $registered_controls;
    }
}
