<script>
    window.addEventListener('load', function() {
        $(function() {
            var notesTable = $('#notesTable').DataTable({
                scrollY: '25vh',
                createdRow: function(row, data, index) {
                    $(row).attr('id', 'notes-row-' + data.id);
                },
                columns: [{
                        data: 'id',
                    },
                    {
                        data: 'notes',
                        className: 'td-notes'
                    },
                    {
                        data: 'id',
                        width: '0%;',
                        className: 'td-action',
                        render: function(data, type, row) {
                            return `
                                <button type="button" class="btn btn-primary btn-update-notes" data-id="` + data + `" data-adviser_id = "` + row.adviser_id + `" data-toggle="tooltip" title="Update Notes">
                                    <i class="fas fa-pen"></i>
                                </button>&nbsp;
                                <button type="button" class="btn btn-danger btn-delete-notes" data-id="` + data + `" data-toggle="tooltip" title="Update Notes">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            `;
                        },
                    }
                ],
                columnDefs: [{
                    targets: [0],
                    visible: false,
                    searchable: false,
                }, {
                    targets: [1, 2],
                    orderable: false,
                    className: 'text-left',
                }],
                order: [
                    [0, 'desc']
                ],
            });

            $(document).on('click', '.btn-view-notes', function() {
                $('#notes_adviser_id').val($(this).data('id'));

                var adviserName = $(this).data('name');

                $.get('advisers', {
                    action: 'listNotes',
                    adviser_id: $(this).data('id'),
                }).always(function(response) {
                    var response = JSON.parse(response);

                    notesTable.clear();

                    notesTable.rows.add(response).draw();

                    $('#notes').val('');

                    $('#notesModalTitle').text(adviserName + ' Notes');

                    $('#notesModal').modal('show');
                });
            });

            $('#notesModal').on('shown.bs.modal', function() {
                notesTable.draw();
            });

            $('#notesForm').on('submit', function(event) {
                event.preventDefault();

                var data = [];

                $(this).serializeArray().forEach(function(item) {
                    data[item.name] = item.value;
                });

                data = Object.assign({}, data);

                data.action = 'createNote';

                $.post('advisers', data).always(function(response) {
                    var response = JSON.parse(response);

                    data.id = response.id;

                    $('#notes').val('');

                    notesTable.row.add(data).draw();
                });
            });

            $(document).on('click', '.btn-update-notes', function() {
                $('#update_id').val($(this).data('id'));

                $('#update_adviser_id').val($(this).data('adviser_id'));

                $('#update_notes').val($(this).parent().parent().find('.td-notes').text());

                $('#updateNotesModal').modal('show');
            });

            $('#updateNotesForm').on('submit', function(event) {
                event.preventDefault();

                var data = [];

                $(this).serializeArray().forEach(function(item) {
                    data[item.name] = item.value;
                });

                data = Object.assign({}, data);

                data.action = 'updateNote';

                $.post('advisers', data).always(function() {
                    notesTable.row('#notes-row-' + data.id).data(data).draw();

                    $('#updateNotesModal').modal('hide');
                });
            });

            $(document).on('click', '.btn-delete-notes', function() {
                $('#delete_id').val($(this).data('id'));

                $('#deleteNotesModal').modal('show');
            });

            $('#btnDeleteNotes').on('click', function() {
                var data = {
                    id: $('#delete_id').val(),
                    action: 'deleteNote',
                };

                $.post('advisers', data).always(function() {
                    notesTable.row('#notes-row-' + data.id).remove().draw();

                    $('#deleteNotesModal').modal('hide');
                });
            })
        });
    });
</script>
