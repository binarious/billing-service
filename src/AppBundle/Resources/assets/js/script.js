$('[rel=popover]').popover();
$('[title]:not([rel=popover])').tooltip();

$('.datepicker, [data-date]').datepicker({
    format: 'dd.mm.yyyy',
    startView: new Date(),
    language: 'de',
    autoclose: true
});
