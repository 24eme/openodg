Monsieur, Madame,

Nous vous prions de bien vouloir trouver ci-dessous extrait du procès verbal de
la séance de dégustation du : <?php echo ucfirst(format_date($degustation->date, "P", "fr_FR")) ?>.

Au vu des documents fournis, et des résultats du contrôle documentaire, analytique
et organoleptique, nous vous confirmons les résultats pour vos lots :

<?php if($mailManager->hasConformesNonConformes()): ?>
Certain de vos lots sont CONFORMES et aptes à la commercialisation tandis que
d'autres sont NON CONFORMES.
<?php elseif($mailManager->hasNonConformes()): ?>
L'ensemble de vos lots sont CONFORMES et aptes à la commercialisation.
<?php elseif($mailManager->hasConformes()): ?>
Nous vous confirmons que vos lots SONT CONFORMES et aptes à la commercialisation.
<?php endif; ?>

<?php if($mailManager->hasConformes()): ?>
Vous trouverez ci-dessous le lien vers le pdf des lots conformes :
<?php echo url_for('degustation_conformite_pdf', array('id' => $degustation->_id, 'identifiant' => $etablissement->identifiant), true); ?>


<?php endif; ?>
<?php if($mailManager->hasConformesNonConformes()): ?>
Par ailleurs, certains de vos vins dont la liste figure dans les fiches de non conformité
ci-jointes ont été ajournés.

<?php endif; ?>
<?php if($mailManager->hasNonConformes()): ?>
Vous trouverez ci dessous l'ensemble des pdfs présentant des non conformités :

<?php foreach($mailManager->etablissementLotsNonConformes as $lotsNonConformes): ?>
<?php echo url_for('degustation_non_conformite_pdf', array('id' => $degustation->_id, 'identifiant' => $etablissement->identifiant, 'lot_dossier' => $lotsNonConformes->numero_dossier ,'lot_num_anon' => $lotsNonConformes->numero_anonymat), true); ?>

<?php endforeach; ?>
<?php endif; ?>

Nous vous prions de croire, Monsieur, Madame, en l’expression de nos sentiments les meilleurs.
