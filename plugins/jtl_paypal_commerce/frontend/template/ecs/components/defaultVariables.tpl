let ppcConfig        = {json_encode($ppcConfig)},
ppcECSUrl            = '{$ppcECSUrl}',
errorMessage         = '{__('Error')}',
activeButtonLabel    = '{addslashes($ppcPreloadButtonLabelActive)}',
ppcNamespace         = '{$ppcNamespace}',
wrapperID            = '#ppc-paypal-button-custom-' + ppcNamespace + '-wrapper',
buttonID             = '#ppc-paypal-button-' + ppcNamespace,
renderContainerID    = ppcConfig.layout === 'vertical'
? '#paypal-button-' + ppcNamespace  + '-container'
: '#ppc-' + ppcNamespace + '-horizontal-container',
spinnerID            = '#ppc-loading-spinner-express-' + ppcNamespace,
loadingPlaceholderID = '#ppc-loading-placeholder-' + ppcNamespace;
ppcVaultingActive    = {if isset($ppcVaultingActive) && $ppcVaultingActive}true{else}false{/if};
