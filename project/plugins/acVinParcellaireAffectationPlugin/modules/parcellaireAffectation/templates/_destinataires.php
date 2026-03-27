<?php $coop_id = (isset($coop)) ? explode('-', $coop)[1] : null; ?>
<ul class="nav nav-tabs mt-4">
<?php foreach($destinataires as $id => $d):
    if (count($produits) > 1):
        foreach ($produits as $hash => $produit):
    ?>
    <li role="presentation" class="<?php if($id.$hash == $destinataire.$hashproduit): ?>active<?php endif; ?><?php if ($coop_id && strpos($id, $coop_id) === false): ?>disabled<?php endif; ?>">
        <a class="onglet-presentation" data-form="validation-form" data-href="<?php echo url_for(ParcellaireAffectationEtapes::getInstance()->getRouteLink($etape), ['sf_subject' => $parcellaireAffectation, 'destinataire' => $id, 'hashproduit' => $hash]) ?>" href='#'>
            <?php if($id == $parcellaireAffectation->getEtablissementObject()->_id): ?><span class="glyphicon glyphicon-home"></span> <?php endif; ?><?php
            echo ($d['libelle_etablissement'] != 'Cave particulière') ? $d['libelle_etablissement'].' - ' : '';
            echo $produit; ?>
        </a>
    </li>
    <?php
        endforeach;
    else:
    ?>
    <li role="presentation" class="<?php if($id == $destinataire): ?>active<?php endif; ?><?php if ($coop_id && strpos($id, $coop_id) === false): ?>disabled<?php endif; ?>"><a class="onglet-presentation" data-form="validation-form" data-href="<?php echo url_for(ParcellaireAffectationEtapes::getInstance()->getRouteLink($etape), ['sf_subject' => $parcellaireAffectation, 'destinataire' => $id]) ?>" href="#"><?php if($id == $parcellaireAffectation->getEtablissementObject()->_id): ?><span class="glyphicon glyphicon-home"></span> <?php endif; ?><?php echo $d['libelle_etablissement'] ?></a></li>
    <?php endif; ?>
<?php endforeach; ?>
</ul>

<script>
    document.querySelectorAll("a[class^=onglet-presentation]").forEach(function (el) {
        el.addEventListener("click", () => {
            let form = document.querySelector("#" + el.dataset.form);
            let input = document.createElement("input");
            input.setAttribute("type", "hidden");
            input.setAttribute("name", "service");
            input.setAttribute("value", el.dataset.href.substring(el.dataset.href.indexOf("destinataire")));
            form.append(input);
            form.submit();
        });
    });
</script>
