<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Utils;

use JTL\Language\LanguageHelper;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;

class Snippets
{
    public function __construct(private JTLSmarty $smarty, private LanguageHelper $language)
    {
    }

    public function get(): array
    {
        /** @var Firma|null $company */
        $company = $this->smarty->getTemplateVars('Firma');
        $shopSettings = Shopsetting::getInstance()->getAll();
        $specialPages = Shop::Container()->getLinkService()->getSpecialPages();
        $clerkSliderId = uniqid();

        $shippingTimeNote = $this->language->get('shippingInformation', 'productDetails');

        if (isset($company, $company->country, $specialPages[\LINKTYP_VERSAND])) {
            $shippingTimeNote = sprintf(
                $shippingTimeNote,
                $company->country->getName(),
                $specialPages[\LINKTYP_VERSAND]->getURL(),
                $specialPages[\LINKTYP_VERSAND]->getURL()
            );
        }

        return [
            'shippingTimeNote' => htmlspecialchars($shippingTimeNote, ENT_QUOTES, 'utf-8'),
            'addToCart' => $this->language->get("addToCart"),
            'ribbons' => [
                "1" => $this->language->get("ribbon-1", "productOverview"),
                "2" => $this->language->get("ribbon-2", "productOverview"),
                "3" => $this->language->get("ribbon-3", "productOverview"),
                "4" => $this->language->get("ribbon-4", "productOverview"),
                "5" => $this->language->get("ribbon-5", "productOverview"),
                "6" => $this->language->get("ribbon-6", "productOverview"),
                "7" => $this->language->get("ribbon-7", "productOverview"),
                "8" => $this->language->get("ribbon-8", "productOverview"),
                "9" => $this->language->get("ribbon-9", "productOverview")
            ],
            'priceStarting' => $this->language->get("priceStarting"),
            'vpePer' => $this->language->get("vpePer"),
            'shippingTime' => $this->language->get('shippingTime'),
            'productRating' => $this->language->get('productRating', 'product rating'),
            'requestNotification' => $this->language->get('requestNotification'),
            'details' => $this->language->get('details'),
            'icons' => [
                "ratingEmpty" => 'far fa-star',
                "ratingHalf" => 'fas fa-star-half-alt',
                "rating" => 'fas fa-star',
                "basket" => 'fas fa-shopping-cart',
                "sliderPrevious" => 'fas fa-chevron-left',
                "sliderNext" => 'fas fa-chevron-right',
                "stock" =>  'fas fa-dot-circle'
            ],
            'settings' => [
                "slider" => [
                    "id" => $clerkSliderId,
                    "settings" => [
                        "infinite" => false,
                        "nextArrow" => "#clerk-slider-next-{$clerkSliderId}",
                        "prevArrow" => "#clerk-slider-prev-{$clerkSliderId}",
                        "slidesToShow" => 4,
                        "slidesToScroll" => 1,
                        "autoplay" => false,
                        "adaptiveHeight" => true,
                        "lazyload" => "ondemand",
                        "responsive" => [
                            [
                                "breakpoint" => 1457,
                                "settings" => [
                                    "slidesToShow" => 4,
                                    "slidesToScroll" => 1
                                ]
                            ],
                            [
                                "breakpoint" => 1231,
                                "settings" => [
                                    "slidesToShow" => 4,
                                    "slidesToScroll" => 1
                                ]
                            ],
                            [
                                "breakpoint" => 833,
                                "settings" => [
                                    "slidesToShow" => 2,
                                    "slidesToScroll" => 1
                                ]
                            ],
                            [
                                "breakpoint" => 661,
                                "settings" => [
                                    "slidesToShow" => 2,
                                    "slidesToScroll" => 1
                                ]
                            ]
                        ]
                    ]
                ],
                "showBrand" => $shopSettings['artikeluebersicht']['artikeluebersicht_hersteller_anzeigen'] !== 'N',
                "newProductmaxDays" => $shopSettings['boxen']['box_neuimsortiment_alter_tage'],
                "topProductMinStars" => $shopSettings['boxen']['boxen_topbewertet_minsterne'],
                "bestSellerMinSales" => $shopSettings['global']['global_bestseller_minanzahl'],
                "bestSellerDayRange" => $shopSettings['global']['global_bestseller_tage'],
                "storageLightsGreen" => $shopSettings['global']['artikel_lagerampel_gruen'],
                "storageLightsRed" => $shopSettings['global']['artikel_lagerampel_rot'],
                "inventoryManagement" => $shopSettings['global']['artikel_ampel_lagernull_gruen'],
                "storageLightsGreen" => $shopSettings['global']['artikel_lagerampel_gruen'],
                "storageLightsRed" => $shopSettings['global']['artikel_lagerampel_rot'],
                "storageLightIcon" => 'fas fa-dot-circle',
                "storageLightTextGreen" => $this->language->get('ampelGruen', 'global'),
                "storageLightTextYellow" => $this->language->get('ampelGelb', 'global'),
                "storageLightTextRed" => $this->language->get('ampelRot', 'global')
            ]
        ];
    }
}
