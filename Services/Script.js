<script>
    const BASE_ENGAGE = "<?= ApiTokenDetails::backEndBaseUrl(); ?>";
    const siteAddressEngage = "<?= ltSiteHostAddress() ?>";
    const getTokenNameNew = "<?= ApiTokenDetails::tokenName(); ?>";

    const LW_TOKEN_ENGAGE = localStorage.getItem(`${getTokenNameNew}`);
    
</script>