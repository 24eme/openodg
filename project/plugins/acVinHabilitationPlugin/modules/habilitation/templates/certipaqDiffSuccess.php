
<h1>Comparatif Certipaq</h1>

<p>Comparatif des données issues de la base de données et celles trouvées vias l'API Certipaq</p>

<h2>Opérateur</h2>

<?php function print_tr($titre, $local, $certi, $type_comp = null) {
    $class=" class='success' ";
    if ($local === "[N/A]") {
        $local = '<span class="text-muted">n/a</span>';
        $type_comp = null;
    }
    switch ($type_comp) {
        case 'string':
            if (strtolower(trim($local)) != strtolower(trim($certi))){
                $class=" class='danger' ";
            }
            break;
        case 'nombre':
            if (str_replace(array(' ', '.', ''), '', $local) != str_replace(array(' ', '.', ''), '', $certi)){
                $class=" class='danger' ";
            }
            break;
        case 'pays':
            if (strtoupper(substr($local, 0, 2)) != strtoupper(substr($certi, 0, 2))){
                $class=" class='danger' ";
            }
            break;
        case 'date':
            if (preg_replace('/ .*/', '', $local) != preg_replace('/ .*/', '', $certi)) {
                $class=" class='danger' ";
            }
            break;
        default:
            $class = "";
            break;
    }
    echo "<tr $class><th>$titre</th><td>$local</td><td>$certi</td></tr>";
}
?>
<table class="table">
<tr><th>&nbsp;</th><th>Données en base</th><th>Données Certipaq</th></tr>
<?php print_tr("Identifiant interne", $etablissement->identifiant, $certipaq_operateur->id); ?>
<?php print_tr("Raison sociale", $pseudo_operateur->raison_sociale, $certipaq_operateur->raison_sociale, 'string'); ?>
<?php print_tr("Nom", $pseudo_operateur->nom_entreprise, $certipaq_operateur->nom_entreprise, 'string'); ?>
<?php print_tr("SIRET", $pseudo_operateur->siret, $certipaq_operateur->siret, 'nombre'); ?>
<?php print_tr("CVI", $pseudo_operateur->cvi, $certipaq_operateur->cvi, 'nombre'); ?>
<?php print_tr("Adresse", $pseudo_operateur->adresse, $certipaq_operateur->adresse, 'string'); ?>
<?php print_tr("Code postal", $pseudo_operateur->cp, $certipaq_operateur->cp, 'nombre'); ?>
<?php print_tr("Ville", $pseudo_operateur->ville, $certipaq_operateur->ville, 'string'); ?>
<?php print_tr("Pays", $pseudo_operateur->pays, $certipaq_operateur->pays, 'pays'); ?>
<?php print_tr("Telephone", $pseudo_operateur->telephone, $certipaq_operateur->telephone, 'nombre'); ?>
<?php print_tr("Portable", $pseudo_operateur->portable, $certipaq_operateur->portable, 'nombre'); ?>
<?php print_tr("Email", $pseudo_operateur->email, $certipaq_operateur->email, 'string'); ?>
<?php print_tr("Observations", "[N/A]", $certipaq_operateur->observations); ?>

<tr><td colspan=3><h2>Habilitations</h2></td></tr>
<?php
    foreach ($certipaq_operateur->sites as $site_id => $site):
    $pseudo_site = (object) $pseudo_operateur->sites[$site_id]->getRawValue();
?>
<?php print_tr('Nom du site', $pseudo_site->nom_site, $site->nom_site); ?>
<?php print_tr('Capacité cuverie', $pseudo_site->capacite_cuverie, $site->capacite_cuverie, ''); ?>
<?php print_tr('Adresse', $pseudo_site->adresse, $site->adresse." ".$site->complement_adresse, 'string'); ?>
<?php print_tr('Code postal', $pseudo_site->cp, $site->cp, 'string'); ?>
<?php print_tr('Ville', $pseudo_site->ville, $site->ville, 'string'); ?>
<?php print_tr('Telephone', $pseudo_site->telephone, $site->telephone, 'string'); ?>
<?php print_tr('Fax', $pseudo_site->fax, $site->fax, 'string'); ?>
<?php print_tr('Commentaire', "[N/A]", $site->commentaire); ?>
<?php foreach($site->habilitations as $id => $h):  $pseudo_h = (object) $pseudo_site->habilitations[$id]; ?>
<?php print_tr("Hab $id : Numéro", "[N/A]", $h->num_habilitation); ?>
<?php print_tr("Hab $id : Activité", $pseudo_h->dr_activites_operateurs->libelle, $h->dr_activites_operateurs->libelle, 'string'); ?>
<?php print_tr("Hab $id : Cahier des charges", $pseudo_h->dr_cdc->libelle, $h->dr_cdc->libelle, 'string'); ?>
<?php print_tr("Hab $id : CdC famille", $pseudo_h->dr_cdc_famille->libelle, $h->dr_cdc_famille->libelle, 'string'); ?>
<?php print_tr("Hab $id : Statut", $pseudo_h->dr_statut_habilitation->libelle, $h->dr_statut_habilitation->libelle, 'string'); ?>
<?php print_tr("Hab $id : Outil de production", "[N/A]", $h->outil_production->nom_outil, 'string'); ?>
<?php print_tr("Hab $id : Date decision", $pseudo_h->date_decision, $h->date_decision, 'date'); ?>
<?php print_tr("Hab $id : Date dossier_complet_odg", $pseudo_h->date_dossier_complet_odg, $h->date_dossier_complet_odg); ?>
<?php print_tr("Hab $id : Date habilitation_maximum", "[N/A]", $h->date_habilitation_maximum); ?>
<?php print_tr("Hab $id : Date reception_certipaq", "[N/A]", $h->date_reception_certipaq); ?>
<?php print_tr("Hab $id : Date Validation_dossier_complet", "[N/A]", $h->date_validation_dossier_complet); ?>
<?php print_tr("Hab $id : Date Information_odg", "[N/A]", $h->date_information_odg); ?>
<?php print_tr("Hab $id : is_demande_complements","[N/A]", $h->is_demande_complements); ?>
<?php print_tr("Hab $id : Demande complements", "[N/A]", $h->demande_complements); ?>
<?php endforeach; ?>
<?php endforeach; ?>
</table>
<div class="row" style="margin-top: 100px;">
<h2>Données brutes</h2>
<table class="table">
<tr><th>Données en base</th><th>Données Certipaq</th></tr>
<tr>
<td><pre>
<?php print_r($pseudo_operateur->getRawValue()); ?>
</pre></td>
<td><pre>
<?php print_r($certipaq_operateur->getRawValue()); ?>
</pre></td>
</table>
</div>