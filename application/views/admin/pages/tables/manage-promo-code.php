<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4> Manage Promo Code</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="text text-info"
                                href="<?= base_url('admin/home') ?>"><?= display_breadcrumbs(); ?></a></li>
                        <!-- <li class="breadcrumb-item active">Orders</li> -->
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="modal fade edit-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Manage Promo Code</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body p-0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 main-content">
                    <div class="card content-area p-4">
                        <div class="card-header border-0">
                            <div class="card-tools">
                                <a href="<?= base_url() . 'admin/promo-code/' ?>" class="btn btn-block btn-outline-info btn-sm">Add Promo Code</a>
                            </div>
                        </div>
                        <div class="card-innr">
                            <div class="gaps-1-5x"></div>
                            <div class="row">
                                <!-- <div class="form-group col-md-4">
                                    <label>Date and time range:</label>
                                    <div class="input-group col-md-12">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="datepicker">
                                        <input type="hidden" id="start_date" class="form-control float-right">
                                        <input type="hidden" id="end_date" class="form-control float-right">
                                    </div>
                                </div> -->
                                <div class="form-group col-md-3">
                                    <label>Filter By Status</label>
                                    <select id="status" name="status" placeholder="Select Promocode status" required="" class="form-control">
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Filter By Discount Type</label>
                                    <select id="discount_type" name="discount_type" placeholder="Select Partner Discount Type" required="" class="form-control">
                                        <option value="">Select Discount Type</option>
                                        <option value="percentage">Percentage</option>
                                        <option value="amount">Amount</option>
                                    </select>
                                </div>

                                <!-- <div class="form-group col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="status_date_wise_search()">Filter</button>
                                </div> -->
                            </div>
                            <table class='table-striped promo_code_table' id='promo_code_table' data-toggle="table" data-url="<?= base_url('admin/promo_code/view_promo_code') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{
                            "fileName": "promocode-list",
                            "ignoreColumn": ["state"] 
                            }' data-query-params="slider_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">ID</th>
                                        <th data-field="promo_code" data-sortable="false">Promo Code</th>
                                        <th data-field="branch" data-sortable="false">Branch</th>
                                        <th data-field="image" data-sortable="false">Image</th>
                                        <th data-field="message" data-sortable="true">Message</th>
                                        <th data-field="start_date" data-sortable="true">Start Date</th>
                                        <th data-field="end_date" data-sortable="true">End Date</th>
                                        <th data-field="no_of_users" data-sortable="true" data-visible='false'>No .of users</th>
                                        <th data-field="minimum_order_amount" data-sortable="true" data-visible='false'>Minimum order amount</th>
                                        <th data-field="discount" data-sortable="true">Discount</th>
                                        <th data-field="discount_type" data-sortable="true">Discount type</th>
                                        <th data-field="max_discount_amount" data-sortable="true" data-visible='false'>Max discount amount</th>
                                        <th data-field="repeat_usage" data-sortable="true" data-visible='false'>Repeat usage</th>
                                        <th data-field="no_of_repeat_usage" data-sortable="true" data-visible='false'>No of repeat usage</th>
                                        <th data-field="status" data-sortable="false">Status</th>
                                        <th data-field="operate" data-sortable="false">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div><!-- .card-innr -->
                    </div><!-- .card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>