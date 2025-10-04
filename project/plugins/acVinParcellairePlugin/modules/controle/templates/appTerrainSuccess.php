<script>
    var listingTemplate = { template: "<?php echo str_replace(['"', "\n"], ['\"', ""], get_partial('controle/terrainListing')) ?>"}
    var operateurTemplate = { template: "<?php echo str_replace(['"', "\n"], ['\"', ""], get_partial('controle/terrainOperateur')) ?>" }
    var parcelleTemplate = { template: "<?php echo str_replace(['"', "\n"], ['\"', ""], get_partial('controle/terrainParcelle')) ?>" }
    var auditTemplate = { template: "<?php echo str_replace(['"', "\n"], ['\"', ""], get_partial('controle/terrainAudit')) ?>" }
</script>
<script src="/js/appterrain.js"></script>
