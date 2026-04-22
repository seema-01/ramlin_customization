<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Category Wise Sales Report</h4>
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

                                    <div class="form-group col-md-4">
                                        <label>From & To Date</label>
                                        <div class="input-group col-md-12">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="far fa-clock"></i></span>
                                            </div>

                                            <input type="text" class="form-control float-right" id="datepicker">
                                            <input type="hidden" id="start_date" class="form-control">
                                            <input type="hidden" id="end_date" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4 d-flex align-items-center pt-4">
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="category_date_filter()">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- top trendong categories -->
                        <div class="row mb-4" id="top_trending_box">
                            <div class="col-md-12">
                                <h5 class="mb-3"><b>🔥 Top 3 Trending Categories</b></h5>
                                <div class="row" id="trending_categories"></div>
                            </div>
                        </div>
                        <!-- end -->
                        <!-- CATEGORY REPORT TABLE -->
                        <table class='table-striped category_sales_table'
                            data-toggle="table"
                            data-url="<?= base_url('admin/Sales_inventory/get_category_sales_list') ?>"
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
                            data-query-params="category_sales_report_query_params">

                            <thead>
                                <tr>
                                    <th data-field="category_name" data-sortable='true'>Category</th>
                                    <th data-field="total_qty" data-sortable='true'>Total Quantity</th>
                                    <th data-field="total_sales" data-sortable='true'>Total Sales (<?= $currency ?>)</th>
                                </tr>
                            </thead>

                        </table>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>