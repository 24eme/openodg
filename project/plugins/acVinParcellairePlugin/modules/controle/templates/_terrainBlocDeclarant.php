<div class="well">
    <div class="row">
        <div class="col-xs-12">
            <div class="row">
                <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">&nbsp;</div>
                <div style="margin-bottom: 5px" class="col-xs-9">
                    <h4 class="strong">{{ controleCourant.declarant.nom }}</h4>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="row">
                <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                    Ids&nbsp;:
                </div>
                <div style="margin-bottom: 5px" class="col-xs-9">
                    <span class="text-muted">{{ controleCourant.identifiant }} - CVI : {{ controleCourant.declarant.cvi }} - SIRET : {{ controleCourant.declarant.siret }}</span>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="row">
                <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                    Adresse&nbsp;:
                </div>
                <div style="margin-bottom: 5px" class="col-xs-9">
                    <address style="margin-bottom: 0;">
                        {{ controleCourant.declarant.adresse }} {{ controleCourant.declarant.code_postal }} {{ controleCourant.declarant.commune }}
                    </address>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="row">
                <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                    Contact&nbsp;:
                </div>
                <div style="margin-bottom: 5px" class="col-xs-9">
                    <a href="mailto:{{ controleCourant.declarant.email }}">{{ controleCourant.declarant.email }}</a> / <a href="callto:{{ controleCourant.declarant.telephone_bureau }}">{{ controleCourant.declarant.telephone_bureau }}</a> / <a href="callto:{{ controleCourant.declarant.telephone_mobile }}">{{ controleCourant.declarant.telephone_mobile }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
