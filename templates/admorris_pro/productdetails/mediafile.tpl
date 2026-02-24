{*custom*}

{block name='productdetails-mediafile'}

{if !empty($Artikel->oMedienDatei_arr)}
    {assign var=mp3List value=false}
    {assign var=titles value=false}
    <div class="mediafile-grid{* row *}">
    {foreach $Artikel->oMedienDatei_arr as $oMedienDatei}
        {if ($mediaType->name == $oMedienDatei->cMedienTyp
            && $oMedienDatei->cAttributTab|count_characters == 0)
            || ($oMedienDatei->cAttributTab|count_characters > 0
                && $mediaType->name|@seofy == $oMedienDatei->cAttributTab|@seofy)}
            {if $oMedienDatei->nErreichbar == 0}
                {* <div class="col-12"> *}
                    <p class="box_error">
                        {lang key='noMediaFile' section='errorMessages'}
                    </p>
                {* </div> *}
            {else}
                {assign var=cName value=$oMedienDatei->cName}
                {assign var=titles value=$titles|cat:$cName}
                {if !$oMedienDatei@last}
                    {assign var=titles value=$titles|cat:'|'}
                {/if}

                {* Images *}
                {if $oMedienDatei->nMedienTyp == 1}
                    {* <div class="col-12"> *}
                        {* <div class="panel-wrap"> *}
                            <div class="card mediafile-panel">
                                <div class="card-header"><h4 class="">{$oMedienDatei->cName}</h4></div>
                                <div class="card-body">
                                    <p>{$oMedienDatei->cBeschreibung}</p>
                                    {if isset($oMedienDatei->oMedienDateiAttribut_arr) && $oMedienDatei->oMedienDateiAttribut_arr|@count > 0}
                                        {foreach $oMedienDatei->oMedienDateiAttribut_arr as $oAttribut}
                                            {if $oAttribut->cName === 'img_alt'}
                                                {assign var=cMediaAltAttr value=$oAttribut->cWert}
                                            {/if}
                                        {/foreach}
                                    {/if}
                                    {if !empty($oMedienDatei->cPfad)}
                                        <img alt="{if isset($cMediaAltAttr)}{$cMediaAltAttr}{/if}" src="{$ShopURL}/{$smarty.const.PFAD_MEDIAFILES}{$oMedienDatei->cPfad}" class="img-fluid" />
                                    {elseif !empty($oMedienDatei->cURL)}
                                        <img alt="{if isset($cMediaAltAttr)}{$cMediaAltAttr}{/if}" src="{$oMedienDatei->cURL}" class="img-fluid" />
                                    {/if}
                                </div>
                            </div>
                        {* </div> *}
                    {* </div> *}
                    {* Audio *}
                {elseif $oMedienDatei->nMedienTyp == 2}
                    {if $oMedienDatei->cName|strlen > 1}
                        {* <div class="col-12"> *}
                            {* <div class="panel-wrap"> *}
                                <div class="card mediafile-panel">
                                    <div class="card-header"><h4 class="">{$oMedienDatei->cName}</h4></div>
                                    <div class="card-body">
                                        <p>{$oMedienDatei->cBeschreibung}</p>
                                        {* Music *}
                                        {if $oMedienDatei->cPfad|strlen > 1 || $oMedienDatei->cURL|strlen > 1}
                                            {assign var=audiosrc value=$oMedienDatei->cURL}
                                            {if $oMedienDatei->cPfad|strlen > 1}
                                                {assign var=audiosrc value=$smarty.const.PFAD_MEDIAFILES|cat:$oMedienDatei->cPfad}
                                            {/if}
                                            {if $audiosrc|strlen > 1}
                                                <audio controls controlsList="nodownload">
                                                    <source src="{$audiosrc}" type="audio/mpeg">
                                                    {lang key='audioTagNotSupported' section='errorMessages'}
                                                </audio>
                                            {/if}
                                        {/if}
                                    </div>
                                </div>
                            {* </div> *}
                        {* </div> *}
                        {* Audio *}
                    {/if}

                    {* Video *}
                    {elseif $oMedienDatei->nMedienTyp === 3}
                        {block name='productdetails-mediafile-video'}
                            {if ($oMedienDatei->videoType === 'mp4'
                            || $oMedienDatei->videoType === 'webm'
                            || $oMedienDatei->videoType === 'ogg')}
                                {card class="mediafiles-video" title-text=$oMedienDatei->cName}
                                    {row}
                                        {col class="mediafiles-description" cols=12}
                                            {$oMedienDatei->cBeschreibung}
                                        {/col}
                                        {col cols=12}
                                            {include 'snippets/video.tpl' video=$oMedienDatei->video}
                                        {/col}
                                    {/row}
                                {/card}
                            {else}
                                {lang key='videoTypeNotSupported' section='errorMessages'}
                            {/if}
                        {/block}
                {* Sonstiges *}
                {elseif $oMedienDatei->nMedienTyp == 4}
                    {* <div class="col-12"> *}
                        {* <div class="panel-wrap"> *}
                            <div class="card mediafile-panel">
                                <div class="card-header"><h4 class="">{$oMedienDatei->cName}</h4></div>
                                <div class="card-body">
                                    <p>{$oMedienDatei->cBeschreibung}</p>
                                    {if !empty($oMedienDatei->video)}
                                        {include 'snippets/video.tpl' video=$oMedienDatei->video class='yt-container'}
                                    {else}
                                        {if isset($oMedienDatei->oEmbed) && $oMedienDatei->oEmbed->code}
                                            {$oMedienDatei->oEmbed->code}
                                        {/if}
                                        {if !empty($oMedienDatei->cPfad)}
                                            <p>
                                                <a href="{$ShopURL}/{$smarty.const.PFAD_MEDIAFILES}{$oMedienDatei->cPfad}" target="_blank">{$oMedienDatei->cName}</a>
                                            </p>
                                        {elseif !empty($oMedienDatei->cURL)}
                                            <p>
                                                <a href="{$oMedienDatei->cURL}" target="_blank">{$admIcon->renderIcon('externalLink', 'icon-content icon-content--default')} {$oMedienDatei->cName}</a>
                                            </p>
                                        {/if}
                                    {/if}
                                </div>
                            </div>
                        {* </div> *}
                    {* </div> *}
                    {* PDF *}
                {elseif $oMedienDatei->nMedienTyp == 5}
                    {* <div class="col-12"> *}
                        {* <div class="panel-wrap"> *}
                            <div class="card mediafile-panel">
                                <div class="card-header"><h4 class="">{$oMedienDatei->cName}</h4></div>
                                <div class="card-body">
                                    <p>{$oMedienDatei->cBeschreibung}</p>
                                    {if !empty($oMedienDatei->cPfad)}
                                        <a href="{$ShopURL}/{$smarty.const.PFAD_MEDIAFILES}{$oMedienDatei->cPfad}" target="_blank">
                                            <img alt="PDF" src="{$ShopURL}/{$smarty.const.PFAD_BILDER}intern/file-pdf.png" />
                                        </a>
                                        <br />
                                        <a href="{$ShopURL}/{$smarty.const.PFAD_MEDIAFILES}{$oMedienDatei->cPfad}" target="_blank">
                                            {$oMedienDatei->cName}
                                        </a>
                                    {elseif !empty($oMedienDatei->cURL)}
                                        <a href="{$oMedienDatei->cURL}" target="_blank"><img alt="PDF" src="{$ShopURL}/{$smarty.const.PFAD_BILDER}intern/file-pdf.png" /></a>
                                        <br />
                                        <a href="{$oMedienDatei->cURL}" target="_blank">{$oMedienDatei->cName}</a>
                                    {/if}
                                </div>
                            </div>
                        {* </div> *}
                    {* </div> *}
                {/if}
            {/if}
        {/if}
    {/foreach}
    </div>
{/if}
{/block}