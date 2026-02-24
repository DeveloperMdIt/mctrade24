{$isCheckout = str_starts_with($reward_points_route, "bestellvorgang")}

{if $isCheckout}
  <div class="col col-md-6 col-12">
{/if}
{card id="redeem-reward-points" no-body=true class="{if $isCheckout}card-gray{/if}"}
  {cardheader}
    <span class="{if $isCheckout}h4 checkout-confirmation-heading{else}h5{/if} d-flex align-items-center justify-content-between">
      {$admUtils::trans('marketing_reward_points_name')}
      <button class="border-0 p-0 bg-transparent" data-toggle="collapse" data-target="#bonuspoints-collapse" aria-expanded="false">
        <span class="sr-only">Toggle {$admUtils::trans('marketing_reward_points_name')}</span>
      </button>
    </span>
  {/cardheader}
  <style>
    .bonuspointsContainer {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 1.5em;
      container-type: inline-size;
    }

    .bonuspoints {
      line-height: normal;
      flex: 1 1 300px;
    }

    .reward-points-form-wrapper {
      display: flex;
      gap: 10px;
      width: 100%;

      input {
        width: 100% !important;
      }

      .form-inline {
        flex-wrap: nowrap;
        gap: 10px;
        margin: 0;

        &:first-child {
          width: 66.6%
        }

        &:last-child {
          width: 33.3%
        }
      }
    }

    @container (max-width: 400px) {
      .reward-points-form-wrapper {
        flex-wrap: wrap;

        .form-inline.form-inline {
          width: 100%;
        }
      }
    }
  </style>
  {collapse id="bonuspoints-collapse" visible=false}
    {cardbody class="bonuspointsContainer"}
      <div class="bonuspoints">
        {$admUtils::trans('marketing_reward_points_balance')} <strong class="color-brand-primary text-primary">{$valid_reward_points}
          {$admUtils::trans('marketing_reward_points_name_plus')}</strong> {$admUtils::trans('marketing_reward_points_value')} <strong
          class="color-brand-primary text-primary text-nowrap">{$valid_reward_points_value}</strong>
      </div>
      <div class="reward-points-form-wrapper">
        <form action="{get_static_route id=$reward_points_route}" method="post" class="form form-inline">
          {$jtl_token}
          <input aria-label="{$admUtils::trans('marketing_reward_points_redeem_input_label')}" type="number" class="form-control" name="admorris-bonuspunkte" min="{$reward_points_redeem_min}"
            max="{$reward_points_redeem_max}" step="0.01" value="{$reward_points_redeem_value}" required>
          <input type="submit" value="{$admUtils::trans('marketing_reward_points_button')}" class="submit btn btn-outline-secondary">
        </form>
        {* Alle einlösen Button *}
        <form action="{get_static_route id=$reward_points_route}" method="post" class="form form-inline">
          {$jtl_token}
          <input type="submit" class="btn btn-outline-secondary" name="redeem-all-reward-points" value="{$admUtils::trans('marketing_reward_points_redeem_all')|default:"Alle einlösen"}">
        </form>
      </div>
    {/cardbody}
  {/collapse}
{/card}
{if $isCheckout}
  </div>
{/if}