API
===

This API works through https procotol with http authentification.

| Information      | Description 	                                |
|------------	   |--------------------------------------------	|
| Protocol         | HTTPS 	                                    |
| Authentification | HTTP Authentification 	|
| Production URL   | https://declaration.syndicat-cotesdurhone.com/api.php 	|

    > curl https://user:password@declaration.syndicat-cotesdurhone.com/api.php/

Company information
--------------------

Get the company's information from its identifier.

    /export/societe/timbre?login={identifier}&type={type}

| Parameters 	| Description                              	    |
|------------	|--------------------------------------------	|
| login      	| Login or identifier of the company 	        |
| type       	| Response data type : csv (default) or json 	|

    > curl https://user:password@declaration.syndicat-cotesdurhone.com/api.php/export/societe/timbre?login=your_login&type=json

    HTTP/1.1 200 OK
    Date: Mon, 28 Oct 2019 16:00:24 GMT
    Content-Type: text/json; charset=utf-8

    {
        "identifiant":"000000",
        "intitule":null,
        "raison_sociale":"The company",
        "adresse_1":"40 rue Laffitte",
        "adresse_2":"",
        "adresse_3":"",
        "code_postal":"75009",
        "commune":"Paris",
        "pays":null,
        "code_comptable":"000000",
        "naf":"",
        "siret":"1234656789",
        "no_tva_intracommunautaire":"",
        "telephone":"0101010101",
        "telephone_mobile":"",
        "fax":"",
        "email":"email@email.com",
        "site_internet":"",
        "region":"",
        "type":"AUTRE",
        "statut":"ACTIF",
        "date_modification":"2019-03-06",
        "commentaire":""
    }

    > echo "If the company isn't authorized to access."
    > curl https://user:password@declaration.syndicat-cotesdurhone.com/api.php/export/societe/timbre?login=your_login&type=json

    HTTP/1.1 403 Forbidden
    Date: Mon, 28 Oct 2019 16:00:24 GMT
