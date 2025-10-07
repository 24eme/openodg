<script id="dataJson" type="application/json">
<?php echo $sf_data->getRaw('json') ?>
</script>
<script>
    let controles = (localStorage.getItem("controles")) ? JSON.parse(localStorage.getItem("controles")) : {};
    controles["<?php echo $controle->_id ?>"] = JSON.parse(document.getElementById("dataJson").textContent);
    localStorage.setItem("controles", JSON.stringify(controles));
    window.location.replace("<?php echo url_for('parcellaire_declarant', $controle->getEtablissementObject()) ?>");
</script>
