{function name="overlay"}
  <div class="thrixty-overlay"><span>360°</span></div>
{/function}


{$imageCount = $Artikel->Bilder|count + 1}
{$imageCountDefault = 5}
{if $imageCount == 1}
  <div id="gallery_preview_wrapper" class="product-thumbnails-wrapper">
    <div id="gallery_preview"
      class="product-thumbnails slick-smooth-loading carousel carousel-thumbnails slick-lazy {if $imageCount <= $imageCountDefault}slick-count-default{/if}"
      data-slick-type="gallery_preview">
{/if}
    {* {if $imageCount > $imageCountDefault}
                <button class="slick-prev slick-arrow slick-inital-arrow" aria-label="Previous" type="button" style="">Previous</button>
            {/if} *}
    <div
      class="square square-image js-gallery-images {if $admorris_three_sixty_position === 'prepend'}preview-first first-ml{else}last-mr{/if}">
      <div class="inner">
        {image
          alt=$admorris_three_sixty_image->cAltAttribut
          class="product-image"
          fluid=true
          lazy=true
          webp=false
          src="{$admorris_three_sixty_image->cUrlMini}"
        }
        {overlay}
      </div>
    </div>
{if $imageCount == 1}
      {strip}
        <div class="square square-image js-gallery-images last-mr">
          <div class="inner">
            {image 
              alt=$Artikel->Bilder[0]->cAltAttribut
              class="product-image"
              fluid=true
              lazy=true
              webp=true
              src="{$Artikel->Bilder[0]->cURLKlein}"
            }
          </div>
        </div>
      {/strip}
      {* {if $imageCount > $imageCountDefault}
                <button class="slick-next slick-arrow slick-inital-arrow" aria-label="Next" type="button" style="">Next</button>
            {/if} *}
    </div>
  </div>
{/if}