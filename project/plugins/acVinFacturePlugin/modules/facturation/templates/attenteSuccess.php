<ol class="breadcrumb">
    <li><a href="<?php echo url_for('facturation') ?>">Facturation</a></li>
    <li>Facturation en attente</li>
</ol>

<h2>Mouvements de facturation en attente</h2>
<h4 class="page-header"><?php echo count($mouvements) ?> opérateurs avec des mouvements en attente</h4>

<?php include_partial('global/flash'); ?>

<div class="row">
    <table class="table table-striped table-condensed">
      <thead>
        <tr><th>Établissements</th><th></th></tr>
      <tbody>
        <?php foreach ($mouvements->getRawValue() as $id => $infos): ?>
            <tr>
                <td title="<?php echo $id ?>"><?php echo EtablissementClient::getInstance()->retrieveById($id)->raison_sociale ?></td>
                <td>
                    <a target='_blank' href="<?php echo url_for('facturation_declarant', ['id' => $id]) ?>" class="btn btn-xs btn-default pull-right">
                        Voir le<?php echo (count($infos) > 1) ? 's' : '' ?> <?php echo count($infos) ?> mouvement<?php echo (count($infos) > 1) ? 's' : '' ?>
                        <span class="glyphicon glyphicon-chevron-right"></span>
                    </a>
                </td>
            </tr>
        <?php endforeach ?>
      </tbody>
    </table>
</div>
