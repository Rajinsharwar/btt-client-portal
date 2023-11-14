<?php
/*
Plugin Name: BTT Client Portal
Description: Display data in the user Dashboard from the Stripe API.
Version: 1.0
Author: Rajin sharwar
Author URL: https://profiles.wordpress.org/rajinsharwar
*/

$stripe_library_path = plugin_dir_path(__FILE__) . 'stripe/init.php';
require_once($stripe_library_path);

\Stripe\Stripe::setApiKey('rk_test_5103FBz2wWyZphMKn66zoSXzOufDi1P0YMjv8enyjg0BgW1KdV9CA4J95eYTp2mn5nhGKnDPyV64QFO4RlRRnD5LL00qvuuplgD');

function fetch_stripe_invoices($customer_id) {
    // Retrieve the customer's invoices from Stripe
    $invoices = \Stripe\Invoice::all(['customer' => $customer_id]);

    return $invoices;
}

function custom_membership_section_content($atts, $content = null){
    // Access the current user's email
    $user_email = wp_get_current_user()->user_email;

    // Fetch data from Stripe for the user with the matching email
    $customer = fetch_stripe_customer_by_email($user_email);

    if ($customer) {
        // Retrieve the customer's invoices from Stripe
        $invoices = fetch_stripe_invoices($customer->id);

        $custom_content = '<div class="custom-section">';
        $custom_content .= '<h2>My Invoices</h2>';
        $custom_content .= '<style>.custom-table tr td { vertical-align: top; }</style>';
        $custom_content .= '<table class="custom-table">';
        $custom_content .= '<tr><th>Invoice ID</th><th>Client Name</th><th>Memo</th><th>Status</th><th>Invoice Date</th><th>Total Amount</th><th>Action</th></tr>';

        foreach ($invoices->data as $invoice) {
            $memo = $invoice->description;

            // Check if the memo is a numbered list
            if (preg_match('/\d+\.\s+/', $memo)) {
                // Replace each number and dot with a line break
                $memo = preg_replace('/\b(?!1\b)([0-9]|[1-9][0-9]|100)\.\s+/', "<br>$1&nbsp;", $memo);
            }

            $invoice_pdf_link = \Stripe\Invoice::retrieve($invoice->id)->invoice_pdf;

            $custom_content .= '<tr>';
            $custom_content .= '<td>' . $invoice->number . '</td>';
            $custom_content .= '<td>' . $invoice->customer_name . '</td>';
            $custom_content .= '<td>' . $memo . '</td>';
            $custom_content .= '<td>' . $invoice->status . '</td>';
            $custom_content .= '<td>' . date('Y-m-d', $invoice->created) . '</td>';
            $custom_content .= '<td>$' . number_format($invoice->total / 100, 2) . '</td>';
            $custom_content .= '<td><a href="' . $invoice_pdf_link . '" download><b>Download</b></a></td>';
            $custom_content .= '</tr>';
        }

        $custom_content .= '</table>';
        $custom_content .= '</div>';
    } else {
        // No customer found with the specified email
        $custom_content = '<h2>My Invoices</h2>';
        $custom_content .= '<div class="custom-section">No invoices found.</div>';
    }

    return $custom_content;
}

add_shortcode('custom_membership_section', 'custom_membership_section_content');


function fetch_stripe_customer_by_email($user_email) {
    // Retrieve all customers from Stripe
    $all_customers = \Stripe\Customer::all(['limit' => 100]);

    // Find the customer with the matching email
    foreach ($all_customers->data as $customer) {
        if ($customer->email === $user_email) {
            return $customer;
        }
    }

    return null; // Return null if no matching customer is found
}

?>