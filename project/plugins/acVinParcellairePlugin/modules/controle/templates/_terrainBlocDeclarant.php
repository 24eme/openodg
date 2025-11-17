<h4 class="strong">{{ controleCourant.declarant.nom }}</h4>
<p>{{ controleCourant.declarant.adresse }}<br />{{ controleCourant.declarant.code_postal }} {{ controleCourant.declarant.commune }}</p>
<p>
<a href="mailto:{{ controleCourant.declarant.email }}">{{ controleCourant.declarant.email }}</a><br />
<a href="callto:{{ controleCourant.declarant.telephone_bureau }}">{{ controleCourant.declarant.telephone_bureau }}</a><br /><a href="callto:{{ controleCourant.declarant.telephone_mobile }}">{{ controleCourant.declarant.telephone_mobile }}</a>
</p>
