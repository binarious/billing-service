$('[rel=popover]').popover();
$('[title]:not([rel=popover])').tooltip();

$('.datepicker, [data-date]').datepicker({
    format: 'dd.mm.yyyy',
    startView: new Date(),
    language: 'de',
    autoclose: true
});

$('.confirm-submit').on('click', function(evt) {
    evt.preventDefault();
    var form = $(this).parents('form');

    swal({
        title: "Sind Sie sicher, dass Sie fortfahren möchten?",
        text: "Die gewünschte Aktion kann nicht rückgängig gemacht werden!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Ja, fortfahren!",
        cancelButtonText: "Abbrechen",
        closeOnConfirm: true
    }, function() {
        form.submit();
    });
});
