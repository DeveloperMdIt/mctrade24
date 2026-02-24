{* This file must only be included ONCE in the admin *}
<script type="text/javascript">
    window.lpaAdminAjaxUrl = '{$lpaAdminGlobal.adminUrl}'.includes('?') ? '{$lpaAdminGlobal.adminUrl}&isLpaAjax=1' : '{$lpaAdminGlobal.adminUrl}?isLpaAjax=1';
    window.lpaLang = {
        /* general settings */
        'lpaError': '{__('lpaError')}',
        'checkSuccess': '{__('checkSuccess')}',
        'checkFail': '{__('checkFail')}',
        'technicalError': '{__('technicalError')}',
        'pleaseWait': '{__('pleaseWait')}',
        'keyGenSuccess': '{__('keyGenSuccess')}',
        'keyGenFailure': '{__('keyGenFailure')}',
        /* order management */
        'internalCommentOptional': '{__('internalCommentOptional')}',
        'noCancelOperation': '{__('noCancelOperation')}',
        'searchFailedTitle': '{__('searchFailedTitle')}',
        'searchFailedNoOrderFound': '{__('searchFailedNoOrderFound')}',
        'cancelChargePermissionTitle': '{__('cancelChargePermissionTitle')}',
        'cancelChargePermissionDescription': '{__('cancelChargePermissionDescription')}',
        'cancelChargePermissionConfirm': '{__('cancelChargePermissionConfirm')}',
        'closeChargePermissionTitle': '{__('closeChargePermissionTitle')}',
        'closeChargePermissionDescription': '{__('closeChargePermissionDescription')}',
        'closeChargePermissionConfirm': '{__('closeChargePermissionConfirm')}',
        'cancelChargeTitle': '{__('cancelChargeTitle')}',
        'cancelChargeDescription': '{__('cancelChargeDescription')}',
        'cancelChargeConfirm': '{__('cancelChargeConfirm')}',
        'createChargeTitle': '{__('createChargeTitle')}',
        'createChargeDescription': '{__('createChargeDescription')}',
        'createChargeConfirm': '{__('createChargeConfirm')}',
        'captureChargeTitle': '{__('captureChargeTitle')}',
        'captureChargeDescription': '{__('captureChargeDescription')}',
        'captureChargeConfirm': '{__('captureChargeConfirm')}',
        'createRefundTitle': '{__('createRefundTitle')}',
        'createRefundDescription': '{__('createRefundDescription')}',
        'createRefundConfirm': '{__('createRefundConfirm')}',
        'cancelSubscriptionTitle': '{__('cancelSubscriptionTitle')}',
        'cancelSubscriptionDescription': '{__('cancelSubscriptionDescription')}',
        'cancelSubscriptionConfirm': '{__('cancelSubscriptionConfirm')}',
        'createOrderForSubscriptionTitle': '{__('createOrderForSubscriptionTitle')}',
        'createOrderForSubscriptionDescription': '{__('createOrderForSubscriptionDescription')}',
        'createOrderForSubscriptionConfirm': '{__('createOrderForSubscriptionConfirm')}',
        'pauseSubscriptionTitle': '{__('pauseSubscriptionTitle')}',
        'pauseSubscriptionDescription': '{__('pauseSubscriptionDescription')}',
        'pauseSubscriptionConfirm': '{__('pauseSubscriptionConfirm')}',
        'resumeSubscriptionTitle': '{__('resumeSubscriptionTitle')}',
        'resumeSubscriptionDescription': '{__('resumeSubscriptionDescription')}',
        'resumeSubscriptionConfirm': '{__('resumeSubscriptionConfirm')}',
        'resumeSubscriptionCreateNow': '{__('resumeSubscriptionCreateNow')}',
        'changeHiddenButtonModeTitle': '{__('changeHiddenButtonModeTitle')}',
        'changeHiddenButtonModeDescription': '{__('changeHiddenButtonModeDescription')}',
        'changeHiddenButtonModeCancel': '{__('changeHiddenButtonModeCancel')}',
        'changeHiddenButtonModeConfirm': '{__('changeHiddenButtonModeConfirm')}'
    };
</script>
<link rel="stylesheet" href="{$lpaAdminGlobal.adminTemplateUrl}css/jquery-confirm.min.css?v={$lpaAdminGlobal.pluginVersion}" type="text/css">
<link rel="stylesheet" href="{$lpaAdminGlobal.adminTemplateUrl}css/admin.css?v={$lpaAdminGlobal.pluginVersion}" type="text/css">
<script src="{$lpaAdminGlobal.adminTemplateUrl}js/jquery-confirm.min.js?v={$lpaAdminGlobal.pluginVersion}" defer="defer"></script>
<script src="{$lpaAdminGlobal.adminTemplateUrl}js/admin.js?v={$lpaAdminGlobal.pluginVersion}" defer="defer"></script>
<script src="{$lpaAdminGlobal.adminTemplateUrl}js/ordermanagement.js?v={$lpaAdminGlobal.pluginVersion}" defer="defer"></script>
<script src="{$lpaAdminGlobal.adminTemplateUrl}js/subscriptionmanagement.js?v={$lpaAdminGlobal.pluginVersion}" defer="defer"></script>
