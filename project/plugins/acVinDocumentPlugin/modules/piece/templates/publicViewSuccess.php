<?php $gitcommit = file_exists('../../.git/ORIG_HEAD') ? str_replace("\n", "", file_get_contents('../../.git/ORIG_HEAD')) : null;?>
<!doctype html>
<html lang="fr">
    <head>
        <?php include_http_metas() ?>
        <?php include_metas() ?>
        <?php include_title() ?>
        <?php if(file_exists(sfConfig::get('sf_web_dir').'/css/compile_'.Organisme::getCurrentOrganisme().'.css')): ?>
            <link href="<?php echo public_path("/css/compile_".Organisme::getCurrentOrganisme().".css").'?'.$gitcommit; ?>" rel="stylesheet">
        <?php else: ?>
            <link href="<?php echo public_path("/css/compile_default.css").'?'.$gitcommit; ?>" rel="stylesheet">
        <?php endif; ?>
    </head>
    <body>
        <div style="padding-top: 40px;" class="container text-center">
            <img src="/images/logo_<?php echo Organisme::getCurrentOrganisme() ?>.png" alt="<?php echo Organisme::getInstance()->getNom() ?>">

            <p style="margin-top: 30px;" class="lead"><?php echo $piece->libelle ?></p>

            <a style="margin-top: 20px;" class="btn btn-primary btn-lg" href="<?php echo $piece->getUrlPublic() ?>">Télécharger ce document</a>
        </div>
    </body>
</html>
