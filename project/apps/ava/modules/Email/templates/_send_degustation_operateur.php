<?php use_helper('Degustation') ?>
<?php use_helper('Date') ?>
Madame, Monsieur,

Un agent de l'ODG-AVA viendra prélever vos lots <?php echo str_replace(" ".$operateur->millesime, "", $operateur->getRawValue()->libelle) ?> du millésime <?php echo $operateur->getMillesime(); ?> à votre chai situé au <?php echo getAdresseChai($operateur) ?> :

Le <?php echo ucfirst(format_date($operateur->date_prelevement, "P", "fr_FR")) ?> entre <?php echo Date::francizeHeure($operateur->heure); ?> et <?php echo Date::francizeHeure(getHeurePlus($operateur, 2)); ?>


NB :

- Le prélèvement se fait uniquement sur des vins en cuve (fermentation terminée, vin stabilisé et clarifié, la filtration n’est pas obligatoire)<?php if($operateur->appellation == 'VTSGN'): ?> ou en bouteille<?php endif; ?>.

- En cas d'impossibilité de recevoir l'agent, ou si les vins ne sont pas prêts, merci de nous avertir par retour de fax, par téléphone, en répondant à ce mail ou en adressant directement un mail à l'adresse <?php echo sfConfig::get('app_email_plugin_reply_to_adresse') ?> dans les plus brefs délais.

Bien cordialement,

Le service Appui technique de l'AVA
