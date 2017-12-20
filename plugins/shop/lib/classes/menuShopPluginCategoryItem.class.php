<?php


class menuShopPluginCategoryItem extends menuItem
{
    /**
     * @return string
     */
    public function getBackendIcon()
    {
        if(empty($this->params['include_subcategories'])) {
            return 'folder';
        } else {
            return 'folders';
        }
    }


    protected function getAvailableParams() {
        return array(
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
                    _wp('Don\'t include'),
                    _wp('1 level depth (direct children only)'),
                    _wp('2 level depth'),
                    _wp('3 level depth'),
                )
            )
        );
    }


    public function toArray()
    {
        if(!wa()->appExists('shop')) {
            return array();
        }
        if(!$category_id = ifempty($this->params['category_id'], 0)) {
            return array();
        }

        $wa = wa('shop');

        $cm = new shopCategoryModel();
        if(!($c = $cm->getById($category_id)) || empty($c['status'])) {
            return array();
        }


        // @todo check types for other settlements
        $route = $this->getShopRoute();
        $category_url = ifempty($route['url_type']) == 1 ? $c['url'] : $c['full_url'];
        $frontend_url = $wa->getRouteUrl('shop/frontend/category', array('category_url' => $category_url));

        if($depth = ifempty($this->params['include_subcategories'], 0)) {
            $helper = new shopViewHelper($wa);
            $childs = $helper->categories($category_id, $depth, true);
        } else {
            $childs = array();
        }
        $data = parent::toArray();

        $data['childs'] = array_merge($childs, $data['childs']);
        $data['url'] = $frontend_url;

        return $data;
    }



    protected function getShopRoute($domain = null, $route_url = null)
    {
        $current_domain = wa()->getRouting()->getDomain(null, true);
        $current_route = wa()->getRouting()->getRoute();
        if (wa()->getApp() != 'shop' || ($domain && $current_domain != $domain) || ($route_url && $route_url != $current_route['url'])) {
            $routes = wa()->getRouting()->getByApp('shop');
            if (!$routes) {
                return false;
            }
            if ($domain && !isset($routes[$domain])) {
                return false;
            }
            $domain = $current_domain;
            if (!isset($routes[$domain])) {
                $domain = key($routes);
            }
        } else {
            $current_route['domain'] = $current_domain;
            return $current_route;
        }
        if ($route_url) {
            $route = false;
            foreach ($routes[$domain] as $r) {
                if ($r['url'] === $route_url) {
                    $route = $r;
                    break;
                }
            }
        } else {
            $route = end($routes[$domain]);
        }
        if ($route) {
            $route['domain'] = $domain;
        }
        return $route;
    }

    public static function getCategoryControl($name, $params)
    {
        if(!wa()->appExists('shop')) {
            return sprintf('<em class="status-error">%s</em>', _wp('App "Shop" required.'));
        }
        $view = wa()->getView();
        $view->assign('name', $name);
        $view->assign('params', $params);
        $template = wa()->getAppPath('plugins/shop/templates/controls/CategoryControl.html', 'menu');
        return $view->fetch($template);
    }
}