<?php
/*
Plugin Name: BTT Client Portal
Description: Display data in the user Dashboard from the Stripe API.
Version: 1.5
Author: Rajin sharwar
Author URL: https://profiles.wordpress.org/rajinsharwar
*/

if (is_plugin_active('gravityformsstripe/stripe.php')) {
    $stripe_library_path = WP_CONTENT_DIR . '/plugins/gravityformsstripe/includes/stripe/stripe-php/init.php';
} else {
    $stripe_library_path = plugin_dir_path(__FILE__) . 'stripe/init.php';
}

require_once($stripe_library_path);

// Initializing Stripe Class
\Stripe\Stripe::setApiKey('rk_test_5103FBz2wWyZphMKn66zoSXzOufDi1P0YMjv8enyjg0BgW1KdV9CA4J95eYTp2mn5nhGKnDPyV64QFO4RlRRnD5LL00qvuuplgD');

//Main Class
class InvoicesRender
{

    /**
     * 
     * Fetching Stripe Invoices
     * 
     */
    function fetch_stripe_invoices($customer_id)
    {
        // Retrieve the customer's invoices from Stripe
        $invoices = \Stripe\Invoice::all(['customer' => $customer_id]);

        return $invoices;
    }

    /**
     * Fetching Stripe Subscriptions
     */
    function fetch_stripe_subscriptions($customer_id)
    {
        // Retrieve the customer's subscriptions from Stripe
        $subscriptions = \Stripe\Subscription::all(['customer' => $customer_id]);

        return $subscriptions;
    }

    /**
     * Render invoices and subscriptions using the Shortcode: {custom_membership_section}
     */
    function custom_membership_section_content($atts, $content = null)
    {
        // Access the current user's email
        $user_email = wp_get_current_user()->user_email;

        // Fetch data from Stripe for the user with the matching email
        $customer = $this->fetch_stripe_customer_by_email($user_email);

        if ($customer) {
            // Retrieve the customer's invoices and subscriptions from Stripe
            $invoices = $this->fetch_stripe_invoices($customer->id);
            $subscriptions = $this->fetch_stripe_subscriptions($customer->id);

            $custom_content = '<section>';
            // Render invoices

            $custom_content .= '<div class="custom-section">';
            $custom_content .= '<h2>Invoices</h2>';
            $custom_content .= '<style>.custom-table tr td { vertical-align: top; }</style>';
            $custom_content .= '<table class="custom-table">';
            $custom_content .= '<tr><th>Invoice Date</th><th>Download PDFs</th><th>Description<th>Client Name</th><th>Memo</th><th>Status</th><th>Total Amount</th></tr>';


            foreach ($invoices->data as $invoice) {

                $memo = $invoice->description;
                $inctype = substr($invoice->id, 0, 3);
                $invtotal = $invoice->total;
                $billreason = $invoice->billing_reason;




                /* if this is an invoice and not a payment intent etc. check to make sure it's over 0 - or the intial invoice,  else don't display */
                if ($inctype === 'in_' && ($invtotal > 0 || $bill_reason = 'subscription_create')) {

                    // Check if the memo is a numbered list
                    if (preg_match('/\d+\.\s+/', $memo)) {
                        // Replace each number and dot with a line break
                        $memo = preg_replace('/\b(?!1\b)([0-9]|[1-9][0-9]|100)\.\s+/', "<br>$1&nbsp;", $memo);
                    }
                    //check to see if this is a subscription update, or a one-off invoice 
                    if ($billreason === 'subscription_update') {
                        $billreason = 'Subscription Payment';
                    } elseif ($billreason === 'subscription_create') {
                        $billreason = 'Set Up Fee';
                        $invtotal = $invoice->amount_paid;
                    } elseif ($billreason === 'manual') {
                        $billreason = 'Other';
                    }
                    ;


                    $invoice_pdf_link = \Stripe\Invoice::retrieve($invoice->id)->invoice_pdf;
                    $invoice_pdf_link = \Stripe\Invoice::retrieve($invoice->id)->invoice_pdf;
                    $invoice_payment_link = $invoice->hosted_invoice_url;

                    $custom_content .= "<tr>";
                    $custom_content .= '<td>' . date('Y-m-d', $invoice->created) . '</td>';
                    $custom_content .= '<td><a href="' . $invoice_payment_link . '" target="_blank" style="text-decoration: underline;">' . $invoice->number . '</a></td>';
                    $custom_content .= '<td>' . $billreason . '</td>';
                    $custom_content .= '<td>' . $invoice->customer_name . '</td>';
                    $custom_content .= '<td>' . $memo . '</td>';
                    $custom_content .= '<td>' . $invoice->status . '</td>';

                    $custom_content .= '<td>$' . number_format($invtotal / 100, 2) . '</td>';
                    //  $custom_content .= '<td><a href="' . $invoice_pdf_link . '" download><b>Download<br>Invoice</b></a></td>';
                    // $custom_content .= '<td><a href="' . $receipt_pdf_link . '" download><b>Download<br>Receipt</b></a></td>';
                    $custom_content .= '</tr>';
                }

            }
            $custom_content .= '</table>';
            $custom_content .= '</div>';
            $custom_content .= '<hr>';

            // Render subscriptions

            $custom_content .= '<h2>Subscriptions</h2>';
            $custom_content .= '<style>.custom-table tr td { vertical-align: top; }</style>';
            $custom_content .= '<table class="custom-table">';
            $custom_content .= '<tr><th>Plan</th><th>Recurring Amount</th><th>Status</th><th>Start Date</th><th>End Date</th></tr>';

            foreach ($subscriptions->data as $subscription) {
                $custom_content .= '<tr>';
                //  var_dump($subscription->plan);
                $update_billing_info_link = '/membership-account/membership-billing/';
                $change_plan_link = '/membership-account/membership-levels/';

                if ($subscription->plan->amount_decimal === "6200") {
                    $plan_label = $subscription->description;
                    $cancel_subscription_link = '/membership-account/membership-cancel/?levelstocancel=2';
                } elseif ($subscription->plan->amount_decimal === "6550") {
                    $plan_label = $subscription->description;
                    $cancel_subscription_link = '/membership-account/membership-cancel/?levelstocancel=3';
                } elseif ($subscription->plan->amount_decimal === "12400") {
                    $plan_label = $subscription->description;
                    $cancel_subscription_link = '/membership-account/membership-cancel/?levelstocancel=4';
                } elseif ($subscription->plan->amount_decimal === "18800") {
                    $plan_label = $subscription->description;
                    $cancel_subscription_link = '/membership-account/membership-cancel/?levelstocancel=5';
                } elseif ($subscription->plan->amount_decimal === "66960") {
                    $plan_label = $subscription->description;
                    $cancel_subscription_link = '/membership-account/membership-cancel/?levelstocancel=6';
                } elseif ($subscription->plan->amount_decimal === "70740") {
                    $plan_label = $subscription->description;
                    $cancel_subscription_link = '/membership-account/membership-cancel/?levelstocancel=7';
                } elseif ($subscription->plan->amount_decimal === "133920") {
                    $plan_label = $subscription->description;
                    $cancel_subscription_link = '/membership-account/membership-cancel/?levelstocancel=8';
                } elseif ($subscription->plan->amount_decimal === "203040") {
                    $plan_label = $subscription->description;
                    $cancel_subscription_link = '/membership-account/membership-cancel/?levelstocancel=9';
                }

                // Plan details
                $custom_content .= '<td>' . $plan_label . '</br>';
                // Display buttons for the specified plan
                $custom_content .= '<a href="' . $update_billing_info_link . '">Update Billing Info</a> | ';
                $custom_content .= '<a href="' . $change_plan_link . '">Change</a> | ';
                $custom_content .= '<a href="' . $cancel_subscription_link . '">Cancel</a></td>';

                // Recurring amount display
                $recurring_amount = $subscription->plan->amount / 100;
                $custom_content .= '<td>$ ' . number_format($recurring_amount, 2) . ' per Month.</td>';

                // Subscription status and dates
                $custom_content .= '<td>' . $subscription->status . '</td>';
                $custom_content .= '<td>' . date('Y-m-d', $subscription->current_period_start) . '</td>';
                $custom_content .= '<td>' . date('Y-m-d', $subscription->current_period_end) . '</td>';

                $custom_content .= '</td>';

                $custom_content .= '</tr>';
            }

            $custom_content .= '</table>';
            $custom_content .= '<div class="pmpro_actionlinks"><a id="pmpro_actionlink-levels" href="/membership-account/membership-levels/" role="link">View all Subscription Options</a></div>';
            $custom_content .= '</div>';
            $custom_content .= '</section>';

        } else {
            // No customer found with the specified email
            $custom_content = '<section>';
            $custom_content .= '<h2>Invoices and Subscriptions</h2>';
            $custom_content .= '<div class="custom-section">No invoices and subscriptions found.</div>';
            $custom_content .= '</section>';
        }

        return $custom_content;
    }

    /**
     * Fetching the Customer object of the current user using his email.
     */
    function fetch_stripe_customer_by_email($user_email)
    {
        // Retrieve all customers from Stripe
        $all_customers = \Stripe\Customer::all();

        // Find the customer with the matching email
        foreach ($all_customers->data as $customer) {
            // Check standard email field
            if ($customer->email === $user_email) {
                return $customer;
            }

            // Check additional email in metadata
            $metadata = $customer->metadata ?? [];
            if (isset($metadata['other_stripe_admin_email']) && $metadata['other_stripe_admin_email'] === $user_email) {
                return $customer;
            }
        }

        return null; // Return null if no matching customer is found.
    }
}

if (class_exists('InvoicesRender')) {
    $InvoicesRender = new InvoicesRender(); //Initialize the class.
}

//Adding the Shortcode.
add_shortcode('custom_membership_section', array($InvoicesRender, 'custom_membership_section_content'));
