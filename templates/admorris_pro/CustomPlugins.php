<?php

namespace Template\admorris_pro;

use Illuminate\Support\Collection;
use JTL\Cache\JTLCacheInterface;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Category\KategorieListe;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\CheckBox;
use JTL\DB\DbInterface;
use JTL\Filter\Config;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Category;
use JTL\Helpers\Manufacturer;
use JTL\Helpers\Seo;
use JTL\Helpers\Tax;
use JTL\Link\Link;
use JTL\Link\LinkGroupInterface;
use JTL\Media\Image\Product;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Staat;
use Smarty_Internal_Data;

/**
 * Class Plugins
 * @package Template\Admorris
 */
class CustomPlugins extends Plugins
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * Plugins constructor.
     * @param DbInterface       $db
     * @param JTLCacheInterface $cache
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache, TemplateUtils $utils)
    {
        parent::__construct($db, $cache, $utils);
        $this->db = $db;
        $this->cache = $cache;
    }

    public function nl2br_notHtml($string)
    {
        return $this->isHtml($string) ? $string : nl2br($string);
    }

    public function isHtml($string)
    {
        return $string != strip_tags($string) ? true : false;
    }

    public function obfuscateEmail($params, $content, $smarty, &$repeat)
    {
        if (isset($content)) {
            $encoded = json_encode(str_rot13($content));
            $id = 'A' . base64_encode(random_bytes(10));
            $script =
                '<span id="' .
                $id .
                '"><script>document.getElementById("' .
                $id .
                '").parentNode.innerHTML=' .
                $encoded .
                '.replace(/[a-zA-Z]/g,function(c){return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);});</script></span>';
            return $script;
        }
    }
    public function template_exists($string)
    {
        global $smarty;
        return $smarty->template_exists($string);
    }

    
}
