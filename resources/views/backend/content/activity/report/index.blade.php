@extends('backend.master')

@section('title')
    {{ ($business?->business_name ?? 'Medi Trust Solution') }} - Activity Report
@endsection

@section('maincontent')

<link
    href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
    rel="stylesheet"
/>

<style>
    .report-filter-card {
        border: 0;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .06);
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: 40px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 6px !important;
    }

    .select2-container--default
    .select2-selection--single
    .select2-selection__rendered {
        line-height: 38px !important;
    }

    .select2-container--default
    .select2-selection--single
    .select2-selection__arrow {
        height: 38px !important;
    }

    .summary-card {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, .05);
    }

    .summary-label {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .summary-value {
        font-size: 22px;
        font-weight: 700;
        color: #111827;
    }

    .column-box {
        max-height: 260px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
    }

    .report-table-wrapper {
        overflow-x: auto;
    }

    #reportTable {
        min-width: 1500px;
    }

    #reportTable th {
        white-space: nowrap;
        font-size: 12px;
        background: #f8fafc;
    }

    #reportTable td {
        font-size: 12px;
        vertical-align: middle;
    }

    .empty-report {
        padding: 45px 15px !important;
        text-align: center;
        color: #6b7280;
    }

    .loading-report {
        padding: 45px 15px !important;
        text-align: center;
    }

    @media (max-width: 767px) {
        .report-action-buttons {
            width: 100%;
        }

        .report-action-buttons .btn {
            width: 100%;
            margin-bottom: 6px;
        }
    }
</style>

<div class="nxl-content">

    <div class="page-header">
        <div
            class="page-header-left d-flex align-items-center"
        >
            <div class="page-header-title">
                <h5 class="m-b-10">Activity Report</h5>
            </div>

            <ul class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ url('/') }}">Home</a>
                </li>

                <li class="breadcrumb-item">
                    <a href="{{ route('activities.index') }}">
                        Activities
                    </a>
                </li>

                <li class="breadcrumb-item active">
                    Report
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">

        {{-- Filter --}}
        <div class="card report-filter-card">
            <div class="card-header">
                <h5 class="mb-0">
                    Report Filters
                </h5>
            </div>

            <div class="card-body">

                <form id="reportFilterForm">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label">
                                From Date
                            </label>

                            <input
                                type="date"
                                name="from_date"
                                id="from_date"
                                class="form-control"
                                value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                To Date
                            </label>

                            <input
                                type="date"
                                name="to_date"
                                id="to_date"
                                class="form-control"
                                value="{{ now()->format('Y-m-d') }}"
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                Created By / User
                            </label>

                            <select
                                name="created_by"
                                id="created_by"
                                class="form-control"
                            >
                                <option value="">
                                    All Users
                                </option>

                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                id="status"
                                class="form-control"
                            >
                                <option value="">
                                    All Status
                                </option>

                                <option value="pending">
                                    Pending
                                </option>

                                <option value="approved">
                                    Approved
                                </option>

                                <option value="rejected">
                                    Rejected
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Organization
                            </label>

                            <select
                                name="organization_id"
                                id="organization_id"
                                class="form-control"
                            >
                                <option value="">
                                    All Organizations
                                </option>

                                @foreach($organizations as $organization)
                                    <option
                                        value="{{ $organization->organization_id }}"
                                    >
                                        {{ $organization->organization_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Branch ID
                            </label>

                            <input
                                type="number"
                                name="branch_id"
                                id="branch_id"
                                class="form-control"
                                placeholder="Leave empty for all branches"
                            >
                        </div>

                        <div
                            class="col-md-4 d-flex align-items-end"
                        >
                            <div
                                class="report-action-buttons d-flex gap-2 flex-wrap"
                            >
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    id="btnFilter"
                                >
                                    <i class="feather-search"></i>
                                    Generate Report
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-light"
                                    id="btnReset"
                                >
                                    <i class="feather-rotate-ccw"></i>
                                    Reset
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#columnModal"
                                >
                                    <i class="feather-columns"></i>
                                    Fields
                                </button>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>

        {{-- Summary --}}
        <div class="row g-3 mb-3">

            <div class="col-lg-3 col-md-6">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="summary-label">
                            Total Activities
                        </div>

                        <div
                            class="summary-value"
                            id="summaryActivityCount"
                        >
                            0
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="summary-label">
                            Total TA
                        </div>

                        <div
                            class="summary-value"
                            id="summaryTa"
                        >
                            0.00
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="summary-label">
                            Total DA
                        </div>

                        <div
                            class="summary-value"
                            id="summaryDa"
                        >
                            0.00
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card summary-card">
                    <div class="card-body">
                        <div class="summary-label">
                            Grand Total
                        </div>

                        <div
                            class="summary-value"
                            id="summaryGrandTotal"
                        >
                            0.00
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Report Table --}}
        <div class="card">
            <div
                class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
            >
                <div>
                    <h5 class="mb-0">
                        Customer Database and Daily Visit Report
                    </h5>

                    <small
                        class="text-muted"
                        id="reportSubTitle"
                    ></small>
                </div>

                <div class="d-flex gap-2 flex-wrap">

                    <button
                        type="button"
                        class="btn btn-success"
                        id="btnExcel"
                    >
                        <i class="feather-file-text"></i>
                        Excel
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger"
                        id="btnPdf"
                    >
                        <i class="feather-file"></i>
                        PDF
                    </button>

                    <button
                        type="button"
                        class="btn btn-dark"
                        id="btnPrint"
                    >
                        <i class="feather-printer"></i>
                        Print
                    </button>

                </div>
            </div>

            <div class="card-body p-0">
                <div class="report-table-wrapper">
                    <table
                        class="table table-bordered mb-0"
                        id="reportTable"
                    >
                        <thead id="reportTableHead"></thead>

                        <tbody id="reportTableBody">
                            <tr>
                                <td class="empty-report">
                                    Generate report to view data.
                                </td>
                            </tr>
                        </tbody>

                        <tfoot id="reportTableFoot"></tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('modals')

<div
    class="modal fade"
    id="columnModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Select Report Fields
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>
            </div>

            <div class="modal-body">

                <div
                    class="d-flex justify-content-between mb-3"
                >
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        id="btnSelectAllFields"
                    >
                        Select All
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="btnDefaultFields"
                    >
                        PDF Default Fields
                    </button>
                </div>

                <div class="column-box">
                    <div class="row g-2">

                        @foreach(
                            $availableColumns as $key => $label
                        )
                            <div class="col-md-4 col-sm-6">
                                <div class="form-check">
                                    <input
                                        class="form-check-input report-column"
                                        type="checkbox"
                                        name="columns[]"
                                        value="{{ $key }}"
                                        id="column_{{ $key }}"
                                        {{ in_array($key, $defaultColumns, true) ? 'checked' : '' }}
                                    >

                                    <label
                                        class="form-check-label"
                                        for="column_{{ $key }}"
                                    >
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnApplyFields"
                >
                    Apply Fields
                </button>
            </div>

        </div>
    </div>
</div>

@endpush

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script
    src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"
></script>

<script>
    const REPORT_DATA_URL =
        @json(route('activities.report.data'));

    const REPORT_PDF_URL =
        @json(route('activities.report.pdf'));

    const REPORT_PRINT_URL =
        @json(route('activities.report.print'));

    const REPORT_EXCEL_URL =
        @json(route('activities.report.excel'));

    const DEFAULT_COLUMNS =
        @json($defaultColumns);

    $(document).ready(function () {
        initSelect2();
        loadReport();
    });

    function initSelect2() {
        $('#created_by').select2({
            placeholder: 'All Users',
            allowClear: true,
            width: '100%'
        });

        $('#status').select2({
            placeholder: 'All Status',
            allowClear: true,
            minimumResultsForSearch: Infinity,
            width: '100%'
        });

        $('#organization_id').select2({
            placeholder: 'All Organizations',
            allowClear: true,
            width: '100%'
        });
    }

    function selectedColumns() {
        return $('.report-column:checked')
            .map(function () {
                return $(this).val();
            })
            .get();
    }

    function reportParameters() {
        const params = new URLSearchParams();

        const fromDate = $('#from_date').val();
        const toDate = $('#to_date').val();
        const createdBy = $('#created_by').val();
        const status = $('#status').val();
        const organizationId = $('#organization_id').val();
        const branchId = $('#branch_id').val();

        if (fromDate) {
            params.append('from_date', fromDate);
        }

        if (toDate) {
            params.append('to_date', toDate);
        }

        if (createdBy) {
            params.append('created_by', createdBy);
        }

        if (status) {
            params.append('status', status);
        }

        if (organizationId) {
            params.append(
                'organization_id',
                organizationId
            );
        }

        if (branchId) {
            params.append('branch_id', branchId);
        }

        selectedColumns().forEach(function (column) {
            params.append('columns[]', column);
        });

        return params;
    }

    $('#reportFilterForm').on('submit', function (event) {
        event.preventDefault();
        loadReport();
    });

    $('#btnApplyFields').on('click', function () {
        if (selectedColumns().length === 0) {
            Swal.fire(
                'Field Required',
                'Please select at least one report field.',
                'warning'
            );

            return;
        }

        const modalElement =
            document.getElementById('columnModal');

        const modal =
            bootstrap.Modal.getInstance(modalElement);

        modal.hide();

        loadReport();
    });

    $('#btnSelectAllFields').on('click', function () {
        $('.report-column').prop('checked', true);
    });

    $('#btnDefaultFields').on('click', function () {
        $('.report-column').prop('checked', false);

        DEFAULT_COLUMNS.forEach(function (column) {
            $('#column_' + column).prop('checked', true);
        });
    });

    $('#btnReset').on('click', function () {
        $('#from_date').val(
            @json(now()->startOfMonth()->format('Y-m-d'))
        );

        $('#to_date').val(
            @json(now()->format('Y-m-d'))
        );

        $('#created_by')
            .val('')
            .trigger('change');

        $('#status')
            .val('')
            .trigger('change');

        $('#organization_id')
            .val('')
            .trigger('change');

        $('#branch_id').val('');

        $('.report-column').prop('checked', false);

        DEFAULT_COLUMNS.forEach(function (column) {
            $('#column_' + column).prop('checked', true);
        });

        loadReport();
    });

    $('#btnExcel').on('click', function () {
        window.location.href =
            REPORT_EXCEL_URL + '?' +
            reportParameters().toString();
    });

    $('#btnPdf').on('click', function () {
        window.location.href =
            REPORT_PDF_URL + '?' +
            reportParameters().toString();
    });

    $('#btnPrint').on('click', function () {
        window.open(
            REPORT_PRINT_URL + '?' +
            reportParameters().toString(),
            '_blank'
        );
    });

    function loadReport() {
        const $button = $('#btnFilter');

        $button
            .prop('disabled', true)
            .html(
                '<span class="spinner-border spinner-border-sm"></span> Loading...'
            );

        $('#reportTableBody').html(`
            <tr>
                <td class="loading-report">
                    <span
                        class="spinner-border spinner-border-sm"
                    ></span>
                    Loading report...
                </td>
            </tr>
        `);

        $.ajax({
            url: REPORT_DATA_URL,
            type: 'GET',
            data: reportParameters().toString(),

            success: function (response) {
                renderReport(response);
                renderSummary(response.summary);
                renderReportSubtitle();
            },

            error: function (xhr) {
                let message = 'Unable to load report.';

                if (
                    xhr.status === 422 &&
                    xhr.responseJSON?.errors
                ) {
                    message = Object
                        .values(xhr.responseJSON.errors)[0][0];
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }

                $('#reportTableHead').html('');
                $('#reportTableFoot').html('');

                $('#reportTableBody').html(`
                    <tr>
                        <td class="empty-report">
                            ${escapeHtml(message)}
                        </td>
                    </tr>
                `);

                Swal.fire(
                    'Error',
                    message,
                    'error'
                );
            },

            complete: function () {
                $button
                    .prop('disabled', false)
                    .html(
                        '<i class="feather-search"></i> Generate Report'
                    );
            }
        });
    }

    function renderReport(response) {
        const columns = response.columns || [];
        const rows = response.rows || [];

        let headerHtml = '<tr>';

        columns.forEach(function (column) {
            headerHtml += `
                <th>${escapeHtml(column.label)}</th>
            `;
        });

        headerHtml += '</tr>';

        $('#reportTableHead').html(headerHtml);

        if (rows.length === 0) {
            $('#reportTableBody').html(`
                <tr>
                    <td
                        colspan="${Math.max(columns.length, 1)}"
                        class="empty-report"
                    >
                        No activities found for selected filters.
                    </td>
                </tr>
            `);
        } else {
            let bodyHtml = '';

            rows.forEach(function (row) {
                bodyHtml += '<tr>';

                columns.forEach(function (column) {
                    bodyHtml += `
                        <td>
                            ${escapeHtml(
                                row[column.key] ?? ''
                            )}
                        </td>
                    `;
                });

                bodyHtml += '</tr>';
            });

            $('#reportTableBody').html(bodyHtml);
        }

        renderFooter(
            columns,
            response.summary
        );
    }

    function renderFooter(columns, summary) {
        if (!columns.length) {
            $('#reportTableFoot').html('');
            return;
        }

        let footerHtml = '<tr class="fw-bold">';

        columns.forEach(function (column, index) {
            let value = '';

            if (index === 0) {
                value = 'Total Amount';
            }

            if (column.key === 'ta') {
                value = summary.total_ta;
            }

            if (column.key === 'da') {
                value = summary.total_da;
            }

            if (column.key === 'total') {
                value = summary.grand_total;
            }

            footerHtml += `
                <td>${escapeHtml(value)}</td>
            `;
        });

        footerHtml += '</tr>';

        $('#reportTableFoot').html(footerHtml);
    }

    function renderSummary(summary) {
        $('#summaryActivityCount').text(
            summary.activity_count ?? 0
        );

        $('#summaryTa').text(
            summary.total_ta ?? '0.00'
        );

        $('#summaryDa').text(
            summary.total_da ?? '0.00'
        );

        $('#summaryGrandTotal').text(
            summary.grand_total ?? '0.00'
        );
    }

    function renderReportSubtitle() {
        const fromDate = $('#from_date').val();
        const toDate = $('#to_date').val();

        const employee =
            $('#created_by option:selected').text().trim();

        const dateText =
            (fromDate || 'Beginning') +
            ' To ' +
            (toDate || 'Today');

        $('#reportSubTitle').text(
            employee + ' | ' + dateText
        );
    }

    function escapeHtml(value) {
        return $('<div>')
            .text(value ?? '')
            .html();
    }
</script>

@endpush