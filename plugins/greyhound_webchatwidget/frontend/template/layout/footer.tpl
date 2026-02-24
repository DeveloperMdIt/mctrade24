{block name="layout-footer" prepend}
	{if $ghctoken}
		<!-- GREYHOUND CHATWIDGET: Start -->
		<script>
			(function() {
				function loadChatWidget() {
					var consentNeeded = {$consentNeeded};
					if (!consentNeeded || (typeof CM !== 'undefined' && typeof CM.getSettings === 'function')) {
						if (!consentNeeded || (consentNeeded && CM.getSettings('greyhound_webchatwidget_consent') == true)) {

							var script = document.createElement('script');
							script.type = 'text/javascript';
							script.async = true;
							script.src = 'https://messenger.cdn.greyhound-software.com/chat/{$ghctoken}/chat.js';

							document.head.appendChild(script);
						}
					} else {
						setTimeout(loadChatWidget, 100);
					}
				}
				loadChatWidget();
			})();
		</script>
		<!-- GREYHOUND CHATWIDGET: End -->
	{/if}
{/block}
