<?php

$version['name'] = 'Cloudflare';
$version['api_version'] = '4.0';
$version['date'] = '2019-06-01';
$version['version'] = '1.0';

// Information for customer (will be showed at DNS-management-show-page)
$version['dev_logo'] = 'https://creacoon.nl/images/logo_100h.png'; // URL to your logo
$version['dev_author'] = 'Creacoon'; // Your companyname
$version['dev_website'] = 'https://creacoon.nl'; // URL website
$version['dev_email'] = 'support@creacoon.nl'; // Your e-mailaddress for support questions
//$version['dev_phone']		= ''; // Your phone number for support questions

$version['dns_management_support'] = true;

// Does this integration support DNS templates?
$version['dns_templates_support'] = false;
// Does this integration support DNS records?
$version['dns_records_support'] = true;
// this means TTL is for all dns records the same, you cannot assign a TTL per dns record
$version['dns_single_ttl'] = false;
