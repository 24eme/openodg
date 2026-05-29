<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_COMMISSION_EXTERNE)); ?>

<div class="page-header no-border">
    <h2>Commission externe</h2>
</div>

<div class="panel panel-default" style="min-height: 160px">
    <div class="panel-heading">
        <h2 class="panel-title">Documents nécessaires à l'organisation d'une commission externalisée</h2>
    </div>
    <div class="panel-body">
        <h4>Tiers</h4>
        <ul class="list-group">
            <li class="list-group-item">
                <a id="btn_csv_etiquette" href="<?php echo url_for('degustation_etiquette_csv', $degustation) ?>"><span class="glyphicon glyphicon-list"></span>&nbsp;Tableur des lots pour les prestataires</a>
            </li>
        </ul>
    </div>
</div>


<?php include_partial('degustation/pagination', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_COMMISSION_EXTERNE, 'is_enabled' => true)); ?>
