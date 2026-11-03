{{-- Gaya bersama kartu & tabel aplikasi --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<style>
    .app-card,
    .sidang-card {
        border: 0;
        border-top: 3px solid #007bff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
    }

    .app-card > .card-header,
    .sidang-card > .card-header {
        background: #ffffff;
        border-bottom: 1px solid #eef0f3;
        padding: 14px 20px;
    }

    .app-card .card-title-icon,
    .sidang-card .card-title-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: rgba(0, 123, 255, .1);
        color: #007bff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-size: 16px;
        flex-shrink: 0;
    }

    .app-card .card-header h4,
    .sidang-card .card-header h4 {
        font-weight: 600;
        font-size: 17px;
        margin: 0;
    }

    .app-card .card-header small,
    .sidang-card .card-header small {
        color: #6c757d;
    }

    table.dataTable {
        border-collapse: collapse !important;
    }

    table.dataTable thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6 !important;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #495057;
        white-space: nowrap;
        vertical-align: middle;
    }

    table.dataTable tbody td {
        vertical-align: middle;
        font-size: 14px;
    }

    table.dataTable.table-striped tbody tr:nth-of-type(odd) {
        background-color: #fbfcfd;
    }

    table.dataTable tbody tr:hover {
        background-color: #f1f7ff !important;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 4px 12px;
        margin-left: 0;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 6px;
    }

    .dataTables_wrapper .dataTables_info {
        font-size: 13px;
        color: #6c757d;
    }

    .btn-aksi {
        white-space: nowrap;
    }

    .btn-aksi .btn {
        width: 34px;
        padding-left: 0;
        padding-right: 0;
    }

    .badge-soft-primary {
        background: rgba(0, 123, 255, .12);
        color: #0b5ed7;
    }

    .badge-soft-info {
        background: rgba(23, 162, 184, .14);
        color: #117a8b;
    }

    .badge-soft-success {
        background: rgba(40, 167, 69, .14);
        color: #1e7e34;
    }

    .badge-soft-warning {
        background: rgba(255, 193, 7, .18);
        color: #b38600;
    }

    .badge-soft-danger {
        background: rgba(220, 53, 69, .12);
        color: #c82333;
    }

    .badge-soft-secondary {
        background: rgba(108, 117, 125, .14);
        color: #5a6268;
    }

    .badge-rubrik {
        background: #f1f3f5;
        border: 1px solid #dee2e6;
        color: #495057;
        font-weight: 500;
        margin: 1px 3px 1px 0;
    }
</style>
