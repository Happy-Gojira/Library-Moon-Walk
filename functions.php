<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array(  ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10 );

if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_separate', trailingslashit( get_stylesheet_directory_uri() ) . 'ctc-style.css', array( 'chld_thm_cfg_parent','vantage-style','font-awesome' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 20 );

// END ENQUEUE PARENT ACTION
function moon_insert_into_db() {

    global $wpdb;
    // creates my_table in database if not exists
    $table = $wpdb->prefix . "library_steps";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
        `distance` INT(10) NOT NULL,
        `distance_type` text NOT NULL,
        `zipcode` INT(5) NOT NULL,
        `datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY  (`id`)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    // starts output buffering
    ob_start();
    ?>
    <form action="#v_form" name="stepsToMoon" method="POST" class="moonwalkform" id="v_form">
      <fieldset>
        <legend>Input your Exercise--steps, miles, or minutes</legend>
        <input type="text" id="distance" name="distance" placeholder="Enter Your Distance or Time"><br>
        <input type="radio" name="distance_type" value="steps" id="steps" checked>
        <label for="steps">Steps</label>
        <input type="radio" name="distance_type" id="miles" value="miles">
        <label for="miles">Miles</label>
        <input type="radio" name="distance_type" id="minutes" value="minutes">
        <label for="minutes">Minutes</label><br>
        <input class="zip" type="text" name="zipcode" maxlength="6" placeholder="Enter Your Zip Code">
      </fieldset>
      <input type="submit" name="submit_form" value="submit" />
    </form>
    <?php
    $html = ob_get_clean();
    // does the inserting, in case the form is filled and submitted
    if ( isset( $_POST["submit_form"] ) && ($_POST["distance"] != "" && strlen($_POST["zipcode"]) == 5 )) {
        $table = $wpdb->prefix."library_steps";
        $distance = sanitize_text_field($_POST["distance"]);
        $distance = filter_var($_POST["distance"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
        $distance_type = $_POST["distance_type"];
        $zipcode = sanitize_text_field($_POST["zipcode"]);

        // convert the distance into Miles
        $steps_exercise_constant = 2000;
        $minutes_exercise_constant = 30;
        switch ($distance_type) {
          case 'steps':
            $distance = $distance / $steps_exercise_constant;
            if ($distance < 1) {
              $distance = 1;
            }
            else {
              $distance = round($distance);
            }
            break;
          case 'minutes':
            $distance = $distance / $minutes_exercise_constant;
            if ($distance < 1) {
              $distance = 1;
            }
            else {
              $distance = round($distance);
            }
            break;
          }

        $wpdb->insert(
            $table,
            array(
                'distance' => $distance,
                'distance_type' => $distance_type,
                'zipcode' => $zipcode
            )
        );
        if ($distance == 1) {
          $html = "<p>You moved <strong>$distance mile</strong> closer to the moon. You're awesome!!</p><br><p></p>";
        }
        else {
          $html = "<p>You moved <strong>$distance miles</strong> closer to the moon. You're awesome!!</p>";
        }
    }
    // if the form is submitted but isn't filled correctly
    if ( isset( $_POST["submit_form"] ) && ($_POST["distance"] == "" || strlen($_POST["zipcode"]) != 5))
        $html .= "<p>You need to enter a distance and a zipcode.</p>";
    // outputs everything
    return $html;

}
// adds a shortcode you can use: [insert-into-db]
add_shortcode('moon-db-insert', 'moon_insert_into_db');

// function to pull the data and build a bar graph
function jms_pull_from_db() {

    global $wpdb;
    $total_miles_moon = 238900;
    $table_name = $wpdb->prefix . "library_steps";
    $query ="SELECT SUM(distance) FROM $table_name";
    $results = $wpdb->get_var($query);
    $percentage = ($results/$total_miles_moon)*100;

    ?>
    <strong>Total Miles moved so far: <?php echo($results); ?> Miles</strong>
    <div id="progress-bar" class="all-rounded">
      <div id="progress-bar-percentage" class="all-rounded" style="width: <?php echo($percentage); ?>%"><span><?php echo($results); ?>/238,900 miles</span></div>
    </div>
    <?php

}
// adds a shortcode you can use: [insert-into-db]
add_shortcode('jms-pull', 'jms_pull_from_db');
