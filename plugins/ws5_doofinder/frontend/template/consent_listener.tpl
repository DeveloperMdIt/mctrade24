<script type="application/javascript">
    // Initialize global tracking state
    window._doofinderConsent = {
        isLoaded: false,
        isInitializing: false
    };

    // Function to check consent and initialize if approved
    let retryCount = 0; // Add counter to track retries
    function checkConsentAndInitialize() {
        // Check if CM exists, if not retry after a short delay (max 5 attempts)
        if (typeof CM === 'undefined') {
            if (retryCount < 5) { // Only retry if under max attempts
                retryCount++;
                setTimeout(checkConsentAndInitialize, 500);
            }
            return;
        }

        if (typeof CM.getSettings === 'function') {
            var consentGiven = CM.getSettings('ws5_doofinder_consent');
            if (consentGiven === true) {
                initializeDoofinderCookies();
            }
        }
    }

    // Listen for consent.ready event
    document.addEventListener('consent.ready', function() {
        checkConsentAndInitialize();
    });

    // Listen for consent.updated event
    document.addEventListener('consent.updated', function() {
        // Only initialize if not already loaded
        if (!window._doofinderConsent.isLoaded) {
            checkConsentAndInitialize();
        }
    });

    // Function to initialize doofinder fully
    function initializeDoofinderCookies() {
        // Prevent multiple initializations
        if (window._doofinderConsent.isLoaded || window._doofinderConsent.isInitializing) {
            return;
        }

        // Set initializing flag
        window._doofinderConsent.isInitializing = true;

        //pass consent to doofinder
        Doofinder.enableCookies();
        // set loaded flag
        window._doofinderConsent.isLoaded = true;
    }
</script>