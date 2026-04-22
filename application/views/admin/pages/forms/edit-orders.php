<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>View Order</h4>
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
        <?php
        // echo "<pre>";
        // print_r($order_detls);
        // die;
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!-- The time line -->
                    <section class="time-line-box text-center">
                        <div class="swiper-wrapper col-12">
                            <?php
                            $status = json_decode($order_detls[0]['status']);
                            $status_wise_class = [
                                'awaiting' => ['fa fa-clock-o'],
                                'pending' => ['fa fa-xs fa-history', 'bg-secondary'],
                                'confirmed' => ['fa fa-xs fa-level-down-alt', 'bg-indigo'],
                                'preparing' => ['fa fa-xs fa-people-carry ', 'bg-navy'],
                                'ready_for_pickup' => ['fa fa-xs fa-shipping-fast ', 'bg-yellow'],
                                'out_for_delivery' => ['fa fa-xs fa-shipping-fast ', 'bg-yellow'],
                                'delivered' => ['fa fa-xs fa-user-check ', 'bg-success'],
                                'cancelled' => ['fa fa-xs fa-times-circle ', 'bg-red'],
                            ];
                            foreach ($status as $row) {
                            ?>
                                <div class="swiper-slide">
                                    <div class="max-auto col-md-6 offset-md-3">
                                        <div class="<?= $status_wise_class[$row[0]][1] ?> pt-2 pb-2 rounded"> <span
                                                class="fa-lg"><i class="<?= $status_wise_class[$row[0]][0] ?>"></i></span>
                                        </div>
                                    </div>
                                    <div class="timestamp m-1"><small class="date"><i
                                                class="fas fa-clock"></i>&nbsp;<?= strtoupper($row[1]) ?> </small> </div>
                                    <div class="status text-bold"><span> <?= strtoupper($row[0]) ?> </span></div>
                                </div>
                            <?php } ?>

                        </div>
                    </section>
                </div>
                <div class="col-md-12">
                    <div class="card card-info">
                        <div class="card-body">
                            <div class="card card-widget widget-user-2">
                                <div class="widget-user-header bg-navy">
                                    <h5 class="text-left"> Order Items</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <p class="h5">Branch Name: <span
                                            class="text text-info"><?= output_escaping($branch_name[0]['branch_name']); ?>
                                        </span></p>
                                    <p class="h6">Branch Address: <span
                                            class="text text-info"><?= $branch_name[0]['address']; ?> </span></p>
                                    <?php
                                    if (isset($order_detls[0]['notes']) && !empty($order_detls[0]['notes'])) {
                                    ?>
                                        <p class="h6">Order Note: <span
                                                class="text text-info"><?= $order_detls[0]['notes']; ?> </span></p>

                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <hr>
                            <!-- seema : uncomment this button for modify order functionality, check the proper flow for this functionality   -->

                            <!-- <div class="row mb-3">
                                <div class="col text-right">
                                    <button class="btn btn-info" data-toggle="modal" data-target="#addProductModal">
                                        <i class="fa fa-plus"></i> Add Product to Order
                                    </button>
                                </div>
                            </div> -->
                            <!-- Add Product Modal -->
                            <div class="modal fade" id="addProductModal" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">

                                        <div class="modal-header bg-info">
                                            <h5 class="modal-title">Add Product To Order</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>

                                        <div class="modal-body">
                                            <form id="add_product_form">
                                                <input type="hidden" name="order_id" value="<?= $order_detls[0]['order_id']; ?>">
                                                <input type="hidden" name="price" id="hidden_price">
                                                <input type="hidden" name="tax" id="hidden_tax">
                                                <input type="hidden" name="total" id="hidden_total">
                                                <div class="form-group row slider-products">
                                                    <label for="product_id" class="control-label">Products <span class='text-danger text-sm'>*</span></label>
                                                    <div class="col-md-12">
                                                        <select name="product_id" id="product_id" class="search_product w-100" data-placeholder=" Type to search and select products">
                                                            <?php
                                                            if (isset($fetched_data[0]['id']) && $fetched_data[0]['type']  == 'products') {
                                                                $product_details = fetch_details(['id' => $fetched_data[0]['type_id']], 'products', 'id,name');
                                                                if (!empty($product_details)) {
                                                            ?>
                                                                    <option value="<?= $product_details[0]['id'] ?>" selected> <?= $product_details[0]['name'] ?></option>
                                                            <?php
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label>Variant</label>
                                                    <select class="form-control" id="variant_id" name="variant_id" required>
                                                        <option value="">Select Product First</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label>Quantity</label>
                                                    <input type="number" name="qty" id="qty" min="1" value="1" class="form-control" required>
                                                </div>

                                                <!-- addition -->
                                                <div class="form-group mt-3">
                                                    <label class="font-weight-bold">Payment Options</label><br>

                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="wallet_payment" name="payment_method" value="wallet" class="custom-control-input">
                                                        <label class="custom-control-label" for="wallet_payment">
                                                            Deduct From Wallet
                                                            <span class="badge badge-success ml-2 wallet-badge">
                                                                Wallet Balance: <?= $currency . number_format(floatval($order_detls[0]['user_balance']), 2); ?>
                                                            </span>
                                                        </label>
                                                    </div>

                                                    <div class="custom-control custom-radio mt-2">
                                                        <input type="radio" id="cod_payment" name="payment_method" value="cod" class="custom-control-input" checked>
                                                        <label class="custom-control-label" for="cod_payment">COD (Cash on Delivery)</label>
                                                    </div>
                                                </div>

                                                <!-- PRICE SUMMARY -->
                                                <div class="card mt-3 shadow-sm">
                                                    <div class="card-body p-2">
                                                        <h6 class="mb-2 font-weight-bold">Price Summary</h6>
                                                        <div class="d-flex justify-content-between">
                                                            <span>Product Price:</span>
                                                            <span id="product_price"><?= $currency; ?>0</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span>Tax:</span>
                                                            <span id="product_tax"><?= $currency; ?>0</span>
                                                        </div>

                                                        <hr class="my-2">

                                                        <div class="d-flex justify-content-between font-weight-bold">
                                                            <span>Total:</span>
                                                            <span id="total_amount"><?= $currency; ?>0</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="modal-footer">
                                            <button class="btn btn-info" id="save_product_btn">Add to Order</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- end modal -->
                            <?php $total = 0;
                            $tax_amount = 0;
                            foreach ($items as $item) {
                                $item['discounted_price'] = ($item['discounted_price'] == '') ? 0 : $item['discounted_price'];
                                $total += $subtotal = ($item['quantity'] != 0 && ($item['discounted_price'] != '' && $item['discounted_price'] > 0) && $item['price'] > $item['discounted_price']) ? ($item['price'] - $item['discounted_price']) : ($item['price'] * $item['quantity']);
                                $tax_amount += (int) ($item['tax_amount']);
                                $total += $subtotal = $tax_amount;
                            ?>
                                <div class="row">
                                    <div class="col">
                                        <div class="card card-2">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="sq align-self-center">
                                                        <a href='<?= base_url() . $item['product_image'] ?>'
                                                            data-toggle='lightbox' data-gallery='order-images'
                                                            class="order-product-image mx-2">
                                                            <img src='<?= base_url() . $item['product_image'] ?>'
                                                                class="img-fluid" />
                                                        </a>
                                                    </div>
                                                    <div class="media-body my-auto text-right">
                                                        <div class="row my-auto flex-column flex-md-row">
                                                            <div class="col my-auto">
                                                                <h6 class="mb-0 text-left">
                                                                    <?= (strlen($item['pname']) > 25) ? substr($item['pname'], 0, 25) . "..." : $item['pname'] ?>
                                                                </h6>
                                                                <?php if (isset($item['product_variants']) && !empty($item['product_variants'])) { ?>
                                                                    <h6 class="mb-0 text-left">
                                                                        <small><?= str_replace(',', ' | ', $item['product_variants'][0]['variant_values']) ?></small>
                                                                    </h6>
                                                                <?php } ?>

                                                                <!-- Display Add-Ons -->
                                                                <?php if (!empty($item['add_ons'])) {
                                                                    $add_ons = json_decode($item['add_ons'], true);
                                                                    if (!empty($add_ons)) { ?>
                                                                        <p class="mb-0 text-left">Add ons:</p>
                                                                        <small class="mb-0 text-left addons_datas">
                                                                            <?php foreach ($add_ons as $add_on) { ?>
                                                                                <?= $add_on['title'] ?> x <?= $add_on['qty'] ?> price:
                                                                                <?= $settings['currency'] . number_format($add_on['price'], 2) ?><br>
                                                                            <?php } ?>
                                                                        </small>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                            </div>
                                                            <div class="col-auto my-auto">
                                                                <div class="price mb-2 list-view-price">
                                                                    Price:
                                                                    <?= $settings['currency'] . number_format((int) $item['price'] + (int) $item['tax_amount']) ?>
                                                                    <?php if (isset($item['discounted_price']) && !empty($item['discounted_price'])) { ?>
                                                                        <span
                                                                            class="striped-price"><?= $settings['currency'] . number_format($item['discounted_price']) ?></span>
                                                                    <?php } ?>
                                                                    <a href=" <?= BASE_URL('admin/product/view-product?edit_id=' . $item['product_id'] . '') ?>"
                                                                        title="View Product" class="btn btn-info btn-xs">
                                                                        <i class="fa fa-eye"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <div class="col my-auto"> Variant ID :
                                                                <?= $item['product_variant_id'] ?> </div>
                                                            <div class="col my-auto"> Qty : <?= $item['quantity'] ?></div>
                                                            <div class="col my-auto"> Type:
                                                                <?= ucwords(str_replace('_', ' ', $item['product_type'])); ?>
                                                            </div>
                                                            <div class="col my-auto">
                                                                <h6 class="mb-0">
                                                                    <?= $settings['currency'] . number_format($item['price'] * $item['quantity']) ?>
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-info">
                                        <div class="card-header bg-navy border-0 h5">Customer Details</div>
                                        <div class="card-body">
                                            <div class="card card-widget widget-user-2">
                                                <div class="widget-user-header bg-info">
                                                    <input type="hidden" name="hidden" id="order_id"
                                                        value="<?php echo $order_detls[0]['id']; ?>">

                                                    <div class="widget-user-image">
                                                        <img class="img-circle elevation-2"
                                                            src="<?= base_url(AVTAR_IMAGE) ?>" alt="User Avatar">
                                                    </div>
                                                    <h5 class="widget-user-desc"><?= $order_detls[0]['uname']; ?></h5>
                                                    <h6 class="widget-user-desc"><?= $order_detls[0]['address']; ?></h6>
                                                </div>
                                                <div class="card-footer p-0">
                                                    <ul class="nav flex-column">
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)"
                                                                class="nav-link text-info">Contact <span
                                                                    class="float-right text-info"><?= (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) ? str_repeat("X", strlen($order_detls[0]['mobile']) - 3) . substr($order_detls[0]['mobile'], -3) : $order_detls[0]['mobile']; ?></span></a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)"
                                                                class="nav-link text-info">Email
                                                                <?php if (isset($order_detls[0]['email']) && !empty($order_detls[0]['email'])) { ?>
                                                                    <span
                                                                        class="float-right text-info"><?= (!defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) ? str_repeat("X", strlen($order_detls[0]['email']) - 3) . substr($order_detls[0]['email'], -3) : $order_detls[0]['email']; ?>
                                                                    </span>
                                                                <?php } ?>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)" class="nav-link text-info">User
                                                                Wallet Balance(<?= $settings['currency'] ?>)<span
                                                                    class="float-right text-info"><?= number_format(floatval($order_detls[0]['user_balance']), 2); ?></span>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)"
                                                                class="nav-link text-info">Order Date<span
                                                                    class="float-right text-info">
                                                                    <?= date('d-M-Y, g:i A - D', strtotime($order_detls[0]['date_added'])); ?></span></a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)" class="nav-link text-info"> <b>
                                                                    OTP </b><span class="float-right text-info">
                                                                    <?= $order_detls[0]['item_otp']; ?></span></a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <?php
                                                            $city = fetch_details(['id' => $order_detls[0]['city_id']], "cities", "name");
                                                            ?>
                                                            <a href="javascript:void(0)" class="nav-link text-info">
                                                                Order City <span class="float-right text-info">
                                                                    <?= $city[0]['name']; ?></span></a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-info">
                                        <div class="card-body">
                                            <div class="card card-widget widget-user-2">
                                                <div class="widget-user-header bg-navy">
                                                    <h5 class="text-center">Payment Details</h5>
                                                </div>
                                                <div class="card-footer p-0">
                                                    <ul class="nav flex-column">
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)"
                                                                class="nav-link text-info">Payment Method<span
                                                                    class="float-right text-info"><?= $order_detls[0]['payment_method']; ?></span></a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)"
                                                                class="nav-link text-info">Total(<?= $settings['currency'] ?>)<span
                                                                    class="float-right text-info" id='amount'>
                                                                    <?= '+ ' . number_format($order_detls[0]['order_total']);
                                                                    $total = $order_detls[0]['order_total']; ?></span>
                                                            </a>
                                                        </li>
                                                        <!-- tax amount -->
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)" class="nav-link text-info">Tax
                                                                Amount<span
                                                                    class="float-right text-info"><?= '+ ' . number_format(floatval($order_detls[0]['tax_amount'])); ?></span></a>
                                                        </li>
                                                        <!-- end -->
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)"
                                                                class="nav-link text-info">Delivery
                                                                Charge(<?= $settings['currency'] ?>)<span
                                                                    class="float-right text-info"><?php echo '+ ' . $order_detls[0]['delivery_charge'];
                                                                                                    $total = $total + $order_detls[0]['delivery_charge']; ?></span></a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)"
                                                                class="nav-link text-info">Delivery
                                                                Tip(<?= $settings['currency'] ?>)<span
                                                                    class="float-right text-info"><?php echo '+ ' . $order_detls[0]['delivery_tip'];
                                                                                                    $total = $total + $order_detls[0]['delivery_tip']; ?></span></a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)"
                                                                class="nav-link text-info">Wallet
                                                                Balance(<?= $settings['currency'] ?>) <span
                                                                    class="float-right text-info"><?php echo '- ' . $order_detls[0]['wallet_balance'];
                                                                                                    $total = $total - $order_detls[0]['wallet_balance']; ?></span></a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)"
                                                                class="nav-link text-info">Promo Code Discount
                                                                (<?= $settings['currency'] ?>)<span
                                                                    class="float-right text-info">
                                                                    <?php echo '- ' . $order_detls[0]['promo_discount'];
                                                                    $total = floatval($total - $order_detls[0]['promo_discount']); ?></span>
                                                            </a>
                                                        </li>
                                                        <input type="hidden" name="total_amount" id="total_amount"
                                                            value="<?php echo $order_detls[0]['order_total'] + $order_detls[0]['delivery_charge'] ?>">
                                                        <input type="hidden" name="final_amount" id="final_amount"
                                                            value="<?php echo $order_detls[0]['final_total']; ?>">
                                                        <input type="hidden" id="final_total" name="final_total"
                                                            value="<?= $total; ?>">
                                                        <li class="nav-item">
                                                            <a href="javascript:void(0)" class="nav-link bg-info">
                                                                <?php
                                                                $total_final = (float) $total + (float) $order_detls[0]['tax_amount'];

                                                                ?>
                                                                Payable Total(<?= $settings['currency'] ?>) <span
                                                                    class="float-right"><?= number_format($total_final); ?></span>
                                                            </a>
                                                        </li>

                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.widget-user -->
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tbody>

                                            <?php 
                                            if($order_detls[0]['active_status'] !== 'draft'){
                                            if (isset($order_detls[0]['is_self_pick_up']) && empty($order_detls[0]['is_self_pick_up']) && $order_detls[0]['is_self_pick_up'] == 0) { ?>
                                                <tr><small class="text text-primary">Rider will list according to Rider
                                                        Serviceable City. Serviceable City you can set in Rider's page.
                                                    </small></tr>
                                                <tr>
                                                    <th class="col-2">Deliver By <span class='text-danger text-sm'>*</span>
                                                    </th>
                                                    <td>
                                                        <?php if (isset($permissions_message)) { ?>
                                                            <span class='text-danger text-sm'>(<?= $permissions_message ?>)</span><br>
                                                        <?php } ?>
                                                        <select id="deliver_by" name="deliver_by" class="form-control "
                                                            required>
                                                            <option value="">Select Rider</option>
                                                            <?php foreach ($delivery_res as $row) { ?>
                                                                <option value="<?= $row['id'] ?>"
                                                                    <?= (!empty($order_detls[0]['rider_id']) && $order_detls[0]['rider_id'] == $row['id']) ? 'selected' : '' ?>>
                                                                    <?= $row['username'] . " - " . $row['rider_orders'] . ' Pending Orders' ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <th class="col-3">Status <span class='text-danger text-sm'>*</span></th>
                                                <td>
                                                    <select name="status" id="status" class="form-control"
                                                        data-isjson="true"
                                                        data-orderid="<?= $order_detls[0]['order_id']; ?>">
                                                        <option value="pending"
                                                            <?= (isset($order_detls[0]['active_status']) && $order_detls[0]['active_status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                                        <option value="confirmed"
                                                            <?= (isset($order_detls[0]['active_status']) && $order_detls[0]['active_status'] == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                                        <option value="preparing"
                                                            <?= (isset($order_detls[0]['active_status']) && $order_detls[0]['active_status'] == 'preparing') ? 'selected' : '' ?>>Preparing</option>
                                                        <option value="ready_for_pickup"
                                                            <?= (isset($order_detls[0]['active_status']) && $order_detls[0]['active_status'] == 'ready_for_pickup') ? 'selected' : '' ?>>Ready For Pickup</option>
                                                        <option value="out_for_delivery"
                                                            <?= (isset($order_detls[0]['active_status']) && $order_detls[0]['active_status'] == 'out_for_delivery') ? 'selected' : '' ?>>Out For Delivery</option>
                                                        <option value="delivered"
                                                            <?= (isset($order_detls[0]['active_status']) && $order_detls[0]['active_status'] == 'delivered') ? 'selected' : '' ?>>Delivered</option>
                                                        <option value="cancelled"
                                                            <?= (isset($order_detls[0]['active_status']) && $order_detls[0]['active_status'] == 'cancelled') ? 'selected' : '' ?>>Cancel</option>
                                                    </select>
                                                </td>
                                            </tr>
                                           
                                            <?php if (isset($order_detls[0]['is_self_pick_up']) && !empty($order_detls[0]['is_self_pick_up']) && $order_detls[0]['is_self_pick_up'] == 1) { ?>
                                                <tr class="">
                                                    <th class="col-3">Owner Note for Self Pickup <span
                                                            class='text-danger text-sm'>*</span></th>
                                                    <td>
                                                        <input type="text" class="form-control" id="owner_note"
                                                            name="owner_note"
                                                            value="<?= (isset($order_detls[0]['owner_note']) && !empty($order_detls[0]['owner_note'])) ? $order_detls[0]['owner_note'] : "" ?>"
                                                            placeholder="Owner Note for Self Pickup">
                                                    </td>
                                                </tr>
                                                <tr class="">
                                                    <th class="col-3">Self Pickup Time <span
                                                            class='text-danger text-sm'>*</span></th>
                                                    <td>
                                                        <input type="datetime-local" class="form-control"
                                                            id="self_pickup_time" name="self_pickup_time"
                                                            value="<?= (isset($order_detls[0]['self_pickup_time']) && !empty($order_detls[0]['self_pickup_time'])) ? date("Y-m-d\TH:i:s", strtotime($order_detls[0]['self_pickup_time'])) : "" ?>"
                                                            placeholder=" Self Pickup time">
                                                        <input type="hidden" class="form-control" id="is_self_pick_up"
                                                            name="is_self_pick_up"
                                                            value="<?= (isset($order_detls[0]['is_self_pick_up']) && !empty($order_detls[0]['is_self_pick_up'])) ? $order_detls[0]['is_self_pick_up'] : "" ?>">
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            <tr
                                                class="reason-to-cancel <?= (isset($order_detls[0]['active_status']) && $order_detls[0]['active_status'] == 'cancelled') ? '' : "d-none" ?>">
                                                <th class="col-3">Reason to Cancel <span
                                                        class='text-danger text-sm'>*</span></th>
                                                <td>
                                                    <input type="text" class="form-control" id="reason" name="reason"
                                                        value="<?= (isset($order_detls[0]['reason']) && !empty($order_detls[0]['reason'])) ? $order_detls[0]['reason'] : "" ?>"
                                                        placeholder="Reason">
                                                    <?php
                                                    $username = "";
                                                    $cancel_by_user_id = (isset($order_detls[0]['cancel_by']) && !empty($order_detls[0]['cancel_by'])) ? $order_detls[0]['cancel_by'] : "0";
                                                    if (isset($cancel_by_user_id) && !empty($cancel_by_user_id)) {
                                                        $username = fetch_details(['id' => $cancel_by_user_id], "users", "username");
                                                    }
                                                    if ($username != "") {
                                                    ?>
                                                        <label for="cancel_by">Cancel By
                                                            <?= $username[0]['username'] . "( " . $this->ion_auth->get_users_groups($cancel_by_user_id)->row()->description . " )" ?></label>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="form-group">
                                                        <button type="submit" class="btn btn-info update_order"
                                                            id="submit_btn">Update Order</button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!--/.card-->
                </div>

                <!--/.col-md-12-->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>