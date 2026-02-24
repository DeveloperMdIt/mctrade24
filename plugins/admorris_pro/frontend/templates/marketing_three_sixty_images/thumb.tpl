{function name="overlay"}
  <div class="thrixty-overlay"><span>360°</span></div>
{/function}

{if $Einstellungen.bilder.container_verwenden === 'Y'}
  {$width = $Einstellungen.bilder.bilder_artikel_mini_breite}
  {$height = $Einstellungen.bilder.bilder_artikel_mini_hoehe}
{else}
  {$width = null}
  {$height = null}
{/if}

{if $admorris_number_of_images == 1}
  <div class="productdetails-gallery__thumbs">
    <ul id="gallery_preview_wrapper" class="productdetails-gallery__image-thumbs product-thumbnails-wrapper">
{/if}
    <li>
      {image 
        src="{$admorris_three_sixty_image->cUrlMini}"
        alt="{$admorris_three_sixty_image->cAltAttribut}"
        class="productdetails-gallery__thumb-image productdetails-gallery__thumb-image--thrixty"
        lazy=false
        webp=false
        fluid=false
        height=$height
        width=$width
        style="width: var(--article-image-xs-width)"
      }
      {overlay}
    </li>
{if $admorris_number_of_images == 1}
      <li>
        {image src="{$Artikel->Bilder[0]->cURLMini}"
          alt="{$Artikel->Bilder[0]->cAltAttribut|escape:'html':'UTF-8':FALSE}"
          lazy=false
          webp=true
          fluid=false
          width=$width
          height=$height
          class='productdetails-gallery__thumb-image'
          style="width: var(--article-image-xs-width)"
        }
      </li>
    </ul>
  </div>
{/if}