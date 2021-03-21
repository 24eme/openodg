var fs = require('fs');
var fileHTML = process.argv[2];
const cheerio = require('cheerio');
const $ = cheerio.load(fs.readFileSync(fileHTML));

var date = $('#lblDtCom').text();
var code = $('#lblCommission').text();
var campagne = $('#lblCampagne').text();
var millesime = $('#lblMillesime').text();
var responsable = $('#lblResponsable').text();
var lieu_nom = $('#lblLCom').text();
var lieu_adresse = $('#lblAdresse').text();
var lieu_code_postal = $('#lblCP').text();
var lieu_ville = $('#lblVille').text();

var baseLigne = date + ";" + code + ";" + campagne + ";" + millesime + ";" + responsable + ";" + lieu_nom + ";" + lieu_adresse + ";" + lieu_code_postal + ";" + lieu_ville;

var cleanChamps = function(value) {
    value = value.replace(/\n/g, "");
    value = value.replace(/\r/g, "");
    value = value.replace(/[ ]+/g, " ");
    value = value.replace(/^ /, "");
    value = value.replace(/ $/, "");
    value = value.replace(/^NULL$/, "");
    return value;
}

$('table#gvPrelev tr').each(function() {
    if(!$(this).find('td').length) {
        return;
    }

    if($(this).hasClass('gvListeFooter')) {
        return;
    }

    var operateur = cleanChamps($(this).find('td').eq(1).text());
    var appellation = cleanChamps($(this).find('td').eq(2).text().split("\n")[1]);
    var couleur = cleanChamps($(this).find('td').eq(2).text().split("\n")[3]);
    var cepage = cleanChamps($(this).find('td').eq(3).text());
    var volume = cleanChamps($(this).find('td').eq(4).text());
    var logement = cleanChamps($(this).find('td').eq(5).text());
    var type_lot = cleanChamps($(this).find('td').eq(6).text());
    var passage = cleanChamps($(this).find('td').eq(7).text());
    var degre = cleanChamps($(this).find('td').eq(8).text());
    var doc = cleanChamps($(this).find('td').eq(9).text());
    var numero_anonymat = cleanChamps($(this).find('td').eq(10).text());
    var conformite = cleanChamps($(this).find('td').eq(11).text());
    var motif_refus = cleanChamps($(this).find('td').eq(12).text());
    var commentaire = cleanChamps($(this).find('td').eq(13).text());

    console.log(baseLigne + ";LOT;" + operateur + ";" + appellation + ";" + couleur + ";"  + cepage + ";" + volume + ";" + logement + ";" + type_lot + ";" + passage + ";" + degre + ";" + doc + ";" + numero_anonymat + ";" + conformite + ";" + motif_refus + ";" + commentaire);
});

$('table#gvMembre tr').each(function() {
  if(!$(this).find('td').length) {
      return;
  }

  if($(this).hasClass('gvListeFooter')) {
      return;
  }

  var jury = cleanChamps($(this).find('td').eq(0).text());
  var college = cleanChamps($(this).find('td').eq(1).text());
  var telephone = cleanChamps($(this).find('td').eq(2).text());
  var courriel = cleanChamps($(this).find('td').eq(3).text());
  var presence = cleanChamps($(this).find('td').eq(4).text());

  console.log(baseLigne + ";JURY;" + jury + ";" + college + ";" + telephone + ";" + courriel + ";" + presence);
});
