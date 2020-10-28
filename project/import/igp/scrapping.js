
var Nightmare = require('nightmare');
require('nightmare-inline-download')(Nightmare);
var fs = require('fs');
var mkdirp = require("mkdirp");
const nightmare = Nightmare({ show: true})
var config = require('./config.json');
var destination_file='imports/'+config.file_name+'/';

mkdirp('imports/'+config.file_name);

nightmare

  //authentification
  .goto(config.web_site)
  .type('#LoginPhp',config.user_name)
  .type('#PasswordPhp',config.user_password)
  .click('#identification')
  .wait(3000)
  //fin authentification


  //operateurs
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[4].className += "ordre_alphabetiques";
  })
  .click('.ordre_alphabetiques')
  .wait(2000)
  .click('#Button2')
  .download(destination_file+'operateurs.xlsx')
  .wait(2000)
  // fin operateurs



  //apporteurs de raisins
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[6].className += "apporteurs_de_raisins";
  })
  .click('.apporteurs_de_raisins')
  .wait(2000)
  .click('#Button2')
  .download(destination_file+'apporteurs_de_raisins.xlsx')
  .wait(2000)

  //fin apporteurs de raisins



  //addresses courrier
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[7].className += "addresses_courrier_operateurs";
  })
  .click('.addresses_courrier_operateurs')
  .wait(2000)
  .click('#Button2')
  .download(destination_file+'addresses_courrier_operateurs.xlsx')
  .wait(2000)

  //fin apporteurs de raisins



  //operateurs innactifs
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[9].className += "operateurs_innactifs";
  })
  .click('.operateurs_innactifs')
  .wait(2000)
  .click('#btnExportExcel')
  .download(destination_file+'operateurs_innactifs.xlsx')
  .wait(2000)
  //fin operateurs innactifs

  //contacts
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[12].className += "list_contacts";
  })
  .click('.list_contacts')
  .wait(2000)
  .click('#ContentPlaceHolder1_btnExcel')
  .download(destination_file+'contacts.xlsx')
  .wait(2000)
  // fin contacts

  //habilitation
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[19].className += "habilitation";
  })
  .click('.habilitation')
  .wait(2000)
  .click('#btExportExcel')
  .download(destination_file+'habilitations.xlsx')
  .wait(2000)
  //fin habilitation


  //scraping lots
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[63].className += "lots_declarations";
    })
   .click('.lots_declarations')
   .wait(2000)
   .click('#btnEE')
   .download(destination_file+'lots_2020-2021.xlsx')

   .select('#ddlCamp','')
   .click('#btnEE')
   .download(destination_file+'lots.xlsx')

   .select('#ddlCamp','2019/2020')
   .click('#btnEE')
   .download(destination_file+'lots_2019-2020.xlsx')

   .select('#ddlCamp','2018/2019')
   .click('#btnEE')
   .download(destination_file+'lots_2018-2019.xlsx')

   .select('#ddlCamp','2017/2018')
   .click('#btnEE')
   .download(destination_file+'lots_2017-2018.xlsx')

   .select('#ddlCamp','2016/2017')
   .click('#btnEE')
   .download(destination_file+'lots_2016-2017.xlsx')

   .wait(2000)
  //fin lots

  //changement de dénomination
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[56].className += "changement_denom";
    })
  .click('.changement_denom')
  .click('#Button1')
  .download(destination_file+'changement_denom_2020-2021.xlsx')

  .select('#ddlCampagne','')
  .click('#Button1')
  .download(destination_file+'changement_denom.xlsx')

   .select('#ddlCampagne','2019')
   .click('#Button1')
   .download(destination_file+'changement_denom_2019-2020.xlsx')

   .select('#ddlCampagne','2018')
   .click('#Button1')
   .download(destination_file+'changement_denom_2018-2019.xlsx')

   .select('#ddlCampagne','2017')
   .click('#Button1')
   .download(destination_file+'changement_denom_2017-2018.xlsx')

   .select('#ddlCampagne','2016')
   .click('#Button1')
   .download(destination_file+'changement_denom_2016-2017.xlsx')
   //fin changement de dénomination

   //changement denom autre igp
   .evaluate(()=>{
     var elements = Array.from(document.querySelectorAll('a'))
     elements[57].className += "changement_denom_autre_igp";
     })
    .click('.changement_denom_autre_igp')
    .click('#btnExcel')
    .download(destination_file+'changement_denom_autre_igp_2020-2021.xlsx')

    .select('#ddlCampagne','')
    .wait(2000)
    .click('#btnExcel')
    .download(destination_file+'changement_denom_autre_igp.xlsx')

     .select('#ddlCampagne','2019')
     .wait(2000)
     .click('#btnExcel')
     .download(destination_file+'changement_denom_autre_igp_2019-2020.xlsx')

     .select('#ddlCampagne','2018')
     .wait(2000)
     .click('#btnExcel')
     .download(destination_file+'changement_denom_autre_igp_2018-2019.xlsx')

     .select('#ddlCampagne','2017')
     .wait(2000)
     .click('#btnExcel')
     .download(destination_file+'changement_denom_autre_igp_2017-2018.xlsx')

     .select('#ddlCampagne','2016')
     .wait(2000)
     .click('#btnExcel')
     .download(destination_file+'changement_denom_autre_igp_2016-2017.xlsx')
     //fin changement denom autre igp



     //produits
     .evaluate(()=>{
       var elements = Array.from(document.querySelectorAll('a'))
       elements[117].className += "produits";
     })
      .click('.produits')   //pour donner accès au lien sinon site en maintenance
      .goto(config.web_site_produits)
      .click('#btnAOC')
      .evaluate(()=>{
        var elements = document.querySelector('#ContentPlaceHolder1_GridView1').innerText
        return elements;
        })
      .end()
      .then((text) => {
        fs.writeFileSync(destination_file+'produits.txt',text);
      })
     //fin produits

  .catch(error => {
    console.error('Search failed:', error)
  })
