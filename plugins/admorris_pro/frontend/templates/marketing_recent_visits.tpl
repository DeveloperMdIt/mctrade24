{strip}
    <div class="recent-visits {$admorris_pro_marketing_recent_visits_animation} color-brand-primary text-primary">
        {if $admorris_pro_marketing_recent_visits_limited_stock  === true}
            <span class="recent-visits-stock">{$admUtils::trans('marketing_recent_visits_limited_stock')}</span>
        {/if}
        <div class="recent-visits-visits">
            {$admIcon->renderIcon('recentVisits', "icon-content icon-content--default icon-content--center")} <span
                class="icon-text--center"><strong>{$admorris_pro_marketing_recent_visits_total}&nbsp;{$admUtils::trans('marketing_recent_visits_watching')}
                </strong></span>
        </div>
    </div>
    <style>
        .recent-visits {
            display: flex;
            flex-direction: column;
            font-size: 14px;
        }

        .recent-visits-stock {
            font-size: 18px;
            color: red;
            margin-bottom: 5px;
        }

        .recent-visits-fade svg {
            animation: fading 1s infinite;
        }

        {literal}
            @keyframes fading{0%{opacity:0}50%{opacity:1}100%{opacity:0}}
            @-webkit-keyframes fading {0%{opacity:0}50%{opacity:1}100%{opacity:0}} 
        {/literal}
    </style>

{/strip}