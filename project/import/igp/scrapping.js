
var Nightmare = require('nightmare');
require('nightmare-inline-download')(Nightmare);
var fs = require('fs');

const nightmare = Nightmare({ show: false})
var config = require('./config.json');


nightmare

  //authentification
  .goto(config.web_site)
  .type('#LoginPhp',config.user_name)
  .type('#PasswordPhp',config.user_password)
  .click('#identification')
  .wait(3000)
  //fin authentification


  //scraping contacts
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[4].className += "ordre_alphabetiques";
  })
  .click('.ordre_alphabetiques')
  .wait(2000)
  .click('#Button2')
  .download('imports/contacts.xlsx')
  .wait(2000)
  //fin contacts

  //habilitation
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[19].className += "habilitation";
  })
  .click('.habilitation')
  .wait(2000)
  .click('#btExportExcel')
  .download('imports/habilitations.xlsx')
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
   .download('imports/lots_2020-2021.xlsx')

   .select('#ddlCamp','')
   .click('#btnEE')
   .download('imports/lots.xlsx')

   .select('#ddlCamp','2019/2020')
   .click('#btnEE')
   .download('imports/lots_2019-2020.xlsx')

   .select('#ddlCamp','2018/2019')
   .click('#btnEE')
   .download('imports/lots_2018-2019.xlsx')

   .select('#ddlCamp','2017/2018')
   .click('#btnEE')
   .download('imports/lots_2017-2018.xlsx')

   .select('#ddlCamp','2016/2017')
   .click('#btnEE')
   .download('imports/lots_2016-2017.xlsx')

   .wait(2000)
  //fin lots

  //changement de dénomination
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[56].className += "changement_denom";
    })
  .click('.changement_denom')
  .click('#Button1')
  .download('imports/changement_denom_2020-2021.xlsx')

  .select('#ddlCampagne','')
  .click('#Button1')
  .download('imports/changement_denom.xlsx')

   .select('#ddlCampagne','2019')
   .click('#Button1')
   .download('imports/changement_denom_2019-2020.xlsx')

   .select('#ddlCampagne','2018')
   .click('#Button1')
   .download('imports/changement_denom_2018-2019.xlsx')

   .select('#ddlCampagne','2017')
   .click('#Button1')
   .download('imports/changement_denom_2017-2018.xlsx')

   .select('#ddlCampagne','2016')
   .click('#Button1')
   .download('imports/changement_denom_2016-2017.xlsx')
   //fin changement de dénomination

   //changement denom autre igp
   .evaluate(()=>{
     var elements = Array.from(document.querySelectorAll('a'))
     elements[57].className += "changement_denom_autre_igp";
     })
    .click('.changement_denom_autre_igp')
    .click('#btnExcel')
    .download('imports/changement_denom_autre_igp_2020-2021.xlsx')

    .select('#ddlCampagne','')
    .wait(2000)
    .click('#btnExcel')
    .download('imports/changement_denom_autre_igp.xlsx')

     .select('#ddlCampagne','2019')
     .wait(2000)
     .click('#btnExcel')
     .download('imports/changement_denom_autre_igp_2019-2020.xlsx')

     .select('#ddlCampagne','2018')
     .wait(2000)
     .click('#btnExcel')
     .download('imports/changement_denom_autre_igp_2018-2019.xlsx')

     .select('#ddlCampagne','2017')
     .wait(2000)
     .click('#btnExcel')
     .download('imports/changement_denom_autre_igp_2017-2018.xlsx')

     .select('#ddlCampagne','2016')
     .wait(2000)
     .click('#btnExcel')
     .download('imports/changement_denom_autre_igp_2016-2017.xlsx')
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
        fs.writeFileSync('imports/produits.txt',text);
      })
     //fin produits

  .catch(error => {
    console.error('Search failed:', error)
  })
