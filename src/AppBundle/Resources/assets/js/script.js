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

function addItemsForm($collectionHolder, $newLink) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newForm = $('<tr></tr>').append(newForm);
    $newLink.before($newForm);
}

$itemsTable = $('#itemsTable');
if ($itemsTable.length > 0) {
    var $collectionHolder;

    // setup an "add a tag" link
    var $addTagLink = $('<a href="#" class="btn btn-default">Position hinzufügen</a>');
    var $newLink = $('<tr><td colspan=3></td></tr>').append($addTagLink);

    $(function() {
        // Get the ul that holds the collection of tags
        $collectionHolder = $('.items');

        // add the "add a tag" anchor and li to the tags ul
        $collectionHolder.append($newLink);

        // count the current form inputs we have (e.g. 2), use that as the new
        // index when inserting a new item (e.g. 2)
        $collectionHolder.data('index', $collectionHolder.find(':input').length);

        $addTagLink.on('click', function(e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // add a new tag form (see next code block)
            addItemsForm($collectionHolder, $newLink);
        });
    });
}

$('#sendPdf').click(function(evt) {
    return confirm('Sind Sie sicher?');
});
