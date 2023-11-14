<?php
/*
Template Name: Stripe Subscription Template
*/
get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <h1>Stripe Data</h1>

        <?php
        $stripe_data = fetch_stripe_data();

        if ($stripe_data) {
            echo '<h2>Customer Data:</h2>';
            echo '<ul>';
            foreach ($stripe_data['customers'] as $customer) {
                echo '<li>' . esc_html($customer->name) . '</li>';
            }
            echo '</ul>';

            echo '<h2>Plan Data:</h2>';
            echo '<ul>';
            foreach ($stripe_data['plans'] as $plan) {
                echo '<li>' . esc_html($plan->name) . '</li>';
            }
            echo '</ul>';
        } else {
            echo 'Failed to fetch Stripe data.';
        }
        ?>
    </main>
</div>

<?php
get_footer();
