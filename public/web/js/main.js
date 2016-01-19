/**
 * Created by horizon on 18/01/16.
 */


$(document).ready(function () {
    // Initialisation du plugin DataTable
    var table = initTable();



    // Selection d'un postit et affichade d'un formulaire
    $('#tab_postit tbody').on( 'click', 'tr', function () {
        // Récuperation de la ligne sélectionne
        var row = table.row( this ).data();

        $("#trigger_upd").trigger("click");

        // Affectation des valeurs du postit dans le formulaire
        $('#updPostitForm #id').val(row.id);
        $('#updPostitForm #title').val(row.title);
        $('#updPostitForm #content').val(row.content);
        $('#updPostitForm #color').val(row.color);
    } );

    // Initialisation des modals
    $("a[rel*=leanModal]").leanModal({ top : 50, overlay : 0.4, closeButton: ".modal_close" });

    // Capture de la validation d'un formulaire
    $('#addPostitForm').submit(function(){
        $.ajax({
            url: '/api/v1/postits',
            type: 'POST',
            data : $('#addPostitForm').serialize(),
            success: function(){
                console.log('form submitted.');
                $("#lean_overlay").trigger("click");
                $("#addPostitForm")[0].reset();

                table.destroy();
                table = initTable();
            }
        });
        return false;
    });

    // Validation des formulaires
    $('#updPostitForm').submit(function(){
        $.ajax({
            url: '/api/v1/postits/'+$('#updPostitForm #id').val(),
            type: 'PUT',
            data : $('#updPostitForm').serialize(),
            success: function(){
                console.log('form submitted.');
                $("#lean_overlay").trigger("click");
                $("#updPostitForm")[0].reset();
                table.destroy();
                table = initTable();
            }
        });
        return false;
    });

    $('#updPostitForm').on('click', '#dltbtn', function(){
        $.ajax({
            url: '/api/v1/postits/'+$('#updPostitForm #id').val(),
            type: 'DELETE',
            success: function(){
                console.log('form submitted.');
                $("#lean_overlay").trigger("click");
                $("#updPostitForm")[0].reset();

                table.destroy();
                table = initTable();
            }
        });
        return false;
    });

    // Trie *
    $('.filterPostit').on('click', function(){
        var column = $(this).attr('role');
        var order = $(this).attr('rel');

        table.destroy();
        table = filterTable(column, order);
    });

    $('#searchForm').submit( function(){
        table.destroy();
        table = $('#tab_postit').DataTable({
            ajax: {
                url : "/api/v1/postits/search/"+$('#searchPostit').val(),
                dataSrc: 'data'
            },
            columns:[
                { "data": "id" },
                { "data": "title" },
                { "data": "content" },
                { "data": "color" },
                { "data": "date" }
            ],
            order: []
        });
        return false;
    });


});

function initTable(){
    var table = $('#tab_postit').DataTable({
        ajax: {
            url : "/api/v1/postits",
            dataSrc: 'data'
        },
        columns:[
            { "data": "id" },
            { "data": "title" },
            { "data": "content" },
            { "data": "color" },
            { "data": "date" }
        ]
    });

    // Cacher l'id
    table.column(0).visible(false);

    return table;
}

function filterTable(column, order){
    var table = $('#tab_postit').DataTable({
        ajax: {
            url : "/api/v1/postits/search/"+column+"/"+order,
            dataSrc: 'data'
        },
        columns:[
            { "data": "id" },
            { "data": "title" },
            { "data": "content" },
            { "data": "color" },
            { "data": "date" }
        ],
        order: []
    });

    // Cacher l'id
    table.column(0).visible(false);

    return table;
}