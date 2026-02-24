<?php

return [
    0 => [
        'items' => [
            'wishlist' => [
                'label' => 'icon_text'
            ],
            'account' => [
                'label' => 'icon_text',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'icon_text'
            ],
            'search' => [
                'dropdown' => false
            ],
            'cart' => [
                'label' => 'icon',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'icon_text'
            ],
            'comparelist' => [
                'label' => 'icon_text'
            ],
            'language' => [
                'label' => 'icon',
                'offcanvas-label' => 'icon_text',
                'dropdown' => true
            ],
            'currency' => [
                'label' => 'icon_text'
            ]
        ],
        // 'desktopLayout' => [
        //     '1' => [
        //         '1' => ['cms-links', 'box'],
        //         '3' => ['social-icons', 'language', 'currency'],
        //         'inverted' => true,
        //         'height' => 'small'
        //     ],
        //     '2' => [
        //         '1' => ['logo'], 
        //         '3' => ['categories', 'cms-megamenu', 'manufacturers',  'wishlist', 'comparelist', 'cart', 'account']
        //     ],
        //     '3' => [
        //         '3' => ['contact', 'search']
        //         // 'inverted' => 1,
        //         // 'bg_color' => 'black'
        //     ]
        // ],
        'desktopLayout' => [
            '2' => [
                '1' => ['logo', ['categories', 'cms-megamenu', 'manufacturers']],
                '3' => ['search', 'wishlist', 'comparelist', 'cart', 'account'],
                // 'classes' => 'top-aligned',
                'styles' => [
                    'padding-top' => '10px',
                    'padding-bottom' => '10px',

                ]
            ]
        ],
        'mobileLayout' => [
            '2' => [
                '1' => ['logo'],
                '2' => [],
                '3' => ['search', 'offcanvas-button'],
                'styles' => [
                    'padding-top' => '5px',
                    'padding-bottom' => '5px'
                ]
            ]
            // '1' => [
            //     3 => ['social-icons'],
            //     'inverted' => 1
            // ]
        ],
        'offcanvasLayout' => [

            [
                'group' => ['signout', 'cart', 'account', 'wishlist', 'language', 'currency'],
                'horizontal' => true
            ],
            [
                'item' => 'categories',
                'stretch' => false
            ],
            'manufacturers',
            'cms-megamenu',
            // [
            //     'group' => [
            //         'signout',
            //         'cart',
            //         'account',
            //         'cms-links',
            //         'social-icons',
            //         'contact',
            //     ],
            //     'spacing' => 'wide'
            // ]
            
        ]

    ],
    1 => [
        'items' => [
            'wishlist' => [
                'label' => 'icon'
            ],
            'account' => [
                'label' => 'icon',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'icon_text'
            ],
            'search' => [
                'dropdown' => true
            ],
            'cart' => [
                'label' => 'icon',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'icon_text'
            ],
            'comparelist' => [
                'label' => 'icon'
            ],
            'language' => [
                'label' => 'icon',
                'offcanvas-label' => 'icon_text'
            ],
            'currency' => [
                'label' => 'icon'
            ]
        ],
        'desktopLayout' => [
		            '1' => [
                '1' => ['cms-links'],
                // '2' => ['box'],
                '3' => ['social-icons']
            ],
            '2' => [
                '1' => ['logo'], 
                '3' => [['categories', 'cms-megamenu', 'manufacturers'], 'search', 'wishlist', 'comparelist', 'cart', 'account'],
                // 'classes' => 'top-aligned',
                'styles' => [
                    'padding-top' => '20px',
                    'padding-bottom' => '20px',

                ]
            ],
            'dropdown_search' => true
        ],
        'mobileLayout' => [
            // '1' => [
                // '3' => ['social-icons', 'currency', 'language']
            // ],
            '2' => [
                '1' => ['logo'],
                '2' => [],
                '3' => ['cart', 'account','comparelist', 'wishlist', 'search', 'offcanvas-button'],
                'styles' => [
                    'padding-top' => '5px',
                    'padding-bottom' => '5px'
                ]
            ],
            // '1' => [
            //     3 => ['social-icons'],
            //     'inverted' => 1
            // ]
        ],
        'offcanvasLayout' => [
            [
                'group' => ['signout', 'language', 'currency'],
                // 'horizontal' => true
            ],
            'search',
            [
                'item' => 'categories',
                'stretch' => true
            ],
            'manufacturers',
            'cms-megamenu',
            [
                'group' => [
                    'cms-links',
                    'social-icons',
                    'contact'
                ],
                'spacing' => 'wide'
            ]
            
        ]

    ],

    2 => [
        'items' => [
            'wishlist' => [
                'label' => 'icon'
            ],
            'account' => [
                'label' => 'icon',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'icon_text'
            ],
            'search' => [
                'dropdown' => true
            ],
            'cart' => [
                'label' => 'icon',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'icon_text'
            ],
            'comparelist' => [
                'label' => 'icon'
            ],
            'language' => [
                'label' => 'icon',
                'offcanvas-label' => 'icon_text'
            ],
            'currency' => [
                'label' => 'icon'
            ]
        ],
        'classes' => 'centered-logo',
        'desktopLayout' => [
            // '1' => [
            //     '1' => ['cms-links'],
            //     '2' => ['box'],
            //     '3' => ['social-icons']
            // ],
            '2' => [
                '1' => ['search',['categories']],
                '2' => ['logo'],
                '3' => [['cms-megamenu'], 'wishlist', 'comparelist', 'cart', 'account'],
                // 'classes' => 'top-aligned',
                'styles' => [
                    'padding-top' => '25px',
                    'padding-bottom' => '25px'
                ]
            ]
            ],
            'mobileLayout' => [
            // '1' => [
                // '3' => ['social-icons', 'currency', 'language']
            // ],
            '2' => [
                '1' => ['search'],
                '2' => ['logo'],
                '3' => [ 'offcanvas-button'],
                'styles' => [
                    'padding-top' => '10px',
                    'padding-bottom' => '10px'
                ]
            ],
            // '1' => [
            //     3 => ['social-icons'],
            //     'inverted' => 1
            // ]
        ],
        'offcanvasLayout' => [
            [
                'group' => ['signout', 'language', 'currency', 'cart', 'account','comparelist', 'wishlist'],
                // 'horizontal' => true
            ],
            'search',
            [
                'item' => 'categories',
                'stretch' => true
            ],
            'manufacturers',
            'cms-megamenu',
            [
                'group' => [
                    'cms-links',
                    'social-icons',
                    'contact'
                ],
                'spacing' => 'wide'
            ]
            
        ]

    ],
    3 => [
        'items' => [
            'wishlist' => [
                'label' => 'text'
            ],
            'account' => [
                'label' => 'icon_text',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'icon_text'
            ],
            'search' => [
                'dropdown' => true
            ],
            'cart' => [
                'label' => 'text',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'icon_text'
            ],
            'comparelist' => [
                'label' => 'icon_text'
            ],
            'language' => [
                'label' => 'icon',
                'offcanvas-label' => 'icon_text',
                'dropdown' => true
            ],
            'currency' => [
                'label' => 'icon_text'
            ]
        ],
        'desktopLayout' => [
            '2' => [
                '1' => ['logo'],
				// '2' => ['search'],
                // '3' => ['search'],
                'styles' => [
                    'padding-top' => '15px',
                    'padding-bottom' => '0px',

                ]
            ],
            '3' => [
                '1' => [['categories', 'cms-megamenu', 'manufacturers']],
                '3' => ['search', 'wishlist', 'comparelist', 'cart', 'account'],
				'styles' => [
                    'padding-top' => '0px',
                    'padding-bottom' => '15px',
                // 'classes' => 'top-aligned'
				]
            ]
        ],
        
        'mobileLayout' => [
            '2' => [
                '1' => ['logo'],
                '2' => [],
                '3' => ['search', 'offcanvas-button'],
                'styles' => [
                    'padding-top' => '5px',
                    'padding-bottom' => '5px'
                ]
            ],
            // '1' => [
            //     3 => ['social-icons'],
            //     'inverted' => 1
            // ]
        ],
        'offcanvasLayout' => [
            [
                'group' => ['signout', 'cart', 'account', 'comparelist', 'wishlist', 'language', 'currency'],
                // 'horizontal' => true
            ],
            'search',
            [
                'item' => 'categories',
                'stretch' => true
            ],
            'manufacturers',
            'cms-megamenu',
            [
                'group' => [
                    'cms-links',
                    'social-icons',
                    'contact'
                ],
                'spacing' => 'wide'
            ]
            
        ]

    ],
    4 => [
        'items' => [
            'wishlist' => [
                'label' => 'text',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'text'
            ],
            'account' => [
                'label' => 'text',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'text'
            ],
            'search' => [
                'dropdown' => true
            ],
            'cart' => [
                'label' => 'text',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'text'
            ],
            'comparelist' => [
                'label' => 'text',
                'mobile-label' => 'icon',
                'offcanvas-label' => 'text'
            ],
            'language' => [
                'label' => 'text',
                'offcanvas-label' => 'text',
                'dropdown' => false
            ],
            'currency' => [
                'label' => 'text'
            ]
        ],
        'desktopLayout' => [
            // '1' => [
                // '1' => ['cms-links' ],
                // '3' => ['box', 'contact', 'currency', 'language'],
                // 'inverted' => true,
                // 'height' => 'small'
            // ],
            '2' => [
                // '3' => ['logo'], 
                '1' => [['logo'], ['categories'],  'wishlist', 'comparelist', 'cart', 'account', 'search'],
                // 'classes' => 'top-aligned',
                'styles' => [
                    'padding-top' => '25px',
                    'padding-bottom' => '25px',

                ]
            ],
            // '3' => [
            //     '3' => [['cms-megamenu', 'manufacturers'], 'contact']
            //     // 'inverted' => 1,
            //     // 'bg_color' => 'black'
            // ],
        ],
        'mobileLayout' => [
            '2' => [
                '1' => ['logo'],
                '2' => [],
                '3' => ['search', 'offcanvas-button'],
                'styles' => [
                    'padding-top' => '9px',
                    'padding-bottom' => '9px'
                ]
            ],
            // '1' => [
            //     3 => ['social-icons'],
            //     'inverted' => 1
            // ]
        ],
        'offcanvasLayout' => [
            [
                'group' => ['signout', 'cart', 'account', 'wishlist', 'comparelist', 'currency', 'language'],
                'horizontal' => true
            ],
            'search',
            [
                'item' => 'categories',
                'stretch' => true
            ],
            'manufacturers',
            'cms-megamenu',
            [
                'group' => [
                    'cms-links',
                    'social-icons',
                    'contact'
                ],
                'spacing' => 'wide'
            ]
            
        ]

    ]
];