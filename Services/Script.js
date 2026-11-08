<script>
    const BASE = BASE_URL ? BASE_URL : "<?= ApiTokenDetails::backEndBaseUrl(); ?>";
    const siteAddress = siteHostAddress ? siteHostAddress : "<?= ltSiteHostAddress() ?>";
    const getTokenNameNew = "<?= ApiTokenDetails::tokenName(); ?>";

    const LWTOKEN = localStorage.getItem(`${getTokenNameNew}`);
    
</script>