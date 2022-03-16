<cas:entities>
<?php foreach($compte->getSociete()->getEtablissementsObj() as $e): ?>
    <cas:entity>
        <cas:raison_sociale><?php echo htmlspecialchars($e->etablissement->raison_sociale, ENT_XML1, 'UTF-8'); ?></cas:raison_sociale>
<?php if ($e->etablissement->cvi): ?>
        <cas:cvi><?php echo $e->etablissement->cvi; ?></cas:cvi>
<?php endif; ?>
<?php if ($compte->societe_informations->siret): ?>
        <cas:siret><?php echo $compte->societe_informations->siret; ?></cas:siret>
<?php endif; ?>
<?php if ($e->etablissement->ppm): ?>
        <cas:ppm><?php echo $e->etablissement->ppm; ?></cas:ppm>
<?php endif; ?>
<?php if ($e->etablissement->no_accises): ?>
        <cas:accise><?php echo $e->etablissement->no_accises; ?></cas:accise>
<?php endif; ?>
<?php if ($compte->getSociete()->no_tva_intracommunautaire): ?>
        <cas:tva><?php echo $compte->getSociete()->no_tva_intracommunautaire; ?></cas:tva>
<?php endif; ?>
<?php endforeach; ?>
    </cas:entity>
</cas:entities>