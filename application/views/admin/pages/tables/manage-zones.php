<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Manage Delivery Zones</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="home_breadcrumb"
                                href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Zones</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-12 main-content">
                        <div class="card content-area p-4">
                            <div class="card-head d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="card-title mb-0">Zone Details</h4>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <a class="btn btn-primary btn-sm"
                                        href="<?= base_url('admin/area/manage-city-outlines') ?>">
                                        <i class="fa fa-plus"></i> Create New Zone
                                    </a>
                                </div>
                            </div>

                            <!-- <hr> -->
                            <div class="card-innr">
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <div>
                                            <label>Filter By Status</label>
                                            <select id="status" name="status" placeholder="Select Offer Type"
                                                required="" class="form-control">
                                                <option value="">Select Status</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <div>
                                            <label>Filter By City</label>
                                            <select id="city_id_filter" name="city_id_filter" class="form-control">
                                                <option value="">Select City</option>
                                                <?php if (!empty($cities)) {
                                                    foreach ($cities as $city) { ?>
                                                        <option value="<?= $city['id'] ?>">

                                                            <?= $city['name'] ?>
                                                        </option>
                                                    <?php }
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="gaps-1-5x"></div>
                                <table class='table-striped' id="zone_list" data-toggle="table"
                                    data-url="<?= base_url('admin/area/zone_list') ?>" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                    data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                    data-toolbar="" data-show-export="true" data-maintain-selected="true"
                                    data-export-types='["txt","excel"]' data-query-params="slider_query_params">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable="true">ID</th>
                                            <th data-field="city_name" data-sortable="true">City</th>
                                            <th data-field="zone_name" data-sortable="true">Zone Name</th>
                                            <!-- <th data-field="geolocation_type" data-sortable="true">Type</th> -->
                                            <th data-field="status" data-sortable="false">Status</th>
                                            <th data-field="date_created" data-sortable="true">Created Date</th>
                                            <th data-field="operate" data-sortable="false" data-events="actionEvents">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>