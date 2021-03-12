<?php use_helper('Lot') ?>
Monsieur, Madame,

Nous vous prions de bien vouloir trouver ci-dessous extrait du procès verbal de
la séance de dégustation du : <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")) ?>.

Au vu des documents fournis, et des résultats du contrôle documentaire, analytique
et organoleptique, nous vous confirmons les résultats pour vos lots :

<?php if(count($lots[Lot::STATUT_CONFORME]) > 0 && count($lots[Lot::STATUT_NONCONFORME]) > 0): ?>
Certain de vos lots sont CONFORMES et aptes à la commercialisation tandis que
d'autres sont NON CONFORMES.
<?php elseif(count($lots[Lot::STATUT_CONFORME])): ?>
L'ensemble de vos lots sont CONFORMES et aptes à la commercialisation.
<?php elseif(count($lots[Lot::STATUT_NONCONFORME])): ?>
Nous vous confirmons que vos lots SONT CONFORMES et aptes à la commercialisation.
<?php endif; ?>

<?php if(count($lots[Lot::STATUT_CONFORME])): ?>
Vous trouverez ci-dessous le lien vers le pdf des lots conformes :
<a href="<?php echo url_for('degustation_conformite_pdf', array('id' => $degustation->_id, 'identifiant' => $etablissement->identifiant), true); ?>">
    lots conformes
</a>


<?php endif; ?>
<?php if(count($lots[Lot::STATUT_CONFORME]) && count($lots[Lot::STATUT_NONCONFORME])): ?>
Par ailleurs, certains de vos vins dont la liste figure dans les fiches de non conformité
ci-jointes ont été ajournés.

<?php endif; ?>
<?php if(count($lots[Lot::STATUT_NONCONFORME])): ?>
Vous trouverez ci dessous l'ensemble des pdfs présentant des non conformités :

<?php foreach($lots[Lot::STATUT_NONCONFORME] as $lotNonConforme): ?>
    <a href="<?php echo url_for('degustation_non_conformite_pdf', array('id' => $degustation->_id, 'lot_dossier' => $lotNonConforme->numero_dossier ,'lot_archive' => $lotNonConforme->numero_archive), true); ?>">
        <?= showProduitLot($lotNonConforme) . ", non conformité de type : " . $lotNonConforme->getShortLibelleConformite() ?>
    </a>
<?php endforeach; ?>
<?php endif; ?>

Nous vous prions de croire, Monsieur, Madame, en l’expression de nos sentiments les meilleurs.
