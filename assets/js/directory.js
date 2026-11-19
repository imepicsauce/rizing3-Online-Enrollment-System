$(function () {
    var directory = $('#datatables').dataTable({
        sPaginationType: 'full_numbers',
        aaSorting: [[2, 'desc']],
        oLanguage: { sSearch: 'Search records', sEmptyTable: 'No records yet.' }
    });
    // Include paste and the native search-clear control in the legacy table filter.
    $('.dataTables_filter input').on('input', function () {
        directory.fnFilter(this.value);
    });
});
