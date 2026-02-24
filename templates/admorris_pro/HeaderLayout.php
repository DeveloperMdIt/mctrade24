<?php

namespace Template\admorris_pro;

use JTL\Shop;

class HeaderLayout {
    private $templateMapping = [
        'logo'             => 'header/logo.tpl',
        'categories'       => 'header/category_megamenu.tpl',
        'manufacturers'    => 'header/manufacturer_megamenu.tpl',
        'cms-megamenu'     => 'header/pages_megamenu.tpl',
        'cms-links'        => 'header/cms_links.tpl',
        'social-icons'     => 'header/social_header_icons.tpl',
        'search'           => 'header/search.tpl',
        // 'shop-nav'         => 'layout/header_shop_nav.tpl',
        'contact'          => 'header/header_contact.tpl',
        'box'              => 'header/header_boxes.tpl',
        'box2'             => 'header/header_boxes.tpl',
        'box3'             => 'header/header_boxes.tpl',
        'box4'             => 'header/header_boxes.tpl',
        'offcanvas-button' => 'header/offcanvas_button.tpl',
        'account'          => 'header/shopnav_account.tpl',
        'cart'             => 'header/shopnav_cart.tpl',
        'comparelist'      => 'layout/header_shop_nav_compare.tpl',
        'wishlist'         => 'layout/header_shop_nav_wish.tpl',
        'signout'          => 'header/shopnav_signout.tpl',
        'language'         => 'header/language_selector.tpl',
        'currency'         => 'header/currency_selector.tpl',
        'reorder'          => 'header/reorder.tpl'
        
        
    ];

    private $offcanvasTemplateMapping = [
        'categories'    => 'offcanvas/offcanvas_categories.tpl',
        'manufacturers' => 'offcanvas/offcanvas_manufacturers.tpl',
        // 'contact'       => '',
        'cms-links'     => 'offcanvas/offcanvas_cms_links.tpl',
        'cms-megamenu'  => 'offcanvas/offcanvas_pages.tpl'
    ];

    public $headerItems = [];
    private $desktopLayout;
    private $mobileLayout;
    private $offcanvasLayout;
    public ?string $classes;

    

    public function __construct() {

            $headerLayoutDataPath = \PFAD_ROOT.'templates/admorris_pro/php/headerLayoutData.json';

            if (\file_exists($headerLayoutDataPath)) {
                $jsonStr = \file_get_contents($headerLayoutDataPath);
                $layoutPreset = \json_decode($jsonStr, true);

                $this->setItems($layoutPreset['items']);
                $this->desktopLayout = $layoutPreset['desktopLayout'];
                $this->mobileLayout = $layoutPreset['mobileLayout'];
                $this->offcanvasLayout = $layoutPreset['offcanvasLayout'];
                $this->classes = $layoutPreset['classes'];
            } else {
                throw new \Exception('templates/admorris_pro/php/headerLayoutData.json not found.');
            }


    }

    /**
     * $headerItems mit Template-Pfaden und Einstellungen befüllen
     */
    private function setItems($itemSettings) {

        foreach ($this->templateMapping as $itemName => $template) {
            $this->headerItems[$itemName]['template'] = $template;
            if (!empty($this->offcanvasTemplateMapping[$itemName])) {
                $this->headerItems[$itemName]['templateOffcanvas'] = $this->offcanvasTemplateMapping[$itemName];
            }
            $this->headerItems[$itemName]['name'] = $itemName;
            $groupCssClass = $this->groupCssClass($itemName);
            if ($groupCssClass) {
                $this->headerItems[$itemName]['class'] = $groupCssClass;
            }

            if(!empty($itemSettings[$itemName])) {
                foreach ($itemSettings[$itemName] as $settingKey => $setting) {
                    $this->headerItems[$itemName][$settingKey] = $setting;
                }
            }
        }
    }

    public function getItemSetting($itemName, $setting, $layoutType = 'undefined') {

        $item = $this->headerItems[$itemName];
        if ($layoutType === 'undefined' || $layoutType === 'desktopLayout') {
            return isset($item[$setting]) ? $item[$setting] : null;
        } elseif ($layoutType === 'mobileLayout') {
            $itemSetting = isset($item['mobile-'.$setting]) ? $item['mobile-'.$setting] : null;

            if (!empty($itemSetting)) {
                return $itemSetting;
            } else {
                return $item[$setting];
            }
        } elseif ($layoutType === 'offcanvasLayout') {
            $itemSetting = isset($item['offcanvas-'.$setting]) ? $item['offcanvas-'.$setting] : null;
            if ($itemSetting) {
                return $itemSetting;
            } else {
                return $item[$setting];
            }
        }
    }

    private function getLayoutType($layoutType) {
        if ($layoutType === 'desktopLayout') {
            $layout = $this->desktopLayout;
        } elseif ($layoutType === 'mobileLayout') {
            $layout = $this->mobileLayout;
        } else {
            $layout = $this->offcanvasLayout;
        }

        return $layout;
    }

    public function getRowLayout($layoutType, $row) {

        $layout = $this->getLayoutType($layoutType);

        if (!empty($layout[$row])) {
            return $layout[$row];
        }
        
    }

    public function getRowSetting($layoutType, $row, $setting) {
        $rowLayout = $this->getRowLayout($layoutType, $row);
        if ($rowLayout && isset($rowLayout[$setting])) {
            return $rowLayout[$setting];
        }
    }

    public function getColumnItems($layoutType, $row, $column) {
        $row = $this->getRowLayout($layoutType, $row);


        if (!empty($row[$column])) {
            $columnItems = [];

   
            foreach ($row[$column] as $key=>$item) {
                if (\is_array($item)) {
                    $subItems = ['group' => true, 'items' => []];
                    foreach ($item as $subItem) {
                        \array_push($subItems['items'], $this->headerItems[$subItem]);
                    }
                    \array_push($columnItems, $subItems);
                } else {
                    \array_push($columnItems, $this->headerItems[$item]);
                }

                
            }
            return $columnItems;
        }
    }

    

    public function getRowItems($layoutType, $row) {
        $rowLayout = $this->getRowLayout($layoutType, $row);

        /** 
         * Check if empty columns should be rendered
         * When the center column contains elements and one of the side columns too, then render all.
         * Otherwise not 
         */
        
        $centerContainsEl = !empty($rowLayout[2]) ? true : false;
        $renderEmptyCol = $centerContainsEl && !empty($rowLayout[1]) || $centerContainsEl && !empty($rowLayout[3]) ? true : false;
        $rowArr = [];
        /** Set Center-Col Layout when $renderEmptyCol is true.
         *  To center the middle column perfectly when de side-columnn contain items
         *  the center-col css class modifier needs to be set
         */

        if ($renderEmptyCol) {
            $rowArr['centerCol'] = true;
        }

        foreach ($rowLayout as $column => $columnEl) {
            if (!empty($rowLayout[$column]) || $renderEmptyCol) {
                $columnItems = [];
    
       
                foreach ($columnEl as $key=>$item) {
                    if (\is_array($item)) {
                        $subItems = ['group' => true, 'items' => []];
                        foreach ($item as $subItem) {
                            \array_push($subItems['items'], $this->headerItems[$subItem]);
                        }
                        \array_push($columnItems, $subItems);
                    } elseif (!empty($this->headerItems[$item])) {
                        \array_push($columnItems, $this->headerItems[$item]);
                    }
                }
                $rowArr[$column] = $columnItems;
            }
            
        }
        return $rowArr;
        
    }

    public function getOffcanvasItems() {
        $offcanvasItems = [];
        foreach ($this->offcanvasLayout as $item) {
            // subarrays
            if (is_array($item)) {
                
                // group for disabling separators, custom margin settings
                // or horizontal layout 
                if (isset($item['group'])) {
                    $group = $item;
                    $group['group'] = [];

                    foreach ($item['group'] as $groupItem) {
                        $group['group'][] = $this->headerItems[$groupItem];
                    }
                    $group['classes'] = $this->cssModifierClasses($group, 'offcanvas-nav__group--');

                    $offcanvasItems[] = $group;
                    
                } elseif (isset($item['item'])) {
                    // $itemWithConfig = $item;
                    $itemWithConfig = $this->headerItems[$item['item']];
                    $itemWithConfig['classes'] = $this->cssModifierClasses($item, 'offcanvas-nav__element--');
                    $offcanvasItems[] = $itemWithConfig;
                }
            } else {
                $offcanvasItems[] = $this->headerItems[$item];

            }
        }
        return $offcanvasItems;
    }


    private function groupCssClass ($name) {
        $itemClass = '';

        if (in_array($name, ['categories', 'cms-megamenu', 'manufacturers'])) {
            $itemClass = ' header-row__element--megamenu';
        } elseif (in_array($name, ['account', 'cart', 'wishlist', 'comparelist', 'reorder'])) {
            $itemClass = ' header-row__element--shopnav';
        }
        return $itemClass;
    }

    public function getItemGroup ($name) {
        if (in_array($name, ['categories', 'cms-megamenu', 'manufacturers'])) {
            return 'megamenu';
        }
    }

    private function cssModifierClasses ($item, $prefix) {
        $i = $item;
        $modifiers = [];
        // deleting  'group' and 'item' leaves only the settings 
        unset($i['group']);
        unset($i['item']);
        if (!empty($i)) {
            foreach ($i as $key => $value) {
                if ($value === true) {
                    $modifiers[] = $prefix.$key;
                } else {
                    $modifiers[] = $prefix.$key.'-'.$value;
                }
            }
    
            return ' ' . implode(' ', $modifiers);
        }
        
    }

    public function getRowStyles ($layoutType, $row) {
        $layout = $this->getLayoutType($layoutType);
        $styles = $layout[$row]['styles'];
        if (!empty($styles)) {
            $css = '';
            foreach ($styles as $key => $value) {
                $css .= $key.': '.$value.';';
            }
            return $css;
        }

        return;
    }

    public function getRowClasses ($layoutType, $row) {
        $layout = $this->getLayoutType($layoutType);
        if (!empty($layout[$row]['classes'])) {
            return $layout[$row]['classes'];
        }
        
    }

    // private function groupItems () {
    //     foreach ($this->desktopLayout as $key => $row) {
    //         foreach ($row as $key => $column) {
    //             $itemGroup = [];
                

    //             function groupBy($column) {
    //                 if (count($column) === 0) {
    //                     return;
    //                 } else {

    //                     foreach ($column as $key => $item) {
    //                         $group = $this->getItemGroup($item);
    //                         if ($group) {
    //                             $itemGroup[] = $item;
    //                             $itemGroup['type'] = $group;
    //                             unset($column[$key]);
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }
    // }



}