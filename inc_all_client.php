<?php 

include("config.php");
include_once("functions.php");
include("check_login.php");
include("header.php");
include("top_nav.php");

?>

<?php 

if(isset($_GET['client_id'])){
  $client_id = intval($_GET['client_id']);

  $sql = mysqli_query($mysqli,"UPDATE clients SET client_accessed_at = NOW() WHERE client_id = $client_id AND company_id = $session_company_id");

  $sql = mysqli_query($mysqli,"SELECT * FROM clients
    LEFT JOIN locations ON primary_location = location_id AND location_archived_at IS NULL
    LEFT JOIN contacts ON primary_contact = contact_id AND contact_archived_at IS NULL
    WHERE client_id = $client_id 
    AND clients.company_id = $session_company_id");

  if(mysqli_num_rows($sql) == 0){
    include("header.php");
    echo "<center><h1 class='text-secondary mt-5'>Nothing to see here</h1></center>";
  }else{

    $row = mysqli_fetch_array($sql);
    $client_name = $row['client_name'];
    $client_type = $row['client_type'];
    $client_website = $row['client_website'];
    $client_referral = $row['client_referral'];
    $client_currency_code = $row['client_currency_code'];
    $client_net_terms = $row['client_net_terms'];
    if($client_net_terms == 0){
      $client_net_terms = $config_default_net_terms;
    }
    $client_notes = $row['client_notes'];
    $client_created_at = $row['client_created_at'];
    $primary_contact = $row['primary_contact'];
    $primary_location = $row['primary_location'];
    $contact_id = $row['contact_id'];
    $contact_name = $row['contact_name'];
    $contact_title = $row['contact_title'];
    $contact_email = $row['contact_email'];
    $contact_phone = $row['contact_phone'];
    $contact_extension = $row['contact_extension'];
    $contact_mobile = $row['contact_mobile'];
    $location_id = $row['location_id'];
    $location_name = $row['location_name'];
    $location_address = $row['location_address'];
    $location_city = $row['location_city'];
    $location_state = $row['location_state'];
    $location_zip = $row['location_zip'];
    $location_country = $row['location_country'];
    $location_phone = $row['location_phone'];

    //Client Tags

    $client_tag_name_display_array = array();
    $client_tag_id_array = array();
    $sql_client_tags = mysqli_query($mysqli,"SELECT * FROM client_tags LEFT JOIN tags ON client_tags.tag_id = tags.tag_id WHERE client_tags.client_id = $client_id");
    while($row = mysqli_fetch_array($sql_client_tags)){

      $client_tag_id = $row['tag_id'];
      $client_tag_name = $row['tag_name'];
      $client_tag_color = $row['tag_color'];
      $client_tag_icon = $row['tag_icon'];
      if(empty($client_tag_icon)){
        $client_tag_icon = "tag";
      }
    
      $client_tag_id_array[] = $client_tag_id;
      $client_tag_name_display_array[] = "$client_tag_name ";
    }
    $client_tags_display = "<i class='fa fa-fw fa-tag text-secondary ml-1 mr-2 mb-2'></i> " . implode('', $client_tag_name_display_array);

    //Add up all the payments for the invoice and get the total amount paid to the invoice
    $sql_invoice_amounts = mysqli_query($mysqli,"SELECT SUM(invoice_amount) AS invoice_amounts FROM invoices WHERE invoice_client_id = $client_id AND invoice_status NOT LIKE 'Draft' AND invoice_status NOT LIKE 'Cancelled'");
    $row = mysqli_fetch_array($sql_invoice_amounts);

    $invoice_amounts = $row['invoice_amounts'];

    $sql_amount_paid = mysqli_query($mysqli,"SELECT SUM(payment_amount) AS amount_paid FROM payments, invoices WHERE payment_invoice_id = invoice_id AND invoice_client_id = $client_id");
    $row = mysqli_fetch_array($sql_amount_paid);
    
    $amount_paid = $row['amount_paid'];

    $balance = $invoice_amounts - $amount_paid;

    //Badge Counts

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('contact_id') AS num FROM contacts WHERE contact_archived_at IS NULL AND contact_client_id = $client_id"));
    $num_contacts = $row['num'];
    
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('location_id') AS num FROM locations WHERE location_archived_at IS NULL AND location_client_id = $client_id"));
    $num_locations = $row['num'];
    
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('asset_id') AS num FROM assets WHERE asset_archived_at IS NULL AND asset_client_id = $client_id"));
    $num_assets = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('ticket_id') AS num FROM tickets WHERE ticket_archived_at IS NULL AND ticket_status != 'Closed' AND ticket_client_id = $client_id"));
    $num_active_tickets = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('ticket_id') AS num FROM tickets WHERE ticket_archived_at IS NULL AND ticket_status = 'Closed' AND ticket_client_id = $client_id"));
    $num_closed_tickets = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('service_id') AS num FROM services WHERE service_client_id = $client_id"));
    $num_services = $row['num'];
    
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('vendor_id') AS num FROM vendors WHERE vendor_archived_at IS NULL AND vendor_client_id = $client_id"));
    $num_vendors = $row['num'];
    
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('login_id') AS num FROM logins WHERE login_archived_at IS NULL AND login_client_id = $client_id"));
    $num_logins = $row['num'];
    
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('network_id') AS num FROM networks WHERE network_archived_at IS NULL AND network_client_id = $client_id"));
    $num_networks = $row['num'];
    
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('domain_id') AS num FROM domains WHERE domain_archived_at IS NULL AND domain_client_id = $client_id"));
    $num_domains = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('certificate_id') AS num FROM certificates WHERE certificate_archived_at IS NULL AND certificate_client_id = $client_id"));
    $num_certificates = $row['num'];
    
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('software_id') AS num FROM software WHERE software_archived_at IS NULL AND software_client_id = $client_id"));
    $num_software = $row['num'];
    
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('invoice_id') AS num FROM invoices WHERE (invoice_status = 'Sent' OR invoice_status = 'Viewed' OR invoice_status = 'Partial') AND invoice_archived_at IS NULL AND invoice_client_id = $client_id"));
    $num_invoices_open = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('invoice_id') AS num FROM invoices WHERE invoice_status = 'Draft' AND invoice_archived_at IS NULL AND invoice_client_id = $client_id"));
    $num_invoices_draft = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('invoice_id') AS num FROM invoices WHERE invoice_status = 'Sent' AND invoice_archived_at IS NULL AND invoice_client_id = $client_id"));
    $num_invoices_sent = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('invoice_id') AS num FROM invoices WHERE invoice_status = 'Viewed' AND invoice_archived_at IS NULL AND invoice_client_id = $client_id"));
    $num_invoices_viewed = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('invoice_id') AS num FROM invoices WHERE invoice_status = 'Partial' AND invoice_archived_at IS NULL AND invoice_client_id = $client_id"));
    $num_invoices_partial = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('invoice_id') AS num FROM invoices WHERE invoice_status = 'Paid' AND invoice_archived_at IS NULL AND invoice_client_id = $client_id"));
    $num_invoices_paid = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('invoice_id') AS num FROM invoices WHERE invoice_archived_at IS NULL AND invoice_client_id = $client_id"));
    $num_invoices = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('quote_id') AS num FROM quotes WHERE quote_archived_at IS NULL AND quote_client_id = $client_id"));
    $num_quotes = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('recurring_id') AS num FROM recurring WHERE recurring_archived_at IS NULL AND recurring_client_id = $client_id"));
    $num_recurring = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('payment_id') AS num FROM payments, invoices WHERE payment_invoice_id = invoice_id AND invoice_client_id = $client_id"));
    $num_payments = $row['num'];
    
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('file_id') AS num FROM files WHERE file_archived_at IS NULL AND file_client_id = $client_id"));
    $num_files = $row['num'];
    
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('document_id') AS num FROM documents WHERE document_archived_at IS NULL AND document_client_id = $client_id"));
    $num_documents = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('event_id') AS num FROM events WHERE event_client_id = $client_id"));
    $num_events = $row['num'];

    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('trip_id') AS num FROM trips WHERE trip_archived_at IS NULL AND trip_client_id = $client_id"));
    $num_trips = $row['num'];

    // Expiring Items

    // Get Domains Expiring within 30 Days
    $row = mysqli_fetch_assoc(mysqli_query($mysqli,"SELECT COUNT('domain_id') AS num FROM domains
      WHERE domain_client_id = $client_id
      AND domain_expire != '0000-00-00'
      AND domain_archived_at IS NULL
      AND domain_expire < CURRENT_DATE + INTERVAL 30 DAY
      AND company_id = $session_company_id"
    ));
    $num_domains_expiring = $row['num'];

    // Get Asset Warranties Expiring
    $sql_asset_warranties_expiring = mysqli_query($mysqli,"SELECT * FROM assets
      WHERE asset_client_id = $client_id
      AND asset_warranty_expire != '0000-00-00'
      AND asset_archived_at IS NULL  
      AND asset_warranty_expire < CURRENT_DATE + INTERVAL 90 DAY
      AND company_id = $session_company_id ORDER BY asset_warranty_expire DESC"
    );

    // Get Assets Retiring
    $sql_asset_retire = mysqli_query($mysqli,"SELECT * FROM assets
      WHERE asset_client_id = $client_id
      AND asset_install_date != '0000-00-00'
      AND asset_archived_at IS NULL 
      AND asset_install_date + INTERVAL 7 YEAR < CURRENT_DATE + INTERVAL 90 DAY
      AND company_id = $session_company_id ORDER BY asset_install_date DESC"
    );

    // Get Stale Tickets
    $sql_tickets_stale = mysqli_query($mysqli,"SELECT * FROM tickets
      WHERE ticket_client_id = $client_id
      AND ticket_created_at < CURRENT_DATE - INTERVAL 14 DAY
      AND ticket_status != 'Closed'
      AND company_id = $session_company_id ORDER BY ticket_created_at DESC"
    );

  }
}

?>

<?php

include("client_side_nav.php");
include("inc_wrapper.php");
include("inc_alert_feedback.php");
include("inc_client_top_head.php");
include("pagination_head.php");

?>