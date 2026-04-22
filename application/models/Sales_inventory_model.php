
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_inventory_model extends CI_Model
{
    public function get_sales_inventory_list(
        $offset = 0,
        $limit = 10,
        $sort = " oi.id ",
        $order = 'DESC'
    ) {
        if (isset($_GET['offset'])) {
            $offset = $_GET['offset'];
        }
        if (isset($_GET['limit'])) {
            $limit = $_GET['limit'];
        }

        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];

            $filters = [
                'oi.id' => $search,
                'p.name' => $search,
            ];
        }
        $count_res = $this->db->select('oi.id')
            ->join('product_variants pv', 'pv.id=oi.product_variant_id', 'left')
            ->join('products p', 'p.id=pv.product_id', 'left')
            ->where('p.name !=', '');

        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $count_res->where(" DATE(oi.date_added) >= DATE('" . $_GET['start_date'] . "') ");
            $count_res->where(" DATE(oi.date_added) <= DATE('" . $_GET['end_date'] . "') ");
        }

        if (isset($filters) && !empty($filters)) {
            $this->db->group_Start();
            $count_res->or_like($filters);
            $this->db->group_End();
        }

        if (isset($_GET['branch_id']) && $_GET['branch_id'] != null) {
            $count_res->where("oi.branch_id", $_GET['branch_id']);
        } else {
            $count_res->where("oi.branch_id", $_SESSION['branch_id']);
        }

        $sales_count = $count_res->group_by('oi.product_variant_id')->get('order_items oi')->result_array();
        $total = count($sales_count);

        $search_res = $this->db->select('oi.id,oi.product_variant_id, p.name, SUM(oi.quantity) AS qty,(p.availability OR pv.availability ) AS availability,(CASE WHEN (p.stock OR pv.stock) THEN p.stock ELSE pv.stock END) AS stock')
            ->join('product_variants pv', 'pv.id=oi.product_variant_id', 'left')
            ->join('products p', 'p.id=pv.product_id', 'left')
            ->where('p.name !=', '');

        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
            $search_res->where(" DATE(oi.date_added) >= DATE('" . $_GET['start_date'] . "') ");
            $search_res->where(" DATE(oi.date_added) <= DATE('" . $_GET['end_date'] . "') ");
        }

        if (isset($filters) && !empty($filters)) {
            $search_res->group_Start();
            $search_res->or_like($filters);
            $search_res->group_End();
        }

        if (isset($_GET['branch_id']) && !empty($_GET['branch_id'])) {
            $search_res->where("oi.branch_id", $_GET['branch_id']);
        } else {
            $search_res->where("oi.branch_id", $_SESSION['branch_id']);
        }
        $user_details = $search_res->group_by('oi.product_variant_id')->order_by($sort, "ASC")->limit($limit, $offset)->get('order_items oi')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($user_details as $row) {


            if (isset($row['stock']) && $row['stock'] != '') {
                $stock = "<span class='badge badge-success'>" . $row['stock'] . "</span>";
            } else if (($row['availability'] <= 0) && $row['stock'] <= 0) {
                $stock = "<span class='badge badge-warning'>available</span>";
            } else {
                $stock = "<span class='badge badge-danger'>N/A</span>";
            }
            $tempRow['id'] = (isset($row['id']) && $row['id'] != '') ?  $row['id'] : "-";
            $tempRow['name'] = (isset($row['name']) && $row['name'] != '') ?  stripslashes($row['name']) : "-";
            $tempRow['stock'] = $stock;
            $tempRow['qty'] = (isset($row['qty']) && $row['qty'] != '') ?  $row['qty'] : "-";
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }


    public function get_category_sales_list()
    {
        $offset = $_GET['offset'] ?? 0;
        $limit  = $_GET['limit'] ?? 10;
        $search = $_GET['search'] ?? '';

        $start_date = $_GET['start_date'] ?? '';
        $end_date   = $_GET['end_date'] ?? '';

        $branch_id = $_SESSION['branch_id'];

        $count = $this->db->select("p.category_id")
            ->from("order_items oi")
            ->join("orders o", "oi.order_id = o.id", "left")
            ->join("product_variants pv", "pv.id = oi.product_variant_id", "left")
            ->join("products p", "p.id = pv.product_id", "left")
            ->join("categories c", "c.id = p.category_id", "inner")
            ->where("o.active_status", "delivered")
            ->where("o.branch_id", $branch_id);

        if (!empty($start_date) && !empty($end_date)) {
            $count->where("DATE(o.date_added) >=", $start_date);
            $count->where("DATE(o.date_added) <=", $end_date);
        }

        if (!empty($search)) {
            $count->like("c.name", $search);
        }

        $total_rows = $count->group_by("p.category_id")->get()->result_array();
        $total = count($total_rows);

        $query = $this->db->select("
            c.name AS category_name,
            SUM(oi.quantity) AS total_qty,
            SUM(oi.quantity * oi.price) AS total_sales
        ")
            ->from("order_items oi")
            ->join("orders o", "oi.order_id = o.id", "left")
            ->join("product_variants pv", "pv.id = oi.product_variant_id", "left")
            ->join("products p", "p.id = pv.product_id", "left")
            ->join("categories c", "c.id = p.category_id", "inner")
            ->where("o.active_status", 'delivered')
            ->where("o.branch_id", $branch_id);

        if (!empty($start_date) && !empty($end_date)) {
            $query->where("DATE(o.date_added) >=", $start_date);
            $query->where("DATE(o.date_added) <=", $end_date);
        }

        if (!empty($search)) {
            $query->like("c.name", $search);
        }

        $rows = $query->group_by("p.category_id")
            ->order_by("c.name", "ASC")
            ->limit($limit, $offset)
            ->get()
            ->result_array();


        /* Format JSON Output */
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'category_name' => $row['category_name'] ?? "-",
                'total_qty'     => $row['total_qty'] ?? "0",
                'total_sales'   => number_format($row['total_sales'], 2),
            ];
        }

        echo json_encode([
            'total' => $total,
            'rows'  => $data
        ]);
    }

    // =========================================================================

    public function get_cancel_order_list()
    {
        $offset = $_GET['offset'] ?? 0;
        $limit  = $_GET['limit'] ?? 10;
        $search = $_GET['search'] ?? '';

        $start_date = $_GET['start_date'] ?? '';
        $end_date   = $_GET['end_date'] ?? '';
        $cancel_by  = $_GET['cancel_by'] ?? '';

        $branch_id = $_SESSION['branch_id'];

        // --- Base query setup for count and data (unchanged joins) ---
        $base_query = $this->db->select('o.id')
            ->from('orders o')
            ->join('order_items oi', 'oi.order_id = o.id', 'left')
            ->join('users_groups ug', 'ug.user_id = o.cancel_by', 'left')
            ->join('groups g', 'g.id = ug.group_id', 'left')
            ->where('o.active_status', 'cancelled')
            ->where('o.branch_id', $branch_id);

        // Apply date range filters
        if (!empty($start_date) && !empty($end_date)) {
            $base_query->where('DATE(oi.date_added) >=', $start_date);
            $base_query->where('DATE(oi.date_added) <=', $end_date);
        }

        // Apply filter by Group ID
        if (!empty($cancel_by)) {
            $base_query->where('g.id', $cancel_by);
        }

        // Apply search filter
        if (!empty($search)) {
            $base_query->group_start()
                ->like('oi.order_id', $search)
                ->or_like('o.reason', $search)
                ->or_like('g.name', $search)
                ->group_end();
        }

        // --- Total Count Calculation ---
        $count_query = clone $base_query;
        $total_rows = $count_query->group_by('o.id')->get()->result_array();
        $total = count($total_rows);


        // --- Query for Paginated Data (Rows) ---
        $query = $base_query->select("
        o.id,
        oi.order_id,
        o.final_total AS total,
        o.reason,
        g.name AS cancel_by_name,
        MIN(oi.date_added) AS date_added
    ", FALSE)
            ->group_by("o.id")
            ->order_by("o.id", "DESC")
            ->limit($limit, $offset)
            ->get();

        $rows = $query->result_array();

        $data = [];
        foreach ($rows as $row) {
            $group_name = strtolower($row['cancel_by_name'] ?? '');
            $badge_html = '';
            $display_name = ucfirst($group_name ?: '-');

            // Determine the Bootstrap badge class based on the group name
            switch ($group_name) {
                case 'admin':
                    // Green Badge (success)
                    $badge_class = 'badge-success';
                    break;
                case 'members':
                    // Yellow Badge (warning) - Using 'customer' assuming 'member' in your request meant 'customer'
                    $badge_class = 'badge-warning';
                    $display_name = 'Customer';
                    break;
                case 'rider':
                    // Blue Badge (primary)
                    $badge_class = 'badge-primary';
                    break;
                default:
                    // Default/Gray Badge
                    $badge_class = 'badge-secondary';
                    $display_name = '-';
                    break;
            }

            if (!empty($group_name)) {
                // Create the HTML for the badge
                $badge_html = '<span class="badge ' . $badge_class . '">' . $display_name . '</span>';
            } else {
                // Handle case where group name is not found
                $badge_html = $display_name;
            }


            $data[] = [
                'order_id'      => $row['order_id'] ?? '-',
                'total'         => number_format($row['total'] ?? 0, 2),
                // Pass the generated HTML badge string
                'cancel_by'     => $badge_html,
                'cancel_reason' => $row['reason'] ?: '-',
                'created_at'    => date('d M Y', strtotime($row['date_added'])),
            ];
        }

        echo json_encode([
            'total' => $total,
            'rows'  => $data
        ]);
    }
}
