<div class="page-header">
    <h2>Récupération de la <?php echo $type; ?> de l'établissement <?= $etablissement->identifiant?></h2>
    <p class="text-center">
    <span class="img-responsive center-block">
    <?php
        $img_path = dirname(__FILE__).'/../../../../web/images/';
        $douane2app = 'douane2'.sfConfig::get('sf_app');
        if (file_exists($img_path.$douane2app.'.gif')): ?>
    <?php else: ?>
        <img src="/images/douane2.gif" alt="Chargement en cours..."/>
        <img src="/images/<?= $douane2app ?>.png"  width="150"/>
    <?php endif; ?>
    </span></p>
</div>
<form action="<?php echo url_for('scrape_fichier', $etablissement); ?>" method="POST" id="form">
    <input type="hidden" name="type" value="<?php echo $type; ?>" />
    <input type="hidden" name="periode" value="<?php echo $periode; ?>" />
</form>

<script>
    setTimeout(function(){document.getElementById("form").submit();}, 500);
</script>
