$('document').ready(function() {

    // Get suggestion list of words and display translation for selected word
    $('#word').focus(function(){
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "/autocomplete",
                    type: "GET",
                    dataType: "json",
                    data: { term: request.term },
                    success: function( data ) { response(data); }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $.get({
                    url: "/translation",
                    dataType: "html",
                    data: { word: ui.item.value },
                    success: function (data) {
                        $("#translations").html(data);
                    }
                });
            }
        });
    });


    // Clear input field
    $('#clearButton').click(function(){
        $('#word').val('');
        $("#translations").html('');
    });


    // Display translation for selected word
    $('#translateButton').click(function() {
        var word = $('#word').val();

        $.get({
            url: "/translation",
            dataType: "html",
            data: { word: word },
            success: function (data) {
                $( "#translations" ).html(data);
            }
        });
    });




});
