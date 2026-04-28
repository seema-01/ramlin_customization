<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Zone_model extends CI_Model
{
    /**
     * Add or update a zone
     */
    function add_zone($data)
    {
        $zone_data = [
            'city_id' => $data['city_id'],
            'zone_name' => $data['zone_name'],
            'boundary_points' => (isset($data['boundary_points']) && !empty($data['boundary_points']) && $data['boundary_points'] != "") ? $data['boundary_points'] : NULL,
            'radius' => (isset($data['radius']) && !empty($data['radius']) && $data['radius'] != "") ? $data['radius'] : 0,
            'geolocation_type' => (isset($data['geolocation_type']) && !empty($data['geolocation_type']) && $data['geolocation_type'] != "") ? $data['geolocation_type'] : NULL,
        ];

        if (isset($data['edit_zone']) && !empty($data['edit_zone']) && $data['edit_zone'] != "") {
            $this->db->set($zone_data)->where('id', $data['edit_zone'])->update('zones');
            return $data['edit_zone'];
        } else {
            $this->db->insert('zones', $zone_data);
            return $this->db->insert_id();
        }
    }

    /**
     * Check if zones overlap using polygon intersection
     * Returns true if zones overlap, false otherwise
     */
    function check_zone_overlap($city_id, $new_boundary_points, $new_geolocation_type, $new_radius = 0, $exclude_zone_id = null)
    {
        // Get all existing zones for this city
        $this->db->select('id, zone_name, boundary_points, geolocation_type, radius');
        $this->db->where('city_id', $city_id);
        $this->db->where('status', 1);

        if ($exclude_zone_id) {
            $this->db->where('id !=', $exclude_zone_id);
        }

        $existing_zones = $this->db->get('zones')->result_array();

        if (empty($existing_zones)) {
            return false; // No existing zones, so no overlap
        }

        $new_points = json_decode($new_boundary_points, true);

        if (empty($new_points)) {
            return false;
        }

        foreach ($existing_zones as $zone) {
            $existing_points = json_decode($zone['boundary_points'], true);

            if (empty($existing_points)) {
                continue;
            }

            // Check overlap based on geolocation types
            if ($new_geolocation_type == 'polygon' && $zone['geolocation_type'] == 'polygon') {
                if ($this->check_polygon_overlap($new_points, $existing_points)) {
                    return [
                        'overlap' => true,
                        'zone_name' => $zone['zone_name']
                    ];
                }
            } elseif ($new_geolocation_type == 'circle' && $zone['geolocation_type'] == 'circle') {
                if ($this->check_circle_overlap($new_points[0], $new_radius, $existing_points[0], $zone['radius'])) {
                    return [
                        'overlap' => true,
                        'zone_name' => $zone['zone_name']
                    ];
                }
            } elseif ($new_geolocation_type == 'polygon' && $zone['geolocation_type'] == 'circle') {
                if ($this->check_polygon_circle_overlap($new_points, $existing_points[0], $zone['radius'])) {
                    return [
                        'overlap' => true,
                        'zone_name' => $zone['zone_name']
                    ];
                }
            } elseif ($new_geolocation_type == 'circle' && $zone['geolocation_type'] == 'polygon') {
                if ($this->check_polygon_circle_overlap($existing_points, $new_points[0], $new_radius)) {
                    return [
                        'overlap' => true,
                        'zone_name' => $zone['zone_name']
                    ];
                }
            }
        }

        return false;
    }

    /**
     * Check if two polygons overlap
     */
    private function check_polygon_overlap($polygon1, $polygon2)
    {
        // Check if any vertex of polygon1 is inside polygon2
        foreach ($polygon1 as $point) {
            if ($this->point_in_polygon($point, $polygon2)) {
                return true;
            }
        }

        // Check if any vertex of polygon2 is inside polygon1
        foreach ($polygon2 as $point) {
            if ($this->point_in_polygon($point, $polygon1)) {
                return true;
            }
        }

        // Check if any edges intersect
        for ($i = 0; $i < count($polygon1); $i++) {
            $p1 = $polygon1[$i];
            $p2 = $polygon1[($i + 1) % count($polygon1)];

            for ($j = 0; $j < count($polygon2); $j++) {
                $p3 = $polygon2[$j];
                $p4 = $polygon2[($j + 1) % count($polygon2)];

                if ($this->line_segments_intersect($p1, $p2, $p3, $p4)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a point is inside a polygon using ray casting algorithm
     */
    private function point_in_polygon($point, $polygon)
    {
        $x = $point['lng'];
        $y = $point['lat'];
        $inside = false;

        for ($i = 0, $j = count($polygon) - 1; $i < count($polygon); $j = $i++) {
            $xi = $polygon[$i]['lng'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];

            $intersect = (($yi > $y) != ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Check if two line segments intersect
     */
    private function line_segments_intersect($p1, $p2, $p3, $p4)
    {
        $d1 = $this->direction($p3, $p4, $p1);
        $d2 = $this->direction($p3, $p4, $p2);
        $d3 = $this->direction($p1, $p2, $p3);
        $d4 = $this->direction($p1, $p2, $p4);

        if (
            (($d1 > 0 && $d2 < 0) || ($d1 < 0 && $d2 > 0)) &&
            (($d3 > 0 && $d4 < 0) || ($d3 < 0 && $d4 > 0))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Calculate direction for line segment intersection
     */
    private function direction($p1, $p2, $p3)
    {
        return ($p3['lng'] - $p1['lng']) * ($p2['lat'] - $p1['lat']) -
            ($p2['lng'] - $p1['lng']) * ($p3['lat'] - $p1['lat']);
    }

    /**
     * Check if two circles overlap
     */
    private function check_circle_overlap($center1, $radius1, $center2, $radius2)
    {
        $distance = $this->calculate_distance(
            $center1['lat'],
            $center1['lng'],
            $center2['lat'],
            $center2['lng']
        );

        // Convert radius from whatever unit to meters (assuming radius is in meters)
        $r1 = sqrt($radius1) * 100;
        $r2 = sqrt($radius2) * 100;

        return $distance < ($r1 + $r2);
    }

    /**
     * Check if a polygon and circle overlap
     */
    private function check_polygon_circle_overlap($polygon, $circle_center, $radius)
    {
        $circle_radius_meters = sqrt($radius) * 100;

        // Check if circle center is inside polygon
        if ($this->point_in_polygon($circle_center, $polygon)) {
            return true;
        }

        // Check if any polygon vertex is inside circle
        foreach ($polygon as $point) {
            $distance = $this->calculate_distance(
                $point['lat'],
                $point['lng'],
                $circle_center['lat'],
                $circle_center['lng']
            );

            if ($distance <= $circle_radius_meters) {
                return true;
            }
        }

        // Check if circle intersects any polygon edge
        for ($i = 0; $i < count($polygon); $i++) {
            $p1 = $polygon[$i];
            $p2 = $polygon[($i + 1) % count($polygon)];

            if ($this->circle_line_segment_intersect($circle_center, $circle_radius_meters, $p1, $p2)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a circle intersects with a line segment
     */
    private function circle_line_segment_intersect($circle_center, $radius, $p1, $p2)
    {
        // Calculate distance from circle center to line segment
        $distance = $this->point_to_line_distance($circle_center, $p1, $p2);
        return $distance <= $radius;
    }

    /**
     * Calculate distance from a point to a line segment
     */
    private function point_to_line_distance($point, $line_start, $line_end)
    {
        $x0 = $point['lng'];
        $y0 = $point['lat'];
        $x1 = $line_start['lng'];
        $y1 = $line_start['lat'];
        $x2 = $line_end['lng'];
        $y2 = $line_end['lat'];

        $dx = $x2 - $x1;
        $dy = $y2 - $y1;

        if ($dx == 0 && $dy == 0) {
            // Line segment is a point
            return $this->calculate_distance($y0, $x0, $y1, $x1);
        }

        $t = (($x0 - $x1) * $dx + ($y0 - $y1) * $dy) / ($dx * $dx + $dy * $dy);
        $t = max(0, min(1, $t));

        $nearest_x = $x1 + $t * $dx;
        $nearest_y = $y1 + $t * $dy;

        return $this->calculate_distance($y0, $x0, $nearest_y, $nearest_x);
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculate_distance($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth_radius * $c;
    }

    /**
     * Get all zones for a city
     */
    function get_zones_by_city($city_id)
    {
        $this->db->select('*');
        $this->db->where('city_id', $city_id);
        $this->db->where('status', 1);
        $this->db->order_by('zone_name', 'ASC');
        return $this->db->get('zones')->result_array();
    }

    /**
     * Check if zone name exists in city
     */
    function zone_name_exists($city_id, $zone_name, $exclude_zone_id = null)
    {
        $this->db->where('city_id', $city_id);
        $this->db->where('zone_name', $zone_name);

        if ($exclude_zone_id) {
            $this->db->where('id !=', $exclude_zone_id);
        }

        $result = $this->db->get('zones')->num_rows();
        return $result > 0;
    }

    /**
     * Delete a zone
     */
    function delete_zone($zone_id)
    {
        return $this->db->where('id', $zone_id)->delete('zones');
    }

    /**
     * Get zone details
     */
    function get_zone($zone_id)
    {
        return $this->db->where('id', $zone_id)->get('zones')->row_array();
    }

    /**
     * Get all zones with city names for management table
     */
    function get_all_zones($where = [], $sort = 'z.id', $order = 'DESC', $limit = 10, $offset = 0)
    {
        $this->db->select('z.*, c.name as city_name');
        $this->db->from('zones z');
        $this->db->join('cities c', 'z.city_id = c.id', 'left');

        if (isset($where['search']) && !empty($where['search'])) {
            $this->db->group_start();
            $this->db->like('z.zone_name', $where['search']);
            $this->db->or_like('c.name', $where['search']);
            $this->db->group_end();
        }

        if (isset($where['city_id']) && !empty($where['city_id'])) {
            $this->db->where('z.city_id', $where['city_id']);
        }
        if (isset($where['status']) && $where['status'] !== '') {
            $this->db->where('z.status', (int) $where['status']);
        }

        // Get total count
        $total = $this->db->count_all_results('', FALSE);

        // Apply sorting and pagination
        $this->db->order_by($sort, $order);
        $this->db->limit($limit, $offset);

        $zones = $this->db->get()->result_array();

        return [
            'total' => $total,
            'data' => $zones
        ];
    }
}
