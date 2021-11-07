define([], function() {
    window.requirejs.config({
        paths: {
            "datatables": M.cfg.wwwroot + '/blocks/eledia_adminexamdates/js/datatables/datatables.min',
            "calendar": M.cfg.wwwroot + '/blocks/eledia_adminexamdates/js/calendar/dist/js/jquery-calendar.min',
        },
        shim: {
            'datatables': {exports: 'DataTable'},
            'calendar': {exports: 'calendar'},
        }
    });
});