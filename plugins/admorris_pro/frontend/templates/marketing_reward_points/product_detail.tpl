<hr>
<div id="reward-points" class="row">
  <div class="col-xs-12 col-sm-12">
    {$admUtils::trans('marketing_reward_points_product_reward')}
    <span class="color-brand-primary text-primary">
      <strong>
        <span id="admRewardPointsRounded">{$article_reward_points}</span> {$admUtils::trans('marketing_reward_points_name_plus')}
      </strong>
    </span>
    {if $show_value}
      {$admUtils::trans('marketing_reward_points_value')} <span
        id="admRewardPointsValue">{$article_reward_points_currency_value}</span>
    {/if}
  </div>
</div>
{* Beim AdmorrisPro Templpate wird bei Variationsartikeln schon eine <hr> angezeigt *}
{if !($admUtils::isTemplateActive() && isset($Artikel->Variationen) && $Artikel->Variationen|@count > 0 && !$showMatrix)}
  <hr>
{/if}


{* fix reward points value after price change, e.g. variation change *}
<script type="module">
  const admRewardPointsRound = (value) => {
    const roundingOperation = {$rounding_factor};
    
    if (roundingOperation === 1)
      value = Math.round(value);
    else if (roundingOperation === 2)
      value = Math.ceil(value)
    else if (roundingOperation === 3)
      value = Math.floor(value)
    else
      value = Math.round((value + Number.EPSILON) * 100) / 100
    return value;
  }


  function getLocalizedPriceWithoutFactor(price) {

    var localized = number_format(price, 2, '{$currencyClass->getDecimalSeparator()}', '{$currencyClass->getThousandsSeparator()}');

    return {($currencyClass->getForcePlacementBeforeNumber()) ? "\"{$currencyClass->getHtmlEntity()|escape}\" + ' ' + localized;" :
      "localized + ' ' + '{$currencyClass->getHtmlEntity()}';"}
      
  }

  // handle price change
  $(document).on('evo:changed.article.price', (e, args) => {
    const newRewardPoints = admRewardPointsRound(args.price * {$earn_factor});
    const newValue = getLocalizedPriceWithoutFactor(newRewardPoints * {$currency_factor} * {$return_factor})
    

    document.querySelector('#admRewardPointsRounded').innerHTML = newRewardPoints;
    const valueElement = document.querySelector('#admRewardPointsValue');
    valueElement && (valueElement.innerHTML = newValue);
  });
  // @link https://locutus.io/php/strings/number_format/
  function number_format(number, decimals, decPoint, thousandsSep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
    const n = !isFinite(+number) ? 0 : +number
    const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
    const sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
    const dec = (typeof decPoint === 'undefined') ? '.' : decPoint
    let s = ''
    const toFixedFix = function (n, prec) {
      if (('' + n).indexOf('e') === -1) {
        return +(Math.round(n + 'e+' + prec) + 'e-' + prec)
      } else {
        const arr = ('' + n).split('e')
        let sig = ''
        if (+arr[1] + prec > 0) {
          sig = '+'
        }
        return (+(Math.round(+arr[0] + 'e' + sig + (+arr[1] + prec)) + 'e-' + prec)).toFixed(prec)
      }
    }
    // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec).toString() : '' + Math.round(n)).split('.')
    if (s[0].length > 3) {
      s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
    }
    if ((s[1] || '').length < prec) {
      s[1] = s[1] || ''
      s[1] += new Array(prec - s[1].length + 1).join('0')
    }
    return s.join(dec)
  }
</script>