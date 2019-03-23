<?php
/**
 * FES integration class file
 * 
 * @package EDD Commissions Payouts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class EDD_Commissions_Payouts_FES {

    public function __construct() {
        add_filter( 'fes_template_paths', array( $this, 'template_paths' ) );
        add_action( 'eddc_before_commissions_overview', array( $this, 'frontend_payout_methods' ) );
        add_action( 'wp_loaded', array( $this, 'user_process_payout_method' ) );
        add_action( 'wp_ajax_edd_user_process_payout_method', array( $this, 'user_process_payout_method' ) );
        add_action( 'edd_dashboard_earnings_before_payout_methods', array( $this, 'payout_method_notices' ) );
    }


    /**
     * Load the Payout Methods template file
     *
     * @param integer $user_id
     * @return void
     */
    public function frontend_payout_methods( $user_id ) {
        EDD_FES()->templates->fes_get_template_part( 'frontend', 'payout-methods' );
    }


    /**
     * Add our template directory to the FES template locator
     *
     * @param array $file_paths
     * @return array
     */
    public function template_paths( $file_paths ) {
        $file_paths[9876] = EDD_COMMISSIONS_PAYOUTS_PLUGIN_DIR . 'templates';

        return $file_paths;
    }


    public function user_process_payout_method() {
        if ( isset( $_REQUEST['edd_enable_payout_method'] ) ) {
            try {
                $payout_method = $_REQUEST['edd_enable_payout_method'];

                if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'enable_payout_method' ) ) {
                    throw new Exception( __( 'Invalid nonce, please try again.', 'edd-commissions-payouts' ) );
                }

                if ( ! is_user_logged_in() ) {
                    throw new Exception( __( 'You must be logged in to do that.', 'edd-commissions-payouts' ) );
                }

                if ( ! EDD_FES()->vendors->vendor_is_status( 'approved' ) ) {
                    throw new Exception( __( 'You must be an approved vendor to do that.', 'edd-commissions-payouts' ) );
                }

                // Returns object instance of EDD_Commissions_Payouts_Method
                $response = EDD_Commissions_Payouts()->helper->add_user_payout_method( $payout_method );

                // AJAX response
                if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                    wp_send_json( array(
                        'type'          => 'success',
                        'message'       => __( 'Success', 'edd-commissions-payouts' ),
                        'redirect'      => $response->get_redirect_uri()
                    ) );
                }
            }catch( Exception $e ) {
                if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                    wp_send_json( array(
                        'type'          => 'error',
                        'message'       => $e->getMessage() 
                    ) );
                }
            }
        }
    }


    public function payout_method_notices() {

    }

}