<?php
namespace CDDU_Manager\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class TimesheetsController {
    public function register_routes(): void {
        register_rest_route('cddu/v1', '/timesheets', [
            [
                'methods'  => 'POST',
                'callback' => [$this, 'create_timesheet'],
                'permission_callback' => function() {
                    return is_user_logged_in() && current_user_can('read');
                }
            ],
        ]);
    }

    public function create_timesheet(WP_REST_Request $req) {
        $data = $req->get_json_params() ?: $req->get_params();
        $month = sanitize_text_field($data['month'] ?? '');
        $hours = floatval($data['hours'] ?? 0);
        $contract_id = intval($data['contract_id'] ?? 0);

        if (!$month || !$contract_id) {
            return new WP_Error('invalid_data', 'month et contract_id requis', ['status' => 400]);
        }

        $post_id = wp_insert_post([
            'post_type'   => 'cddu_timesheet',
            'post_status' => 'publish',
            'post_title'  => 'Timesheet ' . $month . ' – Contrat #' . $contract_id,
            'meta_input'  => [
                'month' => $month,
                'hours' => $hours,
                'contract_id' => $contract_id,
                'user_id' => get_current_user_id(),
            ],
        ]);

        if (is_wp_error($post_id)) { return $post_id; }

        // TODO: comparer avec heures prévues et déclencher avenant si dépassement
        do_action('cddu/timesheet_created', $post_id);

        return new WP_REST_Response(['id' => $post_id], 201);
    }
}
