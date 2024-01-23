<?php use_helper('Lot') ?>
<?php use_javascript('hamza_style.js'); ?>
<?php use_javascript('degustation.js'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation, "options" => array("nom" => "Tables des échantillons"))); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_ANONYMISATION_MANUELLE)); ?>

<div class="row">
    <div class="col-xs-12 row row-no-gutters">
        <h2 style="margin-top: 0; margin-bottom: 10px;">Anonymisation des lots</h2>

        <form method="POST" action="<?php echo url_for('degustation_anonymats_etape', ['id' => $degustation->_id]) ?>">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <div class="input-group" style="margin-bottom: 0; position: relative;">
            <span class="input-group-addon">Filtrer le tableau</span>
            <input id="table_filtre" type="text" class="form-control" placeholder="Rechercher par opérateur, produit ou numéro de logement" autofocus="autofocus" />
            <a href="" id="btn_annuler_filtre" tabindex="-1" class="small hidden" style="z-index: 3; right: 10px; top: 10px; position: absolute;">Annuler la recherche</a>
        </div>
        <table id="table_anonymisation_manuelle"  style="border-top: 0;" class="table table-bordered table-striped table_lots text-center">
          <thead>
            <tr>
              <th class="col-xs-3 text-center">Opérateur</th>
              <th class="col-xs-4 text-center">Produit (millesime)<br/><span class="text-muted">(<?php echo $tri; ?> - <a data-toggle="modal" data-target="#popupTableTriForm" type="button" href="#">changer</a>)</span></th>
              <th class="col-xs-1 text-center">Lgmt</th>
              <th class="col-xs-2 text-center">Num. ODG</th>
              <th class="col-xs-2 text-center">Anonymat</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($degustation->getLotsDegustables() as $lot): ?>
                <?php $name = $form->getWidgetNameFromLot($lot); ?>
                <tr class="lot <?= ($lot->leurre) ? ' warning' : '' ?>">
                    <td class="lot-declarant"><?php echo $lot->declarant_nom ?></td>
                    <td class="lot-produit"><?php echo $lot->produit_libelle ?> (<?php echo $lot->millesime ?>)</td>
                    <td class="lot-logement"><?php echo $lot->numero_logement_operateur ?></td>
                    <td class="lot-numero"><?php echo $lot->numero_dossier . ' / ' . $lot->numero_archive ?></td>
                    <td class="lot-anonymat">
                        <div class="form-group">
                            <label class="sr-only" for="">Numéro anonymat</label>
                            <div class="input-group">
                                <?php echo $form[$name]->render(['class' => 'form-control']); ?>
                                <div class="input-group-addon">
                                    <button type="button" class="close" aria-label="Close" tabindex="-1"><span aria-hidden="true">&times;</span></button>
                                </div>
                            </div>
                        </div>
                        <?php echo $form[$name]->renderError() ?>
                    </td>
                </tr>
            <?php endforeach ?>
          </tbody>
          <tfoot class="hidden">
            <tr><td colspan="7">Aucun lot trouvé <a id="btn_annuler_filtre_table" href=""><small>(annuler la recherche)</small></a></td></tr>
          </tfoot>
        </table>
        <div class="col-xs-4 col-xs-offset-4 text-center">
            <button class="btn btn-sm btn-default ajax" data-toggle="modal" data-target="#popupLeurreForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un leurre</button>
        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-primary">
                Confirmer l'anonymat <i class="glyphicon glyphicon-chevron-right"></i>
            </button>
        </div>
        </div>
        </form>
    </div>
</div>

<?php include_partial('degustation/popupTableTriForm', array('url' => url_for('degustation_tri_table', array('id' => $degustation->_id, 'numero_table' => 0, 'service' => url_for('degustation_anonymats_etape', ['id' => $degustation->_id]))), 'form' => $triTableForm)); ?>
<?php include_partial('degustation/popupAjoutLeurreForm', array('url' => url_for('degustation_ajout_leurre', ['id' => $degustation->_id, 'service' => url_for('degustation_anonymats_etape', ['id' => $degustation->_id])]), 'form' => $ajoutLeurreForm, 'table' => null)); ?>

<script>
    document.querySelector('#table_filtre').onkeyup = function() {
        var lines = document.querySelectorAll('#table_anonymisation_manuelle tbody tr');
        var terms = this.value.split(' ');
        lines.forEach(function(line, index) {
            var words = line.innerText;

            for(keyTerm in terms) {
                var termRegexp = new RegExp(terms[keyTerm], 'i');
                if(words.search(termRegexp) < 0) {
                    line.classList.add("hidden");
                    return;
                }
            }

            line.classList.remove("hidden");
        });

        if(document.querySelectorAll("#table_anonymisation_manuelle tbody tr.hidden").length == document.querySelectorAll("#table_anonymisation_manuelle tbody tr").length) {
            document.querySelector('#table_anonymisation_manuelle tfoot').classList.remove('hidden');
        } else {
            document.querySelector('#table_anonymisation_manuelle tfoot').classList.add('hidden');
        }

        if(this.value) {
            document.getElementById('btn_annuler_filtre').classList.remove('hidden');
        } else {
            document.getElementById('btn_annuler_filtre').classList.add('hidden');
        }
    }

    document.getElementById('btn_annuler_filtre_table').onclick = function(e) {
        document.getElementById('btn_annuler_filtre').click();
        return false;
    };

    document.getElementById('btn_annuler_filtre').onclick = function(e) {
        document.querySelector('#table_filtre').value = "";
        document.querySelector('#table_filtre').dispatchEvent(new Event("keyup"))
        return false;
    };
</script>
