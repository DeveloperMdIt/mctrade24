{*
    This sets a flag in the localStorage, when doofinder was open, so bounce landing page can ignore the popstate
*}
<script>
    function attachHandler() {
        if(document.querySelector(".dfd-header")){
            localStorage.setItem("ignoreBounceLandingPage", true);
        }
    }

    attachHandler();

    setInterval(attachHandler, 2000); 
</script>