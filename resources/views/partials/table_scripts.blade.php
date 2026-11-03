{{-- Script bersama tabel aplikasi (DataTables + SweetAlert) --}}
@include('sweetalert::alert')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $.extend(true, $.fn.dataTable.defaults, {
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        dom: "<'row align-items-center mb-2'<'col-sm-6'l><'col-sm-6 text-sm-right'f>>" +
            "<'table-responsive't>" +
            "<'row align-items-center mt-3'<'col-sm-6'i><'col-sm-6'p>>",
        language: {
            processing: '<span class="spinner-border spinner-border-sm mr-2"></span> Memuat...',
            search: '',
            searchPlaceholder: 'Cari...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(disaring dari _MAX_ data)',
            zeroRecords: 'Data tidak ditemukan',
            emptyTable: 'Belum ada data',
            paginate: { first: '&laquo;', last: '&raquo;', next: '&rsaquo;', previous: '&lsaquo;' }
        }
    });

    const escapeHtml = value => $('<div>').text(value ?? '').html();

    function aksiButtons(buttons) {
        return '<div class="btn-group btn-aksi">' + buttons.join('') + '</div>';
    }

    function btnEditHtml(attrs) {
        return `<button type="button" class="btn btn-outline-primary btn-sm btnEdit" title="Edit" ${attrs}>
            <i class="fas fa-pen"></i></button>`;
    }

    function btnHapusHtml(id) {
        return `<button type="button" class="btn btn-outline-danger btn-sm btnHapus" title="Hapus" data-id="${id}">
            <i class="fas fa-trash"></i></button>`;
    }

    function konfirmasiHapus(options, onConfirm) {
        Swal.fire($.extend({
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }, options)).then(result => {
            if (result.isConfirmed) onConfirm();
        });
    }
</script>
