/**
 * VUT MainRes Maintenance - Root translations.js entry point
 * Loads / references assets/js/translations.js
 */
(function() {
    if (typeof window.I18N !== 'undefined') return;
    var script = document.createElement('script');
    var isSubdir = window.location.pathname.indexOf('/student/') !== -1 || 
                   window.location.pathname.indexOf('/admin/') !== -1 || 
                   window.location.pathname.indexOf('/maintenance/') !== -1;
    script.src = (isSubdir ? '../' : '') + 'assets/js/translations.js';
    document.head.appendChild(script);
})();
