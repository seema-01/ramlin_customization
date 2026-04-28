<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Area extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper', 'file']);
        $this->load->model('Area_model');
        $this->load->model('Zone_model');

        if (!has_permissions('read', 'city')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        } else {
            $this->session->set_flashdata('authorize_flag', "");
        }
    }



    public function manage_cities()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (!has_permissions('read', 'city')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }

            $this->data['main_page'] = TABLES . 'manage-city';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'City Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' City Management  | ' . $settings['app_name'];
            $this->data['google_map_api_key'] = $settings['google_map_javascript_api_key'];
            if (isset($_GET['edit_id'])) {
                $this->data['fetched_data'] = fetch_details(['id' => $_GET['edit_id']], 'cities');
            }


            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function manage_city_outlines()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (!has_permissions('read', 'city')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }

            $this->data['main_page'] = TABLES . 'manage-city-outlines';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Deliverable Area Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Deliverable Area Management  | ' . $settings['app_name'];
            $this->data['fetched_data'] = fetch_details("", 'cities');
            $this->data['google_map_api_key'] = $settings['google_map_javascript_api_key'];
            if (!isset($_SESSION['branch_id'])) {

                redirect('admin/branch', 'refresh');
            } else {

                $this->load->view('admin/template', $this->data);
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function view_city()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->Area_model->get_list($table = 'cities');
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function delete_city()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (trim($_GET['table']) == 'cities') {
                if (print_msg(!has_permissions('delete', 'city'), PERMISSION_ERROR_MSG, 'city')) {
                    return false;
                }
            }
            if (delete_details(['id' => $_GET['id']], $_GET['table'])) {
                $response['error'] = false;
                $response['message'] = 'Deleted Successfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'Something went wrong';
            }
            echo json_encode($response);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    // public function add_city()
    // {
    //     if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
    //         if (isset($_POST['edit_city'])) {
    //             if (print_msg(!has_permissions('update', 'city'), PERMISSION_ERROR_MSG, 'city')) {
    //                 return false;
    //             }
    //         } else {
    //             if (print_msg(!has_permissions('create', 'city'), PERMISSION_ERROR_MSG, 'city')) {
    //                 return false;
    //             }
    //         }
    //         if (!isset($_POST['boundary_points']) && empty($_POST['boundary_points'])) {
    //             $this->form_validation->set_rules('city_name', ' City Name ', 'trim|xss_clean|required');
    //             $this->form_validation->set_rules('latitude', ' latitude ', 'trim|xss_clean|required');
    //             $this->form_validation->set_rules('longitude', ' longitude ', 'trim|xss_clean|required');
    //             $this->form_validation->set_rules('min_order_amount_for_free_delivery', 'Min order amount for free delivery ', 'trim|xss_clean');
    //             $this->form_validation->set_rules('time_to_travel', 'Time to Travel ', 'trim|xss_clean|required');
    //             $this->form_validation->set_rules('max_deliverable_distance', 'Max Deliverable Distance ', 'trim|xss_clean|required');
    //             $this->form_validation->set_rules('delivery_charge_method', 'Delivery Charge Method ', 'trim|xss_clean|required');
    //             $this->form_validation->set_rules('fixed_charge', 'fixed_charge ', 'trim|xss_clean');
    //             $this->form_validation->set_rules('per_km_charge', 'per_km_charge ', 'trim|xss_clean');
    //             $this->form_validation->set_rules('range_wise_charges', 'range_wise_charges ', 'trim|xss_clean');
    //             $this->form_validation->set_rules('bordering_city_ids[]', 'bordering_city_ids ', 'trim|xss_clean');
    //         } else {
    //             $this->form_validation->set_rules('geolocation_type', ' Geolocation Type ', 'trim|xss_clean|required');
    //             $this->form_validation->set_rules('radius', ' radius ', 'trim|xss_clean');
    //             $this->form_validation->set_rules('boundary_points', ' boundary_points ', 'trim|xss_clean|required');
    //         }

    //         if (!$this->form_validation->run()) {

    //             $this->response['error'] = true;
    //             $this->response['csrfName'] = $this->security->get_csrf_token_name();
    //             $this->response['csrfHash'] = $this->security->get_csrf_hash();
    //             $this->response['messages'] = array(
    //                 'city_name' => form_error('city_name'),
    //                 'latitude' => form_error('latitude'),
    //                 'longitude' => form_error('longitude'),
    //                 'time_to_travel' => form_error('time_to_travel'),
    //                 'max_deliverable_distance' => form_error('max_deliverable_distance'),
    //                 'min_order_amount_for_free_delivery' => form_error('min_order_amount_for_free_delivery'),
    //                 'delivery_charge_method' => form_error('delivery_charge_method'),
    //                 'fixed_charge' => form_error('fixed_charge'),
    //                 'per_km_charge' => form_error('per_km_charge'),
    //                 'range_wise_charges' => form_error('range_wise_charges'),
    //                 'bordering_city_ids' => form_error('bordering_city_ids'),
    //                 'geolocation_type' => form_error('geolocation_type'),
    //                 'radius' => form_error('radius'),
    //                 'boundary_points' => form_error('boundary_points'),                    
    //             );
    //             print_r(json_encode($this->response));
    //         } else {
    //             if (isset($_POST['edit_city']) && !empty($_POST['edit_city']) && $_POST['edit_city'] != "") {
    //                 if (isset($_POST['city_name']) && !empty($_POST['city_name'])) {
    //                     if (is_exist(["name" => $_POST['city_name']], 'cities', $_POST['edit_city'])) {
    //                         $response["error"]   = true;
    //                         $response["message"] = "City Name Already Exist ! Provide a unique name";
    //                         $response['csrfName'] = $this->security->get_csrf_token_name();
    //                         $response['csrfHash'] = $this->security->get_csrf_hash();
    //                         $response["data"] = array();
    //                         echo json_encode($response);
    //                         return false;
    //                     }
    //                 }
    //             } else {
    //                 if (is_exist(['name' => $_POST['city_name']], 'cities')) {
    //                     $response["error"]   = true;
    //                     $response["message"] = "City Name Already Exist ! Provide a unique name";
    //                     $response['csrfName'] = $this->security->get_csrf_token_name();
    //                     $response['csrfHash'] = $this->security->get_csrf_hash();
    //                     $response["data"] = array();
    //                     echo json_encode($response);
    //                     return false;
    //                 }
    //             }
    //             $delivery_charge_method = $this->input->post('delivery_charge_method', true);
    //             if ($delivery_charge_method == 'fixed_charge') {
    //                 $_POST['charges'] = $this->input->post('fixed_charge', true);
    //             }
    //             if ($delivery_charge_method == 'per_km_charge') {
    //                 $_POST['charges'] = $this->input->post('per_km_charge', true);
    //             }
    //             if ($delivery_charge_method == 'range_wise_charges') {
    //                 $_POST['charges'] = $this->input->post('range_wise_charges', true);
    //             }
    //             if (!empty($_POST['bordering_city_ids'])) {
    //                 $_POST['bordering_city_ids'] = implode(",", $_POST['bordering_city_ids']);
    //             } else {
    //                 $_POST['bordering_city_ids'] = NULL;
    //             }

    //             $this->Area_model->add_city($_POST);
    //             $this->response['error'] = false;
    //             $this->response['csrfName'] = $this->security->get_csrf_token_name();
    //             $this->response['csrfHash'] = $this->security->get_csrf_hash();
    //             $message = (isset($_POST['edit_city']) && !empty($_POST['edit_city']) && $_POST['edit_city'] != "") ? 'City Updated Successfully' : 'City Added Successfully';
    //             $this->response['message'] = $message;
    //             print_r(json_encode($this->response));
    //         }
    //     } else {
    //         redirect('admin/login', 'refresh');
    //     }
    // }

    public function add_city()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (isset($_POST['edit_city'])) {
                if (print_msg(!has_permissions('update', 'city'), PERMISSION_ERROR_MSG, 'city')) {
                    return false;
                }
            } else {
                if (print_msg(!has_permissions('create', 'city'), PERMISSION_ERROR_MSG, 'city')) {
                    return false;
                }
            }
            if (!isset($_POST['boundary_points']) && empty($_POST['boundary_points'])) {

                $this->form_validation->set_rules('city_name', ' City Name ', 'trim|xss_clean|required');
                $this->form_validation->set_rules('latitude', ' latitude ', 'trim|xss_clean|required');
                $this->form_validation->set_rules('longitude', ' longitude ', 'trim|xss_clean|required');
                $this->form_validation->set_rules('min_order_amount_for_free_delivery', 'Min order amount for free delivery ', 'trim|xss_clean|required');
                $this->form_validation->set_rules('time_to_travel', 'Time to Travel ', 'trim|xss_clean|required');
                $this->form_validation->set_rules('max_deliverable_distance', 'Max Deliverable Distance ', 'trim|xss_clean|required');
                $this->form_validation->set_rules('delivery_charge_method', 'Delivery Charge Method ', 'trim|xss_clean|required');
                $this->form_validation->set_rules('fixed_charge', 'Fixed Charge ', 'trim|xss_clean');
                $this->form_validation->set_rules('per_km_charge', 'Per Km Charge ', 'trim|xss_clean');
                $this->form_validation->set_rules('range_wise_charges', 'Range Wise Charges ', 'trim|xss_clean');
                $this->form_validation->set_rules('bordering_city_ids[]', 'Bordering City Ids ', 'trim|xss_clean');
            } else {
                $this->form_validation->set_rules('city_id', 'City', 'trim|xss_clean|required');
                $this->form_validation->set_rules('zone_name', 'Zone Name', 'trim|xss_clean|required');
                $this->form_validation->set_rules('geolocation_type', ' Geolocation Type ', 'trim|xss_clean|required');
                $this->form_validation->set_rules('radius', ' radius ', 'trim|xss_clean');
                $this->form_validation->set_rules('boundary_points', ' boundary_points ', 'trim|xss_clean|required');
            }

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['messages'] = array(
                    'city_id' => form_error('city_id'),
                    'zone_name' => form_error('zone_name'),
                    'city_name' => form_error('city_name'),
                    'latitude' => form_error('latitude'),
                    'longitude' => form_error('longitude'),
                    'time_to_travel' => form_error('time_to_travel'),
                    'max_deliverable_distance' => form_error('max_deliverable_distance'),
                    'min_order_amount_for_free_delivery' => form_error('min_order_amount_for_free_delivery'),
                    'delivery_charge_method' => form_error('delivery_charge_method'),
                    'fixed_charge' => form_error('fixed_charge'),
                    'per_km_charge' => form_error('per_km_charge'),
                    'range_wise_charges' => form_error('range_wise_charges'),
                    'bordering_city_ids' => form_error('bordering_city_ids'),
                    'geolocation_type' => form_error('geolocation_type'),
                    'radius' => form_error('radius'),
                    'boundary_points' => form_error('boundary_points'),
                );
                print_r(json_encode($this->response));
            } else {
                $boundary_points_raw = isset($_POST['boundary_points']) ? $_POST['boundary_points'] : null;
                $boundary_points_arr = isset($boundary_points_raw) ? json_decode($boundary_points_raw, true) : null;

                // Validate JSON
                if (!empty($boundary_points_arr) && is_array($boundary_points_arr)) {
                    // $response["error"] = true;
                    // $response["message"] = "Invalid boundary points data.";
                    // $response['csrfName'] = $this->security->get_csrf_token_name();
                    // $response['csrfHash'] = $this->security->get_csrf_hash();
                    // echo json_encode($response);
                    // return false;

                    // Validate lat & lng
                    foreach ($boundary_points_arr as $point) {
                        if (
                            !isset($point['lat'], $point['lng']) ||
                            $point['lat'] === null || $point['lng'] === null ||
                            $point['lat'] === '' || $point['lng'] === ''
                        ) {
                            $response["error"] = true;
                            $response["message"] = "Draw zone boundaries! Invalid latitude or longitude in boundary points.";
                            $response['csrfName'] = $this->security->get_csrf_token_name();
                            $response['csrfHash'] = $this->security->get_csrf_hash();
                            echo json_encode($response);
                            return false;
                        }
                    }

                    // Handle zone creation/update
                    if (isset($_POST['boundary_points']) && !empty($_POST['boundary_points'])) {
                        $city_id = $this->input->post('city_id', true);
                        $zone_name = $this->input->post('zone_name', true);
                        $boundary_points = $this->input->post('boundary_points', true);
                        $geolocation_type = $this->input->post('geolocation_type', true);
                        $radius = $this->input->post('radius', true);

                        // Check if zone name already exists in this city
                        if ($this->Zone_model->zone_name_exists($city_id, $zone_name)) {
                            $response["error"] = true;
                            $response["message"] = "Zone name already exists in this city! Please provide a unique zone name.";
                            $response['csrfName'] = $this->security->get_csrf_token_name();
                            $response['csrfHash'] = $this->security->get_csrf_hash();
                            echo json_encode($response);
                            return false;
                        }

                        // Check for zone overlap
                        $overlap_check = $this->Zone_model->check_zone_overlap($city_id, $boundary_points, $geolocation_type, $radius);
                        if ($overlap_check !== false && isset($overlap_check['overlap'])) {
                            $response["error"] = true;
                            $response["message"] = "This zone overlaps with existing zone '" . $overlap_check['zone_name'] . "'. Zones within the same city cannot overlap.";
                            $response['csrfName'] = $this->security->get_csrf_token_name();
                            $response['csrfHash'] = $this->security->get_csrf_hash();
                            echo json_encode($response);
                            return false;
                        }

                        // Create the zone
                        $zone_data = [
                            'city_id' => $city_id,
                            'zone_name' => $zone_name,
                            'boundary_points' => $boundary_points,
                            'geolocation_type' => $geolocation_type,
                            'radius' => $radius
                        ];

                        $this->Zone_model->add_zone($zone_data);
                        $this->response['error'] = false;
                        $this->response['csrfName'] = $this->security->get_csrf_token_name();
                        $this->response['csrfHash'] = $this->security->get_csrf_hash();
                        $this->response['message'] = 'Zone Created Successfully';
                        $this->response['location'] = base_url('admin/area/manage-zones');
                        print_r(json_encode($this->response));
                        return;
                    }
                }


                // Handle city creation/update (original logic)
                if (isset($_POST['edit_city']) && !empty($_POST['edit_city']) && $_POST['edit_city'] != "") {
                    if (isset($_POST['city_name']) && !empty($_POST['city_name'])) {
                        if (is_exist(["name" => $_POST['city_name']], 'cities', $_POST['edit_city'])) {
                            $response["error"] = true;
                            $response["message"] = "City Name Already Exist ! Provide a unique name";
                            $response['csrfName'] = $this->security->get_csrf_token_name();
                            $response['csrfHash'] = $this->security->get_csrf_hash();
                            $response["data"] = array();
                            echo json_encode($response);
                            return false;
                        }
                    }
                } else {
                    if (is_exist(['name' => $_POST['city_name']], 'cities')) {
                        $response["error"] = true;
                        $response["message"] = "City Name Already Exist ! Provide a unique name";
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                }
                $delivery_charge_method = $this->input->post('delivery_charge_method', true);
                if ($delivery_charge_method == 'fixed_charge') {
                    $_POST['charges'] = $this->input->post('fixed_charge', true);
                }
                if ($delivery_charge_method == 'per_km_charge') {
                    $_POST['charges'] = $this->input->post('per_km_charge', true);
                }
                if ($delivery_charge_method == 'range_wise_charges') {
                    $_POST['charges'] = $this->input->post('range_wise_charges', true);
                }
                if (!empty($_POST['bordering_city_ids'])) {
                    $_POST['bordering_city_ids'] = implode(",", $_POST['bordering_city_ids']);
                } else {
                    $_POST['bordering_city_ids'] = NULL;
                }

                $this->Area_model->add_city($_POST);
                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $message = (isset($_POST['edit_city']) && !empty($_POST['edit_city']) && $_POST['edit_city'] != "") ? 'City Updated Successfully' : 'City Added Successfully';
                $this->response['message'] = $message;
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }


    public function get_cities()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            $search = (isset($_GET['search'])) ? $_GET['search'] : null;
            $cities = $this->Area_model->get_cities($sort = "c.name", $order = "ASC", $search);
            $this->response['data'] = $cities['data'];
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            print_r(json_encode($this->response));
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function update_delivery_charge_method()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'city'), PERMISSION_ERROR_MSG, 'city')) {
                return false;
            }
            if (!isset($_GET['delivery_charge_method']) || empty($_GET['delivery_charge_method']) || $_GET['delivery_charge_method'] == "") {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = "Select delivery charge method.";
                print_r(json_encode($this->response));
                return false;
            }

            if (!in_array($this->input->get('delivery_charge_method', true), ['range_wise_charges', 'fixed_charge', 'per_km_charge'])) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = "Select proper method for delivery charge.";
                print_r(json_encode($this->response));
                return false;
            }
            if (!isset($_GET['charges']) || empty($_GET['charges']) || $_GET['charges'] == "") {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = "Select ranges for delivery charge.";
                print_r(json_encode($this->response));
                return false;
            } else {
                $delivery_charge_method = $this->input->get('delivery_charge_method', true);
                $charges = $this->input->get('charges', true);
                insert_details([$delivery_charge_method => $charges, 'delivery_charge_method' => $delivery_charge_method], "cities");
                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = "Charges Updated Successfully.";
                print_r(json_encode($this->response));
                return false;
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function search_location()
    {
        $_POST = $this->input->post(NULL, true);
        $this->form_validation->set_rules('search', 'search', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {
            $search = $this->input->post('search', true);
            $settings = get_settings('system_settings', true);
            $url = "https://places.googleapis.com/v1/places:searchText";

            $data = json_encode([
                "textQuery" => $search,
                "locationBias" => [
                    "circle" => [
                        "center" => [
                            "latitude" => 20.5937,  // India's center
                            "longitude" => 78.9629
                        ],
                        "radius" => 500000.0  // 500km radius
                    ]
                ]
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "X-Goog-Api-Key: " . $settings['google_map_javascript_api_key'],
                "X-Goog-FieldMask: places.displayName,places.formattedAddress,places.location"
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $this->response['error'] = true;
                $this->response['message'] = 'Location not found';
                $this->response['data'] = [];
            } else {
                $this->response['error'] = false;
                $this->response['message'] = 'Location detail get successfully';
                $this->response['data'] = json_decode($response, true);
            }

            curl_close($ch);
            print_r(json_encode($this->response));
        }
    }

    // ========================= zones 
    public function get_zones_by_city()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!isset($_GET['city_id']) || empty($_GET['city_id'])) {
                $response['error'] = true;
                $response['message'] = 'City ID is required';
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                echo json_encode($response);
                return;
            }

            $city_id = $this->input->get('city_id', true);
            $zones = $this->Zone_model->get_zones_by_city($city_id);

            $response['error'] = false;
            $response['data'] = $zones;
            $response['csrfName'] = $this->security->get_csrf_token_name();
            $response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($response);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function manage_zones()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('read', 'city')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }

            $this->data['main_page'] = TABLES . 'manage-zones';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Zone Management | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Zone Management | ' . $settings['app_name'];
            $this->data['google_map_api_key'] = $settings['google_map_javascript_api_key'];
            $this->data['cities'] = fetch_details('', 'cities', 'id,name');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function zone_list()
    {
        // print_r($_GET);
        // die;
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('read', 'city')) {
                echo json_encode(['error' => true, 'message' => PERMISSION_ERROR_MSG]);
                return;
            }

            $offset = 0;
            $limit = 10;
            $sort = 'z.id';
            $order = 'DESC';
            $where = [];

            if (isset($_GET['offset']))
                $offset = $_GET['offset'];
            if (isset($_GET['limit']))
                $limit = $_GET['limit'];
            if (isset($_GET['sort']))
                $sort = $_GET['sort'];
            if (isset($_GET['order']))
                $order = $_GET['order'];
            if (isset($_GET['search']) && !empty($_GET['search']))
                $where['search'] = $_GET['search'];
            if (isset($_GET['city_id']) && !empty($_GET['city_id']))
                $where['city_id'] = $_GET['city_id'];
            if (isset($_GET['status']) && $_GET['status'] !== '')
                $where['status'] = (int)$_GET['status'];

            $zones = $this->Zone_model->get_all_zones($where, $sort, $order, $limit, $offset);

            $rows = [];
            $i = 1;
            foreach ($zones['data'] as $zone) {
                $row = [];
                $row['id'] = $zone['id'];
                $row['city_name'] = $zone['city_name'];
                $row['zone_name'] = $zone['zone_name'];
                $row['geolocation_type'] = ucfirst($zone['geolocation_type']);

                // Status status
                if ($zone['status'] == '1') {
                    $row['status'] = '<a class="badge badge-success text-white" >Active</a>';
                    $operations = '<a class="btn btn-warning btn-xs update_active_status mr-1 mb-1" data-table="zones" title="Deactivate" href="javascript:void(0)" data-id="' . $zone['id'] . '" data-status="' . $zone['status'] . '" ><i class="fa fa-eye-slash"></i></a>';
                } else {
                    $row['status'] = '<a class="badge badge-danger text-white" >Inactive</a>';
                    $operations = '<a class="btn btn-primary mr-1 mb-1 btn-xs update_active_status" data-table="zones" href="javascript:void(0)" title="Active" data-id="' . $zone['id'] . '" data-status="' . $zone['status'] . '" ><i class="fa fa-eye"></i></a>';
                }

                $row['date_created'] = date('d-M-Y', strtotime($zone['date_created']));

                // Actions
                $operations .= '<a href="javascript:void(0);" class="btn btn-danger btn-xs delete-zone mr-1 mb-1" data-id="' . $zone['id'] . '" title="Delete Zone"><i class="fa fa-trash"></i></a>';

                $row['operate'] = $operations;

                $rows[] = $row;
                $i++;
            }

            $response = [
                'total' => $zones['total'],
                'rows' => $rows
            ];

            echo json_encode($response);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function get_zone_details()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!isset($_GET['zone_id']) || empty($_GET['zone_id'])) {
                $response['error'] = true;
                $response['message'] = 'Zone ID is required';
                echo json_encode($response);
                return;
            }

            $zone_id = $this->input->get('zone_id', true);
            $zone = $this->Zone_model->get_zone($zone_id);

            if ($zone) {
                // Get city details
                $city = fetch_details(['id' => $zone['city_id']], 'cities');
                $zone['city_name'] = $city[0]['name'];
                $zone['city_lat'] = $city[0]['latitude'];
                $zone['city_lng'] = $city[0]['longitude'];

                $response['error'] = false;
                $response['data'] = $zone;
            } else {
                $response['error'] = true;
                $response['message'] = 'Zone not found';
            }

            echo json_encode($response);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function delete_zone()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('delete', 'city')) {
                $response['error'] = true;
                $response['message'] = PERMISSION_ERROR_MSG;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                echo json_encode($response);
                return;
            }

            if (!isset($_POST['zone_id']) || empty($_POST['zone_id'])) {
                $response['error'] = true;
                $response['message'] = 'Zone ID is required';
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                echo json_encode($response);
                return;
            }

            $zone_id = $this->input->post('zone_id', true);
            $result = $this->Zone_model->delete_zone($zone_id);

            if ($result) {
                $response['error'] = false;
                $response['message'] = 'Zone deleted successfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'Failed to delete zone';
            }

            $response['csrfName'] = $this->security->get_csrf_token_name();
            $response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($response);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function toggle_zone_status()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('update', 'city')) {
                $response['error'] = true;
                $response['message'] = PERMISSION_ERROR_MSG;
                echo json_encode($response);
                return;
            }

            if (!isset($_POST['zone_id']) || !isset($_POST['status'])) {
                $response['error'] = true;
                $response['message'] = 'Zone ID and status are required';
                echo json_encode($response);
                return;
            }

            $zone_id = $this->input->post('zone_id', true);
            $status = $this->input->post('status', true);

            $this->db->where('id', $zone_id);
            $this->db->update('zones', ['status' => $status]);

            if ($this->db->affected_rows() > 0) {
                $response['error'] = false;
                $response['message'] = 'Zone status updated successfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'Failed to update zone status';
            }

            echo json_encode($response);
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
