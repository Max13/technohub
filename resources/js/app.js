require('./bootstrap');

const bootstrap = require('bootstrap');

(() => {
    // Show aria-hidden=false modals
    document.querySelectorAll('.modal[aria-hidden=false]')
            .forEach(modal => {
                (new bootstrap.Modal(modal)).show();
            });

    // Show tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Show loading on .login-with buttons
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
