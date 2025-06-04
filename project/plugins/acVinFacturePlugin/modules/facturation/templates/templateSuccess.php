<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
  <li class="active"><a href="">Template de facturation</a></li>
  <li class="active"><a href=""><?php echo $template->libelle ?></a></li>
</ol>

<h2>Configuration de la Facturation</h2>

<div> 
    <div class="row"><div class="col-xs-12 mb-5"><b>Période de l'exercice :</b> <?php echo FactureConfiguration::getInstance()->getExercice(); ?></div></div>
    <div class="row">
      <div class="col-xs-1">Nom&nbsp;Factu.&nbsp;:</div>
      <div class="col-xs-7"><?php echo $organisme->getNomFacturation(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">Mail&nbsp;Factu&nbsp;:</div>
      <div class="col-xs-7"><?php echo $organisme->getEmailFacturation(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">SIRET : </div>
      <div class="col-xs-7"><?php echo $organisme->getSiret(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">N°&nbsp;TVA&nbsp;Intra.&nbsp;:</div>
      <div class="col-xs-7"><?php echo $organisme->getNoTvaIntracommunautaire(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">Nom&nbsp;banq.&nbsp;:</div>
      <div class="col-xs-7"><?php echo $organisme->getBanqueNom(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">Adr.&nbsp;banq.&nbsp;:</div>
      <div class="col-xs-7"><?php echo $organisme->getBanqueAdresse(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">IBAN :</div>
      <div class="col-xs-7"><?php echo $organisme->getIban(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">BIC :</div>
      <div class="col-xs-7"><?php echo $organisme->getBic(); ?></div>
    </div>
    <div class="row">
      <div class="col-xs-1">Paiement&nbsp;:</div>
      <div class="col-xs-11"><?php echo FactureConfiguration::getInstance()->getModaliteDePaiement(); ?></div>
    </div>
</div>

<h2><?php echo $template->libelle ?></h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Libellé</th>
            <th>Compta</th>
            <th>Type</th>
            <th>Prix</th>
            <th>TVA</th>
            <th>Document</th>
            <th>Calcul de la Quantité</th>
            <th>Unité</th>
        </tr>
    </thead>
<?php foreach($lignes as $detail): ?>
    <?php $cotisation = $detail->getParent()->getParent(); ?>
    <tr>
        <td><?php echo $cotisation->libelle ?> <?php echo $detail->libelle ?>
        <?php if($detail->exist('date')): ?><br /><small class="text-muted">Date : <?php echo $detail->date ?></small><?php endif; ?></td>
        <td><?php echo $cotisation->code_comptable ?></td>
        <td><?php echo str_replace("Cotisation", "", $detail->modele) ?></td>
        <td class="text-right"><?php echo $detail->prix ?>&nbsp;€</td>
        <td class="text-right"><?php echo $detail->tva ?>&nbsp;€</td>
        <td><?php echo implode(",&nbsp;", $detail->docs->getRawValue()->toArray()) ?></td>
        <td><?php echo $detail->callback ?>
            <?php if($detail->exist('callback_parameters')): ?>
                <small class="text-muted">
                <?php foreach ($detail->callback_parameters as $cle => $parameter): ?>
                    <span title="<?php echo $cle ?>" style="cursor:help;text-decoration:underline dotted 1px"><?php echo $parameter ?></span>
                <?php endforeach ?>
                </small>
            <?php endif; ?>
        </td>
        <td class="text-left"><?php if($detail->exist('unite')): ?><?php echo $detail->unite ?><?php endif; ?></td>
    </tr>
<?php endforeach; ?>
</table>
