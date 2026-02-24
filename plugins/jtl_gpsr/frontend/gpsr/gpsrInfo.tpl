{if !empty($gpsrManufacturer) || !empty($gpsrResponsiblePerson)}
    {if $langGPSRHeading !== '' && $tplscope === 'details'}
    <div class="hr-sect h3 mb-4">{$langGPSRHeading}</div>
    {/if}
    <div class="row gpsr-compliance">
        {if !empty($gpsrManufacturer)}
        <div class="col col-12 col-md-6">
            {include file="string:$gpsrManufacturer"}
        </div>
        {/if}
        {if !empty($gpsrResponsiblePerson)}
        <div class="col col-12 col-md-6">
            {include file="string:$gpsrResponsiblePerson"}
        </div>
        {/if}
    </div>
{/if}
