<script
    src="https://maps.googleapis.com/maps/api/js?key=<?= $google_map_api_key ?>&libraries=drawing&v=weekly"></script>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Delivery Zones</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="text text-info"
                                href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a class="text text-info"
                                href="<?= base_url('admin/area/manage-zones') ?>">Zones</a></li>
                        <li class="breadcrumb-item active">Delivery Zones</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info">
                        <div class="card-body">
                            <h4>Create Delivery Zone for City </h4>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group border border-secondary rounded">
                                        <ol type="I">
                                            <li>Select your city and create delivery zones within it.</li>
                                            <li>Please edit Map or Zone Deliverable Area in desktop. It may not work in
                                                mobile device.</li>
                                            <li>Here you can create multiple zones within a city to deliver the orders.
                                            </li>
                                            <li>Recommended you to use polygon to accurately define the area for
                                                deliverable zone</li>
                                            <li><strong>Note: </strong>Zones within the same city should not overlap
                                                with each other.</li>
                                            <li>Each zone must have a unique name within the city (e.g., "North Zone",
                                                "Downtown", "Airport Area").</li>
                                        </ol>
                                    </div>
                                    <input type="hidden" name="city_outlines" id="city_outlines" value="">
                                    <div class="form-group ">
                                        <label for="city" class="control-label col-md-12">Select City <span
                                                class='text-danger text-xs'>*</span></label>
                                        <div class="col-md-6">

                                            <select class="target form-control" name="city" id="city_id">
                                                <option value="geolocation_type">---Select City---</option>
                                                <?php foreach ($fetched_data as $row) { ?>
                                                    <option value="<?= $row['latitude'] . ',' . $row['longitude'] ?>"
                                                        data-city_id="<?= $row['id'] ?>"
                                                        data-geolocation_type="<?= $row['geolocation_type'] ?>"
                                                        data-boundary_points='<?= htmlspecialchars($row['boundary_points'], ENT_QUOTES, 'UTF-8') ?>'
                                                        data-radius="<?= $row['radius'] ?>"><?= $row['name'] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="zone_name" class="control-label col-md-12">Zone Name <span
                                                class='text-danger text-xs'>*</span></label>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="zone_name" id="zone_name"
                                                placeholder="Enter zone name (e.g., North Zone, Downtown)" required>
                                            <small class="form-text text-muted">Give a unique name to identify this
                                                delivery zone</small>
                                        </div>
                                    </div>
                                    <div class="form-group mt-5 d-none">
                                        <label for="latitudesandlongitudes" class="control-label col-md-12">Boundry
                                            Points<span class='text-danger text-xs'>*</span> </label>
                                        <textarea class="form-control"
                                            placeholder="here will be your selected outlines latitude and longitude"
                                            name="vertices" id="vertices" cols="30" rows="10"></textarea>
                                    </div>

                                    <div class="map-canvas" id="map-canvas"></div>
                                    <button type="button" class="btn btn-info mt-3" id="save_city">Create Zone</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/.card-->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>