<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Cancel Order Report</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a class="text text-info" href="<?= base_url('admin/home') ?>">
                                <?= display_breadcrumbs(); ?>
                            </a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 main-content">
                    <div class="card content-area p-4">

                        <div class="card-innr">
                            <div class="gaps-1-5x row d-flex adjust-items-center">
                                <div class="row col-md-12">

                                    <!-- DATE FILTER -->
                                    <div class="form-group col-md-4">
                                        <label>From & To Date</label>
                                        <div class="input-group col-md-12">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="far fa-clock"></i>
                                                </span>
                                            </div>

                                            <input type="text" class="form-control float-right" id="datepicker">
                                            <input type="hidden" id="start_date" class="form-control">
                                            <input type="hidden" id="end_date" class="form-control">
                                        </div>
                                    </div>

                                    <!-- CANCELLED BY FILTER -->
                                    <!-- <div class="form-group col-md-4">
                                        <label>Cancelled By</label>
                                        <select id="cancel_by" class="form-control">
                                            <option value="">All</option>
                                            <option value="1">Admin</option>
                                            <option value="2">Customer</option>
                                            <option value="3">Rider</option>
                                        </select>
                                    </div> -->

                                    <div class="form-group col-md-4 d-flex align-items-center pt-4">
                                        <button type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            onclick="status_date_wise_search_sales()">
                                            Filter
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- CANCEL ORDER REPORT TABLE -->
                        <table class="table-striped cancel_order_report_table"
                            data-toggle="table"
                            data-url="<?= base_url('admin/Sales_inventory/cancel_order_report') ?>"
                            data-click-to-select="true"
                            data-side-pagination="server"
                            data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true"
                            data-show-columns="true"
                            data-show-refresh="true"
                            data-trim-on-search="false"
                            data-sort-name="id"
                            data-sort-order="desc"
                            data-mobile-responsive="true"
                            data-show-export="true"
                            data-export-types='["txt","excel"]'
                            data-query-params="sales_inventory_report_query_params">

                            <thead>
                                <tr>
                                    <th data-field="order_id" data-sortable="true">Order ID</th>
                                    <th data-field="total" data-sortable="true">
                                        Order Amount (<?= $currency ?>)
                                    </th>
                                    <th data-field="cancel_by" data-sortable="true">Cancelled By</th>
                                    <th data-field="cancel_reason" data-sortable="false">Cancel Reason</th>
                                    <th data-field="created_at" data-sortable="true">Cancelled Date</th>
                                </tr>
                            </thead>

                        </table>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
