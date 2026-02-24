{*custom*}
{block name='page-index'}

{block name='page-index-include-selection-wizard'}
    {include file='selectionwizard/index.tpl'}
{/block}

{$alignment = $admorris_pro_templateSettings->productSliderContentTextAlignment}


{block name="startseite-produktslider"}
{block name='page-index-boxes'}
    {if isset($StartseiteBoxen) && $StartseiteBoxen|@count > 0}
        {* <hr class="large-v-margin"> *}
        {assign var='moreLink' value=null}
        {assign var='moreTitle' value=null}

        {opcMountPoint id='opc_before_boxes'}

        {foreach name=startboxen from=$StartseiteBoxen item=Box}
            {if isset($Box->Artikel->elemente) && count($Box->Artikel->elemente)>0 && isset($Box->cURL)}
                {if $Box->name === 'TopAngebot'}
                    {lang key="topOffer" section="global" assign='title'}
                    {lang key='showAllTopOffers' section='global' assign='moreTitle'}
                    {lang key="subheadingTopAngebot" section="custom" assign='subheading'}
                {elseif $Box->name === 'Sonderangebote'}
                    {lang key="specialOffer" section="global" assign='title'}
                    {lang key='showAllSpecialOffers' section='global' assign='moreTitle'}
                    {lang key="subheadingSpecialOffer" section="custom" assign='subheading'}

                {elseif $Box->name === 'NeuImSortiment'}
                    {lang key="newProducts" section="global" assign='title'}
                    {lang key='showAllNewProducts' section='global' assign='moreTitle'}
                    {lang key="subheadingNeuImSortiment" section="custom" assign='subheading'}

                {elseif $Box->name === 'Bestseller'}
                    {lang key="bestsellers" section="global" assign='title'}
                    {lang key='showAllBestsellers' section='global' assign='moreTitle'}
                    {lang key="subheadingBestseller" section="custom" assign='subheading'}

                {/if}
                {assign var='moreLink' value=$Box->cURL}
                {include file='snippets/product_slider.tpl' productlist=$Box->Artikel->elemente title=$title hideOverlays=true moreLink=$moreLink moreTitle=$moreTitle subheading=$subheading start=true name=$Box->name}

                {opcMountPoint id="opc_after_box_{$Box->name}"}

            {/if}
        {/foreach}
    {/if}
{/block}
{/block}

{block name='page-index-additional-content'}

{if isset($oNews_arr) && $oNews_arr|@count > 0}
    {opcMountPoint id='opc_before_news'}

    <div class="home-news-slider">
        <hr class="v-spacing{if !$admPro->is_small_container()} v-spacing--lg{/if}">
        
        <div class="home-news-slide__header{if $alignment === 'center'} text-center{/if}">
            <h2 class="home-news-slide__heading">{lang key="news" section="news"}</h2>
            <p>{lang key="whatisnew" section="custom"}</p>
        </div>
        
        <div class="news-panel slick-slider slick-lazy product-slider" itemprop="about" itemscope itemtype="http://schema.org/Blog" data-slick-type="news">
            {foreach $oNews_arr as $newsItem}
                <div class="news-wrapper">
                {include file="blog/preview_vertical.tpl"}
                </div>
            {/foreach}
        </div>

        <div class="product-slider__more{if $alignment === 'center'} text-center{/if}">
            <a class="product-slider__more-button btn btn-primary" href="{get_static_route id='news.php'}" {* style="min-width: 260px" *}>{lang key="showAll"}</a>
        </div>
    </div>

    {* {block name="news-slider-config"}
    <script type="module">
        $(document).ready(function(){
            var sliderArrows = $.evo.options.sliderArrows;
            $('.home-news-slider .slick-slider:not(.slick-initialized)').slick({
                arrows: true,
                prevArrow: sliderArrows.left,
                nextArrow: sliderArrows.right,
                slidesToShow: 3,
                responsive: [
                    {
                        breakpoint: 480, // xs
                        settings: {
                            slidesToShow: 1
                        }
                    },
                    {
                        breakpoint: 768, // sm
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 992, // md
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    }
                ]
            });
        });
    </script>
    {/block} *}
{/if}
{/block}
{/block}