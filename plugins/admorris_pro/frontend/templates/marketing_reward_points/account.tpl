{if $valid_reward_points > 0 || $pending_reward_points > 0}
  {col cols=12 lg=6 class="account-data-item account-data-item-reward-points"}
    {card no-body=true id="reward-points" class="account-reward-points"}
      {cardheader}<h3>{$admUtils::trans('marketing_reward_points_name')}</h3>{/cardheader}
      {cardbody}
        {if $valid_reward_points > 0}
          {$admUtils::trans('marketing_reward_points_balance')}
          <strong>{$valid_reward_points} {$admUtils::trans('marketing_reward_points_name_plus')}</strong>
          {if $show_value}
            {$admUtils::trans('marketing_reward_points_value')} {$valid_reward_points_value}
          {/if}
        {/if}
        {if $pending_reward_points}
          {$admUtils::trans('marketing_reward_points_pending')}
          <strong>{$pending_reward_points} {$admUtils::trans('marketing_reward_points_name_plus')}</strong>
          {if $show_value}
            {$admUtils::trans('marketing_reward_points_value')} {$pending_reward_points_value}
          {/if}
        {/if}
      {/cardbody}
    {/card}
  {/col}

{/if}