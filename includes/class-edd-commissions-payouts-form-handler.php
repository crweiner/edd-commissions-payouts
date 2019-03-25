<?php
/**
 * Form processing class file
 * 
 * @package EDD Commissions Payouts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class EDD_Commissions_Payouts_Form_Handler {

    public function __construct() {
        // Process non AJAX requests
        add_action( 'wp_loaded', array( $this, 'user_process_toggle_payout_method' ) );
        add_action( 'wp_loaded', array( $this, 'user_process_preferred_payout_method' ) );

        // AJAX requests
        add_action( 'wp_ajax_edd_user_process_toggle_payout_method', array( $this, 'user_process_toggle_payout_method' ) );
        add_action( 'wp_ajax_edd_user_process_preferred_payout_method', array( $this, 'user_process_preferred_payout_method' ) );
    }
    

    /**
     * Process both AJAX and non AJAX request to toggle the enabled status of user payout method
     *
     * @return void
     */
    public function user_process_toggle_payout_method() {
        if ( isset( $_REQUEST['edd_action'] ) && $_REQUEST['edd_action'] == 'toggle_payout_method' ) {
            try {
                $payout_method = isset( $_REQUEST['edd_payout_method'] ) ? $_REQUEST['edd_payout_method'] : '';
                $payout_action = isset( $_REQUEST['edd_payout_action'] ) ? $_REQUEST['edd_payout_action'] : '';

                if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'process_payout_method' ) ) {
                    throw new Exception( __( 'Invalid nonce, please try again.', 'edd-commissions-payouts' ) );
                }

                if ( ! is_user_logged_in() ) {
                    throw new Exception( __( 'You must be logged in to do that.', 'edd-commissions-payouts' ) );
                }

                if ( ! EDD_FES()->vendors->vendor_is_status( 'approved' ) ) {
                    throw new Exception( __( 'You must be an approved vendor to do that.', 'edd-commissions-payouts' ) );
                }

                if ( ! in_array( $payout_action, array( 'enable', 'remove' ) ) ) {
                    throw new Exception( __( 'Invalid request.', 'edd-commissions-payouts' ) );
                }

                if ( 'enable' === $payout_action ) {
                    // Returns object instance of EDD_Commissions_Payouts_Method
                    $response = EDD_Commissions_Payouts()->helper->enable_user_payout_method( $payout_method );
                }else {
                    // Returns object instance of EDD_Commissions_Payouts_Method
                    $response = EDD_Commissions_Payouts()->helper->remove_user_payout_method( $payout_method );
                }

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


    /**
     * Process both AJAX and non AJAX request to toggle the preferred payout method of a user
     *
     * @return void
     */
    public function user_process_preferred_payout_method() {
        if ( isset( $_REQUEST['edd_action'] ) && $_REQUEST['edd_action'] == 'preferred_payout_method' ) {
            try {
                $payout_method = isset( $_REQUEST['edd_payout_method'] ) ? $_REQUEST['edd_payout_method'] : '';

                if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'process_payout_method' ) ) {
                    throw new Exception( __( 'Invalid nonce, please try again.', 'edd-commissions-payouts' ) );
                }

                if ( ! is_user_logged_in() ) {
                    throw new Exception( __( 'You must be logged in to do that.', 'edd-commissions-payouts' ) );
                }

                if ( ! EDD_FES()->vendors->vendor_is_status( 'approved' ) ) {
                    throw new Exception( __( 'You must be an approved vendor to do that.', 'edd-commissions-payouts' ) );
                }

                // Returns object instance of EDD_Commissions_Payouts_Method
                $response = EDD_Commissions_Payouts()->helper->set_user_preferred_payout_method( $payout_method );

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
}