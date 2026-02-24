{block name='blog-details'}
    {block name='blog-details-include-extension'}
        {include file='snippets/extension.tpl'}
    {/block}

    <div class="blog-details">
        {if !empty($cNewsErr)}
            {block name='blog-details-alert'}
                {alert variant="danger"}{lang key='newsRestricted' section='news'}{/alert}
            {/block}
        {else}
            {block name='blog-details-article'}
                <article itemprop="mainEntity" itemscope itemtype="https://schema.org/BlogPosting"{if $admBlogSettings->currentBannerType} itemref="blog-banner"{/if}>
                    <meta itemprop="mainEntityOfPage" content="{$newsItem->getURL()}">
                    {block name='blog-details-heading'}
                        {if !$admBlogSettings->currentBannerType}
                            {opcMountPoint id='opc_before_heading'}
                            <h1 itemprop="headline">
                                {$newsItem->getTitle()}
                            </h1>
                            {include 'blog/details_meta.tpl'}
                        {/if}
                    {/block}

                    {block name='blog-details-article-content'}
                        {opcMountPoint id='opc_before_content'}
                        {row itemprop="articleBody" class="blog-details-content"}
                            {col cols=12}
                                {$newsItem->getContent()}
                            {/col}
                        {/row}
                        {opcMountPoint id='opc_after_content'}
                    {/block}
                    {if isset($Einstellungen.news.news_kommentare_nutzen) && $Einstellungen.news.news_kommentare_nutzen === 'Y'}
                        {block name='blog-details-article-comments'}
                            {if $userCanComment === true}
                                {block name='blog-details-form-comment'}
                                    {block name='blog-details-form-comment-hr-top'}
                                        <hr class="blog-details-hr">
                                    {/block}
                                    {row}
                                        {col cols=12}
                                            {block name='blog-details-form-comment-heading'}
                                                <div class="h2">{lang key='newsCommentAdd' section='news'}</div>
                                            {/block}
                                            {block name='blog-details-form-comment-form'}
                                                {form method="post"
                                                    action="{if !empty($newsItem->getSEO())}{$newsItem->getURL()}{else}{get_static_route id='news.php'}{/if}"
                                                    class="form jtl-validate"
                                                    id="news-addcomment"
                                                    addhoneypot=true
                                                    slide=true}
                                                    {input type="hidden" name="kNews" value=$newsItem->getID()}
                                                    {input type="hidden" name="kommentar_einfuegen" value="1"}
                                                    {input type="hidden" name="n" value=$newsItem->getID()}
                                                    <div class="required-info">{lang key='requiredInfo'}</div>

                                                    {block name='blog-details-form-comment-logged-in'}
                                                        {formgroup
                                                            id="commentText"
                                                            class="{if $nPlausiValue_arr.cKommentar > 0} has-error{/if}"
                                                            label="<strong>{lang key='newsComment' section='news'}</strong>"
                                                            label-for="comment-text"
                                                            label-class="commentForm"
                                                        }
                                                            {if $nPlausiValue_arr.cKommentar > 0}
                                                                <div class="form-error-msg" role="alert" aria-live="assertive" aria-atomic="true"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                                                    {lang key='fillOut' section='global'}
                                                                </div>
                                                            {/if}
                                                            {if $Einstellungen.news.news_kommentare_freischalten === 'Y'}
                                                                <small class="form-text text-muted-util">{lang key='commentWillBeValidated' section='news'}</small>
                                                            {/if}
                                                            {textarea id="comment-text" name="cKommentar" required=true aria-invalid="{if $nPlausiValue_arr.cKommentar > 0}true{else}false{/if}"}{/textarea}
                                                        {/formgroup}
                                                        {row}
                                                            {col md=4 xl=3 class='blog-details-save'}
                                                                {button block=true variant="primary" name="speichern" type="submit"}
                                                                    {lang key='newsCommentSave' section='news'}
                                                                {/button}
                                                            {/col}
                                                        {/row}
                                                    {/block}
                                                {/form}
                                            {/block}
                                        {/col}
                                    {/row}
                                {/block}
                            {else}
                                {block name='blog-details-alert-login'}
                                    {alert variant="warning"}{lang key='newsLogin' section='news'}{/alert}
                                {/block}
                            {/if}
                            {if $comments|count > 0}
                                {block name='blog-details-comments-content'}
                                    {if $newsItem->getURL() !== ''}
                                        {assign var=articleURL value=$newsItem->getURL()}
                                        {assign var=cParam_arr value=[]}
                                    {else}
                                        {assign var=articleURL value='news.php'}
                                        {assign var=cParam_arr value=['kNews'=>$newsItem->getID(),'n'=>$newsItem->getID()]}
                                    {/if}
                                    {block name='blog-details-form-comment-hr-middle'}
                                        <hr class="blog-details-hr">
                                    {/block}
                                    <div id="comments">
                                        {row class="blog-comments-header"}
                                            {col cols="auto"}
                                                {block name='blog-details-comments-content-heading'}
                                                    <div class="h2 section-heading">{lang key='newsComments' section='news'}
                                                        <span itemprop="commentCount">
                                                            {$newsItem->getCommentCount()}
                                                            {if $newsItem->getChildCommentsCount() && $Einstellungen.news.news_kommentare_anzahl_antwort_kommentare_anzeigen === 'Y'}
                                                                ({$newsItem->getChildCommentsCount()})
                                                            {/if}
                                                        </span>
                                                    </div>
                                                {/block}
                                            {/col}
                                            {col cols="12" md=6 class="ml-auto-util"}
                                                {block name='blog-details-include-pagination'}
                                                    {include file='snippets/pagination.tpl' oPagination=$oPagiComments cThisUrl=$articleURL cParam_arr=$cParam_arr noWrapper=true}
                                                {/block}
                                            {/col}
                                        {/row}
                                        {block name='blog-details-comments'}
                                            {listgroup class="blog-details-comments-list list-group-flush"}
                                                {foreach $comments as $comment}
                                                    {listgroupitem class="blog-details-comments-list-item" itemprop="comment"}
                                                        <p>
                                                            {$comment->getName()}, {$comment->getDateCreated()->format('d.m.y H:i')}
                                                        </p>
                                                        {$comment->getText()}
                                                        {foreach $comment->getChildComments() as $childComment}
                                                            <div class="review-reply">
                                                                <span class="subheadline">{lang key='commentReply' section='news'}:</span>
                                                                <blockquote>
                                                                    {$childComment->getText()}
                                                                    <div class="blockquote-footer">{$childComment->getName()}, {$childComment->getDateCreated()->format('d.m.y H:i')}</div>
                                                                </blockquote>
                                                            </div>
                                                        {/foreach}
                                                    {/listgroupitem}
                                                {/foreach}
                                            {/listgroup}
                                        {/block}
                                    </div>
                                {/block}
                            {/if}
                        {/block}
                    {/if}
                </article>
                {if $oNews_arr|count > 0}
                    {block name='blog-details-form-comment-hr-bottom'}
                        <hr class="blog-details-hr">
                    {/block}
                    {block name='blog-details-latest-news'}
                        <div class="h2">{lang key='news' section='news'}</div>
                        {opcMountPoint id='opc_before_news_list'}
                            {block name='blog-details-previews'}
                                {if $admorrisProSettings['blog_layout_type'] === 'gallery'}
                                    <div class="blog-overview__gallery-grid">
                                        {foreach $oNews_arr as $newsItem}
                                            <div class="blog-overview__item">
                                                {include file='blog/preview_vertical.tpl' tplscope='gallery'}
                                            </div>
                                        {/foreach}
                                    </div>
                                {else}
                                    <div class="blog-overview-preview">
                                        {foreach $oNews_arr as $newsItem}
                                            {block name='blog-details-include-preview'}
                                                {include file='blog/preview.tpl' class="blog-overview-preview-item"}
                                            {/block}
                                        {/foreach}
                                    </div>
                                {/if}
                            {/block}
                    {/block}
                {/if}
            {/block}
        {/if}
    </div>
{/block}
