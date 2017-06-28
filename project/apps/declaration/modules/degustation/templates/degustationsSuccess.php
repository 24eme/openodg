<?php use_helper("Date"); ?>

<ol class="breadcrumb hidden-xs hidden-sm">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_visualisation', $tournee); ?>">Tournée <?php echo $tournee->getLibelle(); ?>  le <?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></a></li>
  <li class="active"><a href="">Commissions</a></li>
</ol>

<section id="commissions">
    <a href="<?php echo url_for("degustation_visualisation", $tournee) ?>" class="pull-left hidden-print"><span style="font-size: 30px" class="eleganticon arrow_carrot-left"></span></a>
    <div class="page-header text-center">
        <h2>Dégustation du <?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></h2>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="list-group">
                <?php foreach($tournee->getCommissions() as $commission): ?>
                <a href="<?php echo url_for("degustation_degustation", array('sf_subject' => $tournee, 'commission' => $commission)) ?>" class="list-group-item col-xs-12">
                    <div class="col-xs-10">
                    <strong class="lead">Commission <?php echo $commission ?></strong><br />
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
