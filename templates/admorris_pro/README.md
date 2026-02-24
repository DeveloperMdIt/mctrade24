# Admorris Evo ChildTemplate


header template files are loaded in functions.php

# Category Images

##Kategorie Banner-Bild
Kategorie FunktionsAttribute:

+ category_banner_image

Banner Bilder müssen in '/bilder/kategorien/banner/' abgelegt werden und der Dateiname im Kategorie-Attribut 'category_banner_image' hinterlegt werden.

## Dropdown Images
Kategorie FunktionsAttribute:

+ category_dropdown_image
+ category_dropdown_padding

Background images for the category dropdowns need to be placed in 'bilder/kategorien/dropdowns' and the category attribute only contains the filename

## Dropdown Kategoriekurzbeschreibung
Kategorie Attribut:

+ category_summary

## Columns in Kategoriedropdown
In `functions.php` ist die Funktion `subcategories_columns_count`, die berechnet wieviele Spalten im Dropdown angezeigt werden. Mit der `$divisor` Variable kann eingestellt werden, wann umgebrochen werden soll. 

TODO: Wert von `$divisor` aus einer Plugin Einstellung holen

## Menu Icons
FunktionsAttribut: category_navbar_icon

Image(s) need to be placed in 'bilder/kategorien/icons' and the category attribute only contains the filename. The inverted icon can be written in the second line. 

## Product Slider Config

Über die globale javascript Variable productSliderConfig kann eine alternative slickSlider Breakpoints übergeben werden.

``` js
[
    {
        breakpoint: 380,
        num: 1
    },
    {
        breakpoint: 460,
        num: 2
    },
    {
        breakpoint: 768,
        num: 3
    },
    {
        breakpoint: 1100,
        num: 4
    }
];
```

# Template Files renamed/moved

Category-Megamenu: 
snippets/categories_mega.tpl => header/category_megamenu.tpl + header/category_dropdown.tpl
basket/cart_dropdown.tpl => header/cart_dropdown.tpl



# base.less modifications (code deactivated)
```css

header {} 

#logo a img {}

#quick-login

[id="shop-nav"] {
    .dropdown-menu {}
}

.row.gallery .product-wrapper {}
// + @media following
1855 - 1878

#main-wrapper.fluid {}
#main-wrapper.exclusive {}
2289 - 2327

header.fixed-navbar {}

#search {}
#search .input-group-addon button, #search-form .input-group .btn {}


```