<style>
.consent-manager-wrapper.consent-manager-wrapper--custom-styles {
  display:none
}
:root {
  {foreach $cnp as $style}
    --cookieNoticePro-{$style@key}: {$style};
  {/foreach}
  {* --cookieNoticeProBannerJustify: {$admorris_pro_marketing_cookie_notice_pro_styles.bannerJustify};
  --cookieNoticeProBannerAlign: {$admorris_pro_marketing_cookie_notice_pro_styles.bannerAlign};
  --cookieNoticeProBannerMaxWidth: {$admorris_pro_marketing_cookie_notice_pro_styles.bannerMaxWidth};
  --cookieNoticeProModalAlign: {$admorris_pro_marketing_cookie_notice_pro_styles.modalAlign};
  --cookieNoticeProPositionModal: {$admorris_pro_marketing_cookie_notice_pro_styles.positionModal};
  --cookieNoticeProButtonColor: {$admorris_pro_marketing_cookie_notice_pro_styles.buttonColor};
  --cookieNoticeProButtonTextColor: {$admorris_pro_marketing_cookie_notice_pro_styles.buttonTextColor};
  --cookieNoticeProLinkColor: {$admorris_pro_marketing_cookie_notice_pro_styles.linkColor};
  --cookieNoticeProHeadlinesColor: {$admorris_pro_marketing_cookie_notice_pro_styles.headlinesColor};
  --cookieNoticeProTextColor: {$admorris_pro_marketing_cookie_notice_pro_styles.textColor};
  --cookieNoticeProBackgroundColor: {$admorris_pro_marketing_cookie_notice_pro_styles.backgroundColor}; */
  *}
}
</style>