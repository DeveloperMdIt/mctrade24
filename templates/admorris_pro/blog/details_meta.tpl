{block name='blog-details-author'}
    <div class="author-meta mb-3">
        {if empty($newsItem->getDateValidFrom())}
            {assign var=dDate value=$newsItem->getDateCreated()->format('Y-m-d H:i:s')}
        {else}
            {assign var=dDate value=$newsItem->getDateValidFrom()->format('Y-m-d H:i:s')}
        {/if}
        {if $newsItem->getAuthor() !== null}
            {block name='blog-details-include-author'}
                {include file='snippets/author.tpl' oAuthor=$newsItem->getAuthor() dDate=$dDate cDate=$newsItem->getDateValidFrom()->format('d.m.Y')}
            {/block}
        {else}
            {block name='blog-details-noauthor'}
                <div itemprop="author publisher" itemscope itemtype="https://schema.org/Organization" class="d-none">
                    <span itemprop="name">{$meta_publisher}</span>
                    <meta itemprop="logo" content="{$ShopLogoURL}" />
                </div>
                <time itemprop="datePublished" datetime="{$dDate}" class="d-none">{$dDate}</time><span class="creation-date">{$newsItem->getDateValidFrom()->format('d.m.Y')}</span>
            {/block}
        {/if}
        <time itemprop="datePublished" datetime="{$dDate}" class="d-none">{$dDate}</time>
        {if isset($newsItem->getDateCreated()->format('Y-m-d H:i:s'))}<time itemprop="dateModified" class="d-none">{$newsItem->getDateCreated()->format('Y-m-d H:i:s')}</time>{/if}

        {if isset($Einstellungen.news.news_kategorie_unternewsanzeigen) && $Einstellungen.news.news_kategorie_unternewsanzeigen === 'Y' && !empty($oNewsKategorie_arr)}
            {block name='blog-details-sub-news'}
                <span class="news-categorylist">
                    {if $newsItem->getAuthor() === null}/{/if}
                    {foreach $oNewsKategorie_arr as $newsCategory}
                        {link itemprop="articleSection"
                            href="{$newsCategory->getURL()}"
                            title="{$newsCategory->getDescription()|strip_tags|escape:'html'|truncate:60}"
                            class="{if !$newsCategory@last}mr-1{/if} d-inline-block"
                        }
                            {$newsCategory->getName()}
                        {/link}
                    {/foreach}
                </span>
            {/block}
        {/if}

        {block name='blog-details-comments-link'}
            {if $Einstellungen.news.news_kommentare_nutzen === 'Y'}
            {link class="text-decoration-none-util text-nowrap-util" href="#comments" title="{lang key='readComments' section='news'}"}
                /
                <span class="fas fa-comments"></span>
                <span class="sr-only">
                    {if $newsItem->getCommentCount() === 1}
                        {lang key='newsComment' section='news'}
                    {else}
                        {lang key='newsComments' section='news'}
                    {/if}
                </span>
                <span itemprop="commentCount">
                    {$newsItem->getCommentCount()}
                    {if $newsItem->getChildCommentsCount()  && $Einstellungen.news.news_kommentare_anzahl_antwort_kommentare_anzeigen === 'Y'}
                        ({$newsItem->getChildCommentsCount()})
                    {/if}
                </span>
            {/link}
            {/if}
        {/block}
    </div>
{/block}