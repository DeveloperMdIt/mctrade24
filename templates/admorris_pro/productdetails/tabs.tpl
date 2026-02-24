{*custom*}
{block name='productdetails-tabs'}
    {block name='productdetails-tabs-settings'}
        {$tabanzeige = $Einstellungen.artikeldetails.artikeldetails_tabs_nutzen !== 'N'}
        {$showProductWeight = false}
        {$showShippingWeight = false}
        {if isset($Artikel->cArtikelgewicht) && $Artikel->fArtikelgewicht > 0
            && $Einstellungen.artikeldetails.artikeldetails_artikelgewicht_anzeigen === 'Y'}
            {$showProductWeight = true}
        {/if}
        {if isset($Artikel->cGewicht) && $Artikel->fGewicht > 0
            && $Einstellungen.artikeldetails.artikeldetails_gewicht_anzeigen === 'Y'}
            {$showShippingWeight = true}
        {/if}
        {$dimension = $Artikel->getDimension()}
        {$funcAttr = $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_ATTRIBUTEANHAENGEN]|default:0}
        {* {$showAttributesTable = ($Einstellungen.artikeldetails.merkmale_anzeigen === 'Y'
            && !empty($Artikel->oMerkmale_arr) || $showProductWeight || $showShippingWeight
            || $Einstellungen.artikeldetails.artikeldetails_abmessungen_anzeigen === 'Y'
            && (!empty($dimension['length']) || !empty($dimension['width']) || !empty($dimension['height']))
            || isset($Artikel->cMasseinheitName) && isset($Artikel->fMassMenge) && $Artikel->fMassMenge > 0
            && $Artikel->cTeilbar !== 'Y' && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1)
            || ($Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y' || $funcAttr == 1)
            && !empty($Artikel->Attribute))} *}
        {* Attributes table custom:
        Because manufacturer, category and product-number are now located in the table too it can be shown always
        *}
        {$showAttributesTable = true}
        {* /custom attributes table*}
        {$useDescriptionWithMediaGroup = ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YA'
            && $Artikel->cMedienDateiAnzeige !== 'tab') || $Artikel->cMedienDateiAnzeige === 'beschreibung')
            && !empty($Artikel->getMediaTypes()))}
        {$useDescription = (($Artikel->cBeschreibung|strlen > 0) || $useDescriptionWithMediaGroup || $showAttributesTable)}
        {$useDownloads = (isset($Artikel->oDownload_arr) && $Artikel->oDownload_arr|@count > 0)}
        {$useVotes = $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
        {$useQuestionOnItem = $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'Y'}
        {$usePriceFlow = ($Einstellungen.preisverlauf.preisverlauf_anzeigen === 'Y' && $bPreisverlauf)}
        {$useAvailabilityNotification = ($verfuegbarkeitsBenachrichtigung === 1)}
        {$useMediaGroup = ((($Einstellungen.artikeldetails.mediendatei_anzeigen === 'YM'
            && $Artikel->cMedienDateiAnzeige !== 'beschreibung') || $Artikel->cMedienDateiAnzeige === 'tab')
            && !empty($Artikel->getMediaTypes()))}
        {$hasVotesHash = isset($smarty.get.ratings_nPage)
            || isset($smarty.get.bewertung_anzeigen)
            || isset($smarty.get.ratings_nItemsPerPage)
            || isset($smarty.get.ratings_nSortByDir)
            || isset($smarty.get.btgsterne)}
        {section name=iterator start=1 loop=11}
            {$tab = tab}
            {$tabname = $tab|cat:$smarty.section.iterator.index|cat:" name"}
            {$tabinhalt = $tab|cat:$smarty.section.iterator.index|cat:" inhalt"}
            {if isset($Artikel->AttributeAssoc[$tabname]) && $Artikel->AttributeAssoc[$tabname]
                && $Artikel->AttributeAssoc[$tabinhalt]}
                {$separatedTabs[{$tabname|replace:' ':'-'}] = [
                'id'      => {$tabname|replace:' ':'-'},
                'name'   => {$Artikel->AttributeAssoc[$tabname]},
                'content' => {$Artikel->AttributeAssoc[$tabinhalt]}
                ]}
            {/if}
        {/section}
        {$setActiveClass = [
            'description'    => (!$hasVotesHash),
            'downloads'      => (!$hasVotesHash && !$useDescription),
            'separatedTabs'  => (!$hasVotesHash && !$useDescription && !$useDownloads),
            'votes'          => ($hasVotesHash || !$useDescription && !$useDownloads && empty($separatedTabs)),
            'questionOnItem' => (!$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs) && !$useVotes),
            'priceFlow'      => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs)
                && !$useQuestionOnItem),
            'availabilityNotification' => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads
                && empty($separatedTabs) && !$useQuestionOnItem && !$usePriceFlow),
            'mediaGroup' => (!$useVotes && !$hasVotesHash && !$useDescription && !$useDownloads && empty($separatedTabs)
                && !$useQuestionOnItem && !$usePriceFlow && !$useAvailabilityNotification)
        ]}
        {if !empty($smarty.get.quickView)}
            {$quickViewIdPostfix = '-quickview'}
        {else}
            {$quickViewIdPostfix = ''}
        {/if}
    {/block}
    {block name='productdetails-tabs-content'}
        {if $useDescription || $useDownloads || $useDescriptionWithMediaGroup || $useVotes || $useQuestionOnItem || $usePriceFlow
            || $useAvailabilityNotification || $useMediaGroup || !empty($separatedTabs)}
            {opcMountPoint id='opc_before_tabs'}
            {if $tabanzeige && !$isMobile}
                {block name='productdetails-tabs-tabs'}
                    {* {container class="{if $Einstellungen.template.theme.left_sidebar === 'Y' && $boxesLeftActive}container-plus-sidebar{/if}"} *}
                        {* <nav class="tab-navigation"> *}
                        {tabs id="product-tabs"|cat:$quickViewIdPostfix class="productdetails-tabs"}
                        {if $useDescription}
                            {block name='productdetails-tabs-tab-description'}
                                {tab title="{lang key='description' section='productDetails'}" active=$setActiveClass.description id="description"|cat:$quickViewIdPostfix}
                                    {block name='productdetails-tabs-tab-description-content'}
                                        {* custom - sidebar layout *}
                                        <div class="productdetails-tabs__description-layout sidebar-layout" style="--sidebar-width: 30rem; --sidebar-gap: 2lh 8.33%;">
                                            {block name='tab-description-media-types'}
                                                <div class="sidebar-layout__main">
                                                    {opcMountPoint id='opc_before_desc'}
                                                    <div class="productdetails-tabs__description-content desc">
                                                        {$Artikel->cBeschreibung}
                                                    </div>
                                                    {if $useDescriptionWithMediaGroup}
                                                        <div class="mediafiles">
                                                            {foreach $Artikel->getMediaTypes() as $mediaType}
                                                                <div class="h3">{$mediaType->name}</div>
                                                                <div class="media">
                                                                    {include file='productdetails/mediafile.tpl'}
                                                                </div>
                                                            {/foreach}
                                                        </div>
                                                    {/if}
                                                    {opcMountPoint id='opc_after_desc'}
                                                </div>
                                            {/block}
                                            {block name='productdetails-tabs-tab-description-include-attributes'}
                                                <div class="productdetails-tabs__description-attributes product-attributes sidebar-layout__sidebar">
                                                    {include file='productdetails/attributes.tpl' 
                                                        tplscope='details'
                                                        showProductWeight=$showProductWeight 
                                                        showShippingWeight=$showShippingWeight
                                                        dimension=$dimension 
                                                        showAttributesTable=$showAttributesTable
                                                    }
                                                    {include file='productdetails/gpsr.tpl'}
                                                </div>
                                            {/block}
                                        </div>
                                    {/block}
                                {/tab}
                            {/block}
                        {/if}

                        {if $useDownloads}
                            {block name='productdetails-tabs-tab-downloads'}
                                {tab title="{lang section='productDownloads' key='downloadSection'}" active=$setActiveClass.downloads id="downloads"|cat:$quickViewIdPostfix}
                                    {opcMountPoint id='opc_before_download'}
                                    {include file='productdetails/download.tpl'}
                                    {opcMountPoint id='opc_after_download'}
                                {/tab}
                            {/block}
                        {/if}

                        {if !empty($separatedTabs)}
                            {block name='productdetails-tabs-tab-separated-tabs'}
                                {foreach $separatedTabs as $separatedTab}
                                    {tab title=$separatedTab.name active=$setActiveClass.separatedTabs && $separatedTab@first id=$separatedTab.name|seofy|cat:$quickViewIdPostfix}
                                        {opcMountPoint id='opc_before_separated_'|cat:$separatedTab.id}
                                        {$separatedTab.content}
                                        {opcMountPoint id='opc_after_separated_'|cat:$separatedTab.id}
                                    {/tab}
                                {/foreach}
                            {/block}
                        {/if}

                        {if $useVotes}
                            {block name='productdetails-tabs-tab-votes'}
                                {tab title="{lang key='Votes'}" active=$setActiveClass.votes
                                        id="votes"|cat:$quickViewIdPostfix}
                                    {opcMountPoint id='opc_before_tab_votes'}
                                    {include file='productdetails/reviews.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}
                                    {opcMountPoint id='opc_after_tab_votes'}
                                {/tab}
                            {/block}
                        {/if}

                        {if $useQuestionOnItem && empty($smarty.get.quickView)}
                            {block name='productdetails-tabs-tab-question-on-item'}
                                {tab title="{lang key='productQuestion' section='productDetails'}" active=$setActiveClass.questionOnItem id="questionOnItem"|cat:$quickViewIdPostfix}
                                    {opcMountPoint id='opc_before_tab_question'}
                                    {include file='productdetails/question_on_item.tpl' position="tab"}
                                    {opcMountPoint id='opc_after_tab_question'}
                                {/tab}
                            {/block}
                        {/if}

                        {if $usePriceFlow}
                            {block name='productdetails-tabs-tab-price-flow'}
                                {tab title="{lang key='priceFlow' section='productDetails'}" active=$setActiveClass.priceFlow id="priceFlow"|cat:$quickViewIdPostfix}
                                    {opcMountPoint id='opc_before_tab_price_history'}
                                    {include file='productdetails/price_history.tpl'}
                                    {opcMountPoint id='opc_after_tab_price_history'}
                                {/tab}
                            {/block}
                        {/if}

                        {if $useAvailabilityNotification && empty($smarty.get.quickView)}
                            {block name='productdetails-tabs-tab-availability-notification'}
                                {tab title="{lang key='notifyMeWhenProductAvailableAgain'}" active=$setActiveClass.availabilityNotification id="availabilityNotification"|cat:$quickViewIdPostfix}
                                    {opcMountPoint id='opc_before_tab_availability'}
                                    {include file='productdetails/availability_notification_form.tpl' position='tab' tplscope='artikeldetails'}
                                    {opcMountPoint id='opc_after_tab_availability'}
                                {/tab}
                            {/block}
                        {/if}

                        {if $useMediaGroup}
                            {block name='productdetails-tabs-tab-mediagroup'}
                                {foreach $Artikel->getMediaTypes() as $mediaType}
                                    {$cMedienTypId = $mediaType->name|seofy}
                                    {tab title="{$mediaType->name} ({$mediaType->count})"
                                            active=$setActiveClass.mediaGroup
                                            && $mediaType@first id=$cMedienTypId|cat:$quickViewIdPostfix}
                                        {include file='productdetails/mediafile.tpl'}
                                    {/tab}
                                {/foreach}
                            {/block}
                        {/if}
                        {/tabs}
                        {* </nav> *}
                    {* {/container} *}
                {/block}
            {else}
                {block name='productdetails-tabs-no-tabs'}
                    {* {container class="{if $Einstellungen.template.theme.left_sidebar === 'Y' && $boxesLeftActive}container-plus-sidebar{/if}"} *}
                        <div class="stack" id="tabAccordion"{$quickViewIdPostfix}>
                            {if $useDescription}
                                {block name='productdetails-tabs-description'}
                                    {card no-body=true}
                                        {cardheader class="h4" id="tab-description-head"|cat:$quickViewIdPostfix}
                                            <button 
                                                class="border-0 d-flex w-100"
                                                data-toggle="collapse"
                                                data-target="#tab-description{$quickViewIdPostfix}"
                                                aria-expanded="{if $setActiveClass.description}true{else}false{/if}"
                                                aria-controls="tab-description{$quickViewIdPostfix}"
                                            >
                                                {lang key='description' section='productDetails'}
                                            </button>
                                        {/cardheader}
                                        {collapse id="tab-description"|cat:$quickViewIdPostfix
                                                visible=$setActiveClass.description
                                            aria=["labelledby"=>"tab-description-head"|cat:$quickViewIdPostfix]
                                        }
                                            {cardbody class="stack"}
                                                {block name='productdetails-tabs-card-description'}
                                                    <div class="productdetails-tabs__description-layout sidebar-layout" style="--sidebar-width: 30rem; --sidebar-gap: 2lh 8.33%;">
                                                        <div class="sidebar-layout__main">
                                                            {block name='productdetails-tabs-card-description-content'}
                                                                {opcMountPoint id='opc_before_desc'}
                                                                
                                                                <div class="desc">
                                                                    {$Artikel->cBeschreibung}
                                                                    {if $useDescriptionWithMediaGroup}
                                                                        {if $Artikel->cBeschreibung|strlen > 0}
                                                                            <hr>
                                                                        {/if}
                                                                        {foreach $Artikel->getMediaTypes() as $mediaType}
                                                                            <div class="media">
                                                                                {block name='productdetails-tabs-description-include-mediafile'}
                                                                                    {include file='productdetails/mediafile.tpl'}
                                                                                {/block}
                                                                            </div>
                                                                        {/foreach}
                                                                    {/if}
                                                                </div>
                                                                {opcMountPoint id='opc_after_desc'}
                                                            {/block}
                                                        </div>
                                                        <div class="productdetails-tabs__description-attributes product-attributes sidebar-layout__sidebar">
                                                            {block name='productdetails-tabs-card-description-attributes'}
                                                                {block name='productdetails-tabs-include-attributes'}
                                                                    <div class="productdetails-tabs__description-attributes product-attributes">
                                                                        {include file='productdetails/attributes.tpl' tplscope='details'
                                                                            showProductWeight=$showProductWeight 
                                                                            showShippingWeight=$showShippingWeight
                                                                            dimension=$dimension 
                                                                            showAttributesTable=$showAttributesTable
                                                                        }
                                                                        {include file='productdetails/gpsr.tpl'}
                                                                    </div>
                                                                {/block}
                                                            {/block}
                                                        </div>
                                                    </div>
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if $useDownloads}
                                {block name='productdetails-tabs-downloads'}
                                    {card no-body=true}
                                        {cardheader class="h4" id="tab-downloads-head"|cat:$quickViewIdPostfix}
                                            <button 
                                                class="border-0 d-flex w-100"
                                                data-toggle="collapse"
                                                data-target="#tab-downloads{$quickViewIdPostfix}"
                                                aria-expanded="{if $setActiveClass.downloads}true{else}false{/if}"
                                                aria-controls="tab-downloads{$quickViewIdPostfix}"
                                            >
                                                {lang section='productDownloads' key='downloadSection'}
                                            </button>
                                        {/cardheader}
                                        {collapse id="tab-downloads"|cat:$quickViewIdPostfix
                                            visible=$setActiveClass.downloads
                                            aria=["labelledby"=>"tab-downloads-head"|cat:$quickViewIdPostfix]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-include-download'}
                                                    {opcMountPoint id='opc_before_download'}
                                                    {include file='productdetails/download.tpl'}
                                                    {opcMountPoint id='opc_after_download'}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if !empty($separatedTabs)}
                                {block name='productdetails-tabs-separated-tabs'}
                                    {foreach $separatedTabs as $separatedTab}
                                        {$separatedTabId = $separatedTab.name|seofy}
                                        {card no-body=true}
                                            {cardheader class="h4" id="tab-{$separatedTabId}-head"|cat:$quickViewIdPostfix}
                                                <button 
                                                    class="border-0 d-flex w-100"
                                                    data-toggle="collapse"
                                                    data-target="#tab-{$separatedTabId}{$quickViewIdPostfix}"
                                                    aria-expanded="{if $setActiveClass.separatedTabs && $separatedTab@first}true{else}false{/if}"
                                                    aria-controls="tab-{$separatedTabId}{$quickViewIdPostfix}"
                                                >
                                                    {$separatedTab.name}
                                                </button>
                                            {/cardheader}
                                            {collapse id="tab-{$separatedTabId}"|cat:$quickViewIdPostfix
                                                visible=($setActiveClass.separatedTabs && $separatedTab@first)
                                                aria=["labelledby"=>"tab-{$separatedTabId}-head"|cat:$quickViewIdPostfix]
                                            }
                                                {cardbody}
                                                    {opcMountPoint id='opc_before_separated_'|cat:$separatedTab.id}
                                                    {$separatedTab.content}
                                                    {opcMountPoint id='opc_after_separated_'|cat:$separatedTab.id}
                                                {/cardbody}
                                            {/collapse}
                                        {/card}
                                    {/foreach}
                                {/block}
                            {/if}

                            {if $useVotes}
                                {block name='productdetails-tabs-votes'}
                                    {card no-body=true }
                                        {cardheader class="h4" id="tab-votes-head"|cat:$quickViewIdPostfix}
                                            <button 
                                                class="border-0 d-flex w-100"
                                                data-toggle="collapse"
                                                data-target="#tab-votes{$quickViewIdPostfix}"
                                                aria-expanded="{if $setActiveClass.votes}true{else}false{/if}"
                                                aria-controls="tab-votes{$quickViewIdPostfix}"
                                            >
                                                {lang key='Votes'}
                                            </button>
                                        {/cardheader}
                                        {collapse id="tab-votes"|cat:$quickViewIdPostfix visible=$setActiveClass.votes
                                            aria=["labelledby"=>"tab-votes-head"|cat:$quickViewIdPostfix]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-include-reviews'}
                                                    {opcMountPoint id='opc_before_tab_votes'}
                                                    {include file='productdetails/reviews.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}
                                                    {opcMountPoint id='opc_after_tab_votes'}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if $useQuestionOnItem}
                                {block name='productdetails-tabs-question-on-item'}
                                    {card no-body=true}
                                        {cardheader class="h4" id="tab-question-head"|cat:$quickViewIdPostfix}
                                            <button 
                                                class="border-0 d-flex w-100"
                                                data-toggle="collapse"
                                                data-target="#tab-questionOnItem{$quickViewIdPostfix}"
                                                aria-expanded="{if $setActiveClass.questionOnItem}true{else}false{/if}"
                                                aria-controls="tab-questionOnItem{$quickViewIdPostfix}"
                                            >
                                                {lang key='productQuestion' section='productDetails'}
                                            </button>
                                        {/cardheader}
                                        {collapse id="tab-questionOnItem"|cat:$quickViewIdPostfix
                                            visible=$setActiveClass.questionOnItem
                                            aria=["labelledby"=>"tab-question-head"|cat:$quickViewIdPostfix]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-include-question-on-item'}
                                                    {opcMountPoint id='opc_before_tab_question'}
                                                    {include file='productdetails/question_on_item.tpl' position="tab"}
                                                    {opcMountPoint id='opc_after_tab_question'}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if $usePriceFlow}
                                {block name='productdetails-tabs-price-flow'}
                                    {card no-body=true}
                                        {cardheader class="h4" id="tab-priceFlow-head"|cat:$quickViewIdPostfix}
                                            <button 
                                                class="border-0 d-flex w-100"
                                                data-toggle="collapse"
                                                data-target="#tab-priceFlow{$quickViewIdPostfix}"
                                                aria-expanded="{if $setActiveClass.priceFlow}true{else}false{/if}"
                                                aria-controls="tab-priceFlow{$quickViewIdPostfix}"
                                            >
                                                {lang key='priceFlow' section='productDetails'}
                                            </button>
                                        {/cardheader}
                                        {collapse id="tab-priceFlow"|cat:$quickViewIdPostfix
                                            visible=$setActiveClass.priceFlow
                                            aria=["labelledby"=>"tab-priceFlow-head"|cat:$quickViewIdPostfix]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-include-price-history'}
                                                    {opcMountPoint id='opc_before_tab_price_history'}
                                                    {include file='productdetails/price_history.tpl'}
                                                    {opcMountPoint id='opc_after_tab_price_history'}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if $useAvailabilityNotification}
                                {block name='productdetails-tabs-availability-notification'}
                                    {card no-body=true}
                                        {cardheader class="h4" id="tab-availabilityNotification-head"|cat:$quickViewIdPostfix}
                                            <button 
                                                class="border-0 d-flex w-100"
                                                data-toggle="collapse"
                                                data-target="#tab-availabilityNotification{$quickViewIdPostfix}"
                                                aria-expanded="{if $setActiveClass.availabilityNotification}true{else}false{/if}"
                                                aria-controls="tab-availabilityNotification{$quickViewIdPostfix}"
                                            >
                                                {lang key='notifyMeWhenProductAvailableAgain'}
                                            </button>
                                        {/cardheader}
                                        {collapse id="tab-availabilityNotification"|cat:$quickViewIdPostfix
                                            visible=$setActiveClass.availabilityNotification
                                            aria=["labelledby"=>"tab-availabilityNotification-head"|cat:$quickViewIdPostfix]
                                        }
                                            {cardbody}
                                                {block name='productdetails-tabs-include-availability-notification-form'}
                                                    {opcMountPoint id='opc_before_tab_availability'}
                                                    {include file='productdetails/availability_notification_form.tpl' position='tab' tplscope='artikeldetails'}
                                                    {opcMountPoint id='opc_after_tab_availability'}
                                                {/block}
                                            {/cardbody}
                                        {/collapse}
                                    {/card}
                                {/block}
                            {/if}

                            {if $useMediaGroup}
                                {block name='productdetails-tabs-media-gorup'}
                                    {foreach $Artikel->getMediaTypes() as $mediaType}
                                        {$cMedienTypId = $mediaType->name|seofy}
                                        {card no-body=true}
                                            {cardheader class="h4" id="tab-{$cMedienTypId}-head"|cat:$quickViewIdPostfix}
                                                <button 
                                                    class="border-0 d-flex w-100"
                                                    data-toggle="collapse"
                                                    data-target="#tab-{$cMedienTypId}{$quickViewIdPostfix}"
                                                    aria-expanded="{if $setActiveClass.mediaGroup && $mediaType@first}true{else}false{/if}"
                                                    aria-controls="tab-{$cMedienTypId}{$quickViewIdPostfix}"
                                                >
                                                    {$mediaType->name}
                                                </button>
                                            {/cardheader}
                                            {collapse id="tab-{$cMedienTypId}"|cat:$quickViewIdPostfix
                                                visible=($setActiveClass.mediaGroup && $mediaType@first)
                                                aria=["labelledby"=>"tab-{$cMedienTypId}-head"|cat:$quickViewIdPostfix]
                                            }
                                                {cardbody}
                                                    {block name='productdetails-tabs-include-mediafile'}
                                                        {include file='productdetails/mediafile.tpl'}
                                                    {/block}
                                                {/cardbody}
                                            {/collapse}
                                        {/card}
                                    {/foreach}
                                {/block}
                            {/if}
                        </div>
                    {* {/container} *}
                {/block}
            {/if}
        {/if}
    {/block}
{/block}