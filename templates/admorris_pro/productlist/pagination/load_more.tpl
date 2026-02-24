{function name="load_btn"}
    {block 'productlist-pagination-load-more-button'}
        {$direction= ($tplScope === "next") ? "more" : "prev"}
        <div class="productlist-pagination__load-wrapper load-{$direction}__wrapper">
            <button type="button" class="productlist-pagination__load-button load-{$direction}__pagination" id="{($direction === "more") ? $filterPagination->getNext()->getUrl(): $filterPagination->getPrev()->getUrl()}">
                <div class="productlist-pagination__load-text load-{$direction}__text">
                    {if $direction === "more"}{lang key="showNext" section="custom"}{else}{lang key="showPrevious" section="custom"}{/if}
                </div>
                <div class="productlist-pagination__load-icon load-{$direction}__icon">
                    {($direction === "more") ? $admIcon->renderIcon('chevronDown', 'icon-content'): $admIcon->renderIcon('chevronUp', 'icon-content')}
                </div>
            </button>
            <div class="productlist-pagination__load-spinner load-{$direction}__spinner">
                <span class="icon-content icon-animated--spin" style="--size: 1">
                    <svg>
                        <use href="#icon-spinner" />
                    </svg>
                </span>
            </div>
        </div>
    {/block}
{/function}

{block 'productlist-pagination-load-more'}
    <div class="productlist-pagination{if $tplScope === "prev"} productlist-pagination--prev{/if} d-flex flex-wrap">
        {if ($tplScope === "next" && $filterPagination->getNext()->getPageNumber() > 0) 
        || ($tplScope === "prev" && $filterPagination->getPrev()->getPageNumber() > 0)
        }
        {load_btn tplScope=$tplScope}
        {/if}
    </div>
{/block}