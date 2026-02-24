{extends file="file:[admPro]marketing_popup/popup_base.tpl"}

{$popupHasImage = true}

{block 'admorris-pro-popup-classname'}newsletter-popup{/block}

{block name='admorris-pro-popup-modal-attributes'}
  aria-describedby="admorris-newsletter-popup-title"
{/block}

{block name='admorris-pro-popup-content'}
  <div class="admorris-popup-form">
    <form method="post" action="newsletter.php" class="newsletter-modal__form">
      <h3 id="admorris-newsletter-popup-title" style="color: {$styleColor};">
        {$contentTitle}
      </h3>
      <p>{$contentText1}</p>
      <fieldset>
        {$jtl_token}
        <input type="hidden" name="abonnieren" value="1" />
        <div class="form-group">
          {$Kunde = null}
          {if isset($smarty.session.Kunde)}
            {$Kunde = $smarty.session.Kunde}
          {/if}
          {if $contentSalutation == 1 || $contentFirstName == 1 || $contentLastName == 1}
            {$count = 0}
            {if $contentSalutation == 1}{$count = $count + 1}{/if}
            {if $contentFirstName == 1}{$count = $count + 1}{/if}
            {if $contentLastName == 1}{$count = $count + 1}{/if}
            <div class="row">
              {if $contentSalutation == 1}
                <div class="col {if $count == 2}col-md-6{else}col-md-12{/if}">
                  <div class="newsletter-input-group">
                    <label class="control-label sr-only" for="newsletter_salutation">{lang key='emailadress'}</label>
                    {select name="cAnrede" id="newsletter_salutation" class='custom-select' required=true}
                    <option value="" selected="selected">{lang key='noSalutation'}</option>
                    <option value="w">{lang key='salutationW'}</option>
                    <option value="m">{lang key='salutationM'}</option>
                    <option value="d">{lang key='salutationD' section='custom'}</option>
                    {/select}
                  </div>
                </div>
              {/if}
              {if $contentFirstName == 1}
                <div class="col {if $count == 1}col-md-12{else}col-md-6{/if}">
                  <div class="newsletter-input-group">
                    <label class="control-label sr-only"
                      for="newsletter_firstname">{lang key='newsletterfirstname' section='newsletter'}</label>
                    <input type="text" size="20" name="cVorname" id="newsletter_firstname" class="form-control" required
                      placeholder="{lang key='newsletterfirstname' section='newsletter'}"
                      value="{if isset($Kunde) && isset($Kunde->cVorname)}{$Kunde->cVorname}{/if}">
                  </div>
                </div>
              {/if}
              {if $contentLastName == 1}
                <div class="col {if $count == 1}col-md-12{else}col-md-6{/if}">
                  <div class="newsletter-input-group">
                    <label class="control-label sr-only"
                      for="newsletter_lastname">{lang key='newsletterlastname' section='newsletter'}</label>
                    <input type="text" size="20" name="cNachname" id="newsletter_lastname" class="form-control"
                      placeholder="{lang key='newsletterlastname' section='newsletter'}"
                      value="{if isset($Kunde) && isset($Kunde->cNachname)}{$Kunde->cNachname}{/if}">
                  </div>
                </div>
              {/if}
              <div class="col col-md-12">
                <div class="newsletter-input-group">
                  <label class="control-label sr-only" for="newsletter_popup_email">Email-Adresse</label>
                  <input type="email" size="20" name="cEmail" id="newsletter_popup_email" class="form-control"
                    placeholder="{lang key="emailadress"}"
                    value="{if isset($Kunde) && isset($Kunde->cMail)}{$Kunde->cMail}{/if}">
                </div>
              </div>
              <div class="col col-md-12">
                <div class="newsletter-input-group">
                  <button onclick="amPopupTrigger.triggerSetCookie()" 
                    type="submit"
                    class="btn btn-primary submit form-control"
                    style="color: {$styleButtonTextColor}; background-color: {$styleButtonColor};">
                    <span>{$contentButtonText}</span>
                  </button>
                </div>
              </div>
            </div>
          {else}
            <label class="control-label sr-only" for="newsletter_popup_email">Email-Adresse</label>
            <div class="input-group">
              <input size="20" name="cEmail" id="newsletter_popup_email" class="form-control"
                placeholder="{lang key="emailadress"}" type="email"
                value="{if isset($Kunde) && isset($Kunde->cMail)}{$Kunde->cMail}{/if}">
              <span class="input-group-btn">
                <button onclick="amPopupTrigger.triggerSetCookie()" 
                  type="submit" class="btn btn-primary submit"
                  style="color: {$styleButtonTextColor}; background-color: {$styleButtonColor};">
                  <span>{$contentButtonText}</span>
                </button>
              </span>
            </div>
          {/if}
        </div>
      </fieldset>
      <p class="admorris-popup-legaltext">
        {$contentText2}
      </p>
    </form>
  </div>
{/block}