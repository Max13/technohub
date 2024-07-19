require('./bootstrap');

const bootstrap = require('bootstrap');

(() => {
    Array.from(document.getElementsByTagName('form'))
         .forEach(form => {
             Array.from(form.getElementsByClassName('login-with'))
                  .forEach(btn => {
                      form.addEventListener('submit', () => {
                          btn.classList.add('loading');
                          btn.disabled = true;
                      });
                  });
         });
})();
