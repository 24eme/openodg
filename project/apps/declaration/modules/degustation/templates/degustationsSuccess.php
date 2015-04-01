<?php use_helper("Date"); ?>
<div>
    <section id="commissions">
        <div class="page-header text-center">
            <h2>Dégustation du <?php echo ucfirst(format_date($tournee->date, "P", "fr_FR")) ?></h2>
        </div> 
        <div class="row">
            <div class="col-xs-12">
                <div class="list-group">
                    <?php for($i = 1; $i <= $tournee->nombre_commissions; $i++): ?>
                    <a href="<?php echo url_for("degustation_degustation", array('sf_subject' => $tournee, 'commission' => $i)) ?>" class="list-group-item col-xs-12">
                        <div class="col-xs-10">
                        <strong class="lead">Commission <?php echo $i ?></strong><br />
                        </div>
                        
                        <!--<div class="col-xs-2 text-right">
                            <span style="font-size: 26px;" class="lead"></span>
                            <small>vins</small>
                        </div>-->
                    </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </section>
</div>