<?php

/**
 * Appends message to standard log file, debug.log.
 *
 * If log file does not exist in /wp-content enter the following:
 *   touch debug.log
 * You can tail the log file with: tail -f debug.log
 */
if( ! function_exists( '_log_debug' ) ) {
  function stitchz_social_login_log_debug( $message ) {
    if( TRUE === WP_DEBUG ) {
      if( is_array( $message ) || is_object( $message ) ) {
        error_log( var_export( $message, TRUE ) );
      } else {
        error_log( $message );
      }
    }
  }
}