<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li><a href="">Tournée du <?php echo Date::francizeDate($dateTournee); ?></a></li>
  <li class="active"><a href="">Liste des opérateurs contrôlés</a></li>
</ol>

<h2>Opérateurs controlés dans la tournée du <?php echo Date::francizeDate($dateTournee); ?></h2>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-xs-6">Opérateurs</th>
        <th class="col-xs-2">Type de tournée</th>
        <th class="col-xs-4" colspan="2">PDF</th>
        <th style="width: 0;"></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($controles as $id => $data): ?>
        <tr>
            <td>
                <a href="<?php echo url_for("controle_liste_manquements_controle", array('id' => $data->_id)) ?>"><?php echo $data->declarant->raison_sociale ?></a>
            </td>
            <td><?php echo $data->type_tournee; ?></td>
            <td><a href="<?php echo url_for('controle_pdf', array('id' => $data->_id)); ?>">PDF du contrôle</a></td>
            <td><a href="<?php echo url_for('controle_pdf_manquements', array('id' => $data->_id)); ?>">PDF des manquements</a></td>
            <td class="text-right"><a class="btn btn-xs btn-default" href="<?php echo url_for('controle_liste_manquements_controle', array('id' => $data->_id)); ?>">Gérer les manquements</a></td>
            <td class="text-right">
                <div class="btn-group">
                  <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Notifier <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu text-left">
                    <li>
                        <a class="btn" href="" data-toggle="modal" data-target="#mailPreviewModal">
                          <i class="glyphicon glyphicon-envelope"></i>&nbsp;Envoyer par mail
                        </a>
                    </li>
                    <li>
                      <a href="" class="btn" data-toggle="modal" data-target="#mailPreviewModal">
                          <i class="glyphicon glyphicon-eye-open"></i>&nbsp;Prévisualiser
                      </a>
                    </li>
                  </ul>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="row">
    <div class="col-xs-4"><a class="btn btn-default" href="<?php echo url_for('controle_index'); ?>"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
</div>

<?php $email = "test@test.fr" ?>
<?php $subject = "Contrôle terrain" ?>
<?php $cc = "" ?>
<div id="mailPreviewModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3 class="modal-title"><?php echo $subject; ?></h3>
        <h4><span style="text-decoration: underlined dotted 1px black">Destinataire :</span> <?php echo $email ?></h4>
        <h4>Copie : <?php echo $cc ?></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12">
            <pre style="white-space: pre-wrap;">
Bonjour,

Suite au contrôle terrain, veuillez trouver les documents suivants :

- https://lien_vers_le_pdf_de_controle
- https://lien_vers_le_pdf_des_manquements

Bien cordialement,

Le syndicat
            </pre>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div class="row">
            <div class="col-md-4 text-left">
                <a class="btn btn-default" href="">Annuler</a>
            </div>
            <div class="col-md-4 text-center">
                <div class="btn-group">
                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Voir les PDF <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu text-left">
                    <li><a href="">PDF du contrôle</a></li>
                    <li><a href="">PDF des manquements</a></li>
                  </ul>
                </div>
            </div>
            <div class="col-md-4">
                <a class="btn btn-primary" href="">
                  <i class="glyphicon glyphicon-envelope"></i>&nbsp;Envoyer par mail
                </a>
            </div>
      </div>
  </div>
</div>
</div>
</div>
