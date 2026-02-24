{$tplscope = (isset($tplscope)) ? $tplscope : null}
{block name="startseite-blog-preview"}
    {$title = $newsItem->getTitle()|escape:'quotes'}
    <div class="blog-preview blog-preview--vertical{if $tplscope === 'gallery'} blog-preview--gallery{/if}">
        {if !empty($newsItem->getPreviewImage())}
            <a class="blog-preview__image-link" href="{$newsItem->getURL()}">
                {* {image class="blog-preview__image blog-img" src="{$newsItem->getPreviewImage()}" alt="{$newsItem->getTitle()|escape:'quotes'} - {$newsItem->getMetaTitle()|escape:'quotes'}" lazy=true} *}

                {include file='snippets/image.tpl'
                    item=$newsItem
                    class='blog-preview__image blog-img'
                    sizes = '(min-width: 1300px) 25vw, (min-width: 992px) 38vw, (min-width: 768px) 55vw, 100vw'
                    alt="{$title} - {$newsItem->getMetaTitle()|escape:'quotes'}"}
            </a>
        {/if}
        <div class="blog-preview__panel-strap">
            <h3 class="blog-preview__heading h4"><a href="{$newsItem->getURL()}" class="blog-title">{$newsItem->getTitle()}</a></h3>

            {if $tplscope === 'gallery'}
                <div class="blog-preview__data">
                    {assign var='dDate' value=$newsItem->getDateValidFrom()->format('Y-m-d')}
                    {if $newsItem->getAuthor() !== null}
                        <div class="d-none d-sm-inline-block">{include file="snippets/author.tpl" oAuthor=$newsItem->getAuthor() showModal=false}</div>
                    {else}
                        <div itemprop="author publisher" itemscope itemtype="http://schema.org/Organization" class="d-none">
                            <span itemprop="name">{$meta_publisher}</span>
                            <meta itemprop="url" content="{$ShopURL}">
                            <meta itemprop="logo" content="{$ShopURL}/{$ShopLogoURL}">
                        </div>
                    {/if}
                    <time itemprop="dateModified" class="d-none">{$newsItem->getDateCreated()->format('Y-m-d')}</time>
                    <time itemprop="datePublished" datetime="{$dDate}" class="blog-preview__date">{$newsItem->getDateValidFrom()->format('d.m.Y')}</time>
                    {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y' && $newsItem->getCommentCount() > 0}
                        <a class="blog-comments" href="{$newsItem->getURL()}#comments" title="{lang key="readComments" section="news"}">
                            {$admIcon->renderIcon('chat', 'icon-content blog-comments__icon')}
                            <span class="sr-only">
                                {if $newsItem->getCommentCount() == 1}
                                    {lang key="newsComment" section="news"}
                                {else}
                                    {lang key="newsComments" section="news"}
                                {/if}
                            </span>
                            <span class="blog-comments__count"  itemprop="commentCount">{$newsItem->getCommentCount()}</span>
                        </a>
                    {/if}
                </div>
            {/if}

            <p class="blog-preview__text" itemprop="description">
                {if $newsItem->getPreview()|strip_tags|strlen > 0}
                    {$newsItem->getPreview()|strip_tags}
                {else}
                    {$newsItem->getContent()|strip_tags|truncate:500:''}
                {/if}
            </p>
            
            {link href=$newsItem->getURL() title=$title}
                {lang key='moreLink' section='news'}
                <i class="fas fa-long-arrow-alt-right icon-mr-2"></i>
            {/link}
        </div>
    </div>
{/block}
