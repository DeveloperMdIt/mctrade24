<div class="future-reward-points btn-border-radius">
  {$admUtils::trans('marketing_reward_points_total_reward')} <span class="color-brand-primary text-primary"><strong>
      {$cart_reward_points}
      {$admUtils::trans('marketing_reward_points_name_plus')}</strong></span>
  {if $show_value}
    {$admUtils::trans('marketing_reward_points_value')}
    <span class="text-nowrap">{$cart_reward_points_currency_value}</span>
  {/if}
</div>

<style>
  .future-reward-points {
    margin-top: 1.1rem;
    text-align: center;
    background-color: var(--light);
    padding: 0.5rem 1rem;
  }
</style>