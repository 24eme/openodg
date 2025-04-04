<?php use_helper('TemplatingPDF'); ?>
<?php use_helper('Lot') ?>
<style>
<?php echo style(); ?>

.td, .th {
    border: 1px solid #000;
}

.align-right {
    text-align: right;
}

.align-left {
    text-align: left;
}

.align-mid {
    text-align: center;
}

.text-red {
    color: red;
}

.text-huit-pt {
    font-size: 8pt;
}

.text-dix-pt {
    font-size: 10pt;
}

.text-six-pt {
    font-size: 6pt;
}

.size-cepage {
    height: 25px;
}

.size-commentaire {
    height: 60px;
}

.fond-sombre {
    background-color: grey;
}

.encart-nom {
    padding: 0px;
    margin: 0px;
}

</style>

<!-- <?php echo include_partial('degustation/IGPficheIndividuellePdf', array());?> -->
<?php echo include_partial('degustation/AOCficheIndividuellePdf', array());?>
