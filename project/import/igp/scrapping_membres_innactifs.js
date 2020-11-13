var Nightmare = require('nightmare');
var fs = require('fs');
const nightmare = Nightmare({ show: true
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


  //list_membres_innactifs

  .evaluate(()=>{
    var elements = Array.from(document.querySelectorAll('a'))
    elements[122].className += "membres_innactifs";
  })
  .click('.membres_innactifs')
  .wait('#Button1')
  .click('#Button1')
  .wait('#gvMembre')
  .evaluate(()=>{
    var elements = document.querySelector('#gvMembre').innerText
    return elements;
    })
  .end()
  .then((text) => {
    fs.writeFileSync(destination_file+'membres_innactifs.txt',text);
  })

  //fin list_membres_innactifs

    .catch(error => {
      console.error('Search failed:', error)
    })
