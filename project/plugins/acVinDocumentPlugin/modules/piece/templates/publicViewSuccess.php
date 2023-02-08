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
        <style>

        .loader {
            width: 18px;
            height: 18px;
            border: 2px solid #fff;
            border-bottom-color: transparent;
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
            }
            @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        </style>
    </head>
    <body>
        <div style="padding-top: 40px;" class="container text-center">
            <img src="/images/logo_<?php echo Organisme::getCurrentOrganisme() ?>.png" alt="<?php echo Organisme::getInstance()->getNom() ?>">

            <p style="margin-top: 30px;" class="lead"><?php echo $piece->libelle ?></p>
            <a id="btn_loading" style="margin-top: 20px;" class="btn btn-primary btn-lg hidden" href="<?php echo $piece->getUrlPublic() ?>"><span class="loader" style="margin-right: 10px;"></span>Téléchargement cours ...</a>
            <a style="margin-top: 20px;" id="btn_download" class="btn btn-primary btn-lg" href="<?php echo $piece->getUrlPublic() ?>">Télécharger ce document</a>
        </div>
        <script>
            document.getElementById('btn_loading').classList.remove('hidden');
            document.getElementById('btn_download').classList.add('hidden');
            window.setTimeout(function() {
                document.getElementById('btn_download').click()
            }, 700);
            window.setTimeout(function() {
                document.getElementById('btn_loading').classList.add('hidden');
                document.getElementById('btn_download').classList.remove('hidden');
            }, 5000);
        </script>
    </body>
</html>
