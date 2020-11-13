var Nightmare = require('nightmare');
var fs = require('fs');
const nightmare = Nightmare({ show: false
})
var config = require('./config.json');
var destination_file='imports/'+config.file_name+'/';


nightmare

  //authentification
  .goto(config.web_site)
  .type('#LoginPhp',config.user_name)
  .type('#PasswordPhp',config.user_password)
  .click('#identification')
  .wait('.menu')
  //fin authentification


  //cépages
  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[117].className += "cepages";
  })
   .click('.cepages')   //pour donner accès au lien sinon site en maintenance
   .goto(config.web_site_produits)
   .refresh()
   .wait(3000)
   .wait('#btnCepage')
   .click('#btnCepage')
   .wait('#ContentPlaceHolder1_gvCepage')
   .evaluate(()=>{
     var elements = document.querySelector('#ContentPlaceHolder1_gvCepage').innerText
     return elements;
     })
   .end()
   .then((text) => {
     fs.writeFileSync(destination_file+'cépages.txt',text);
  })
  // fin cépages


    .catch(error => {
      console.error('Search failed:', error)
    })
