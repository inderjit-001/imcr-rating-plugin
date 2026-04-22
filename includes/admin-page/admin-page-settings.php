<?php

function imcr_options_page_html() {
    ?>
    <div class="wrap">
      <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
      <form action="options.php" method="post">
        <?php
        // output security fields for the registered setting "imcr_options"
        settings_fields( 'imcr_options_group' );
        // output setting sections and their fields
        // (sections are registered for "imcr", each field is registered to a specific section)
        do_settings_sections( 'imcr_options' );
        // output save settings button
        submit_button( __( 'Save Settings', 'ijs-mcr' ) );
        ?>
      </form>
    </div>
    <?php
}

