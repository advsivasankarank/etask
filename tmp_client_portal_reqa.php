<?php
declare(strict_types=1);

function requestPage(string $method, string $url, array &$cookies, array $fields = [], ?string $referer = null, string $userAgent = 'Mozilla/5.0 QA Portal Auditor'): array
{
    $headers = [
        'User-Agent: ' . $userAgent,
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    ];

    if ($referer !== null) {
        $headers[] = 'Referer: ' . $referer;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_COOKIE => buildCookieHeader($cookies),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    }

    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);

    return splitResponse($response ?: '', $info, $cookies, $error);
}

function buildCookieHeader(array $cookies): string
{
    $pairs = [];
    foreach ($cookies as $name => $value) {
        $pairs[] = $name . '=' . $value;
    }

    return implode('; ', $pairs);
}

function splitResponse(string $raw, array $info, array &$cookies, string $error = ''): array
{
    $headerSize = (int) ($info['header_size'] ?? 0);
    $headerText = substr($raw, 0, $headerSize);
    $body = substr($raw, $headerSize);

    foreach (preg_split("/\r\n|\n|\r/", $headerText) as $line) {
        if (stripos($line, 'Set-Cookie:') === 0) {
            $cookiePart = trim(substr($line, 11));
            $segments = explode(';', $cookiePart);
            $nameValue = explode('=', trim($segments[0]), 2);
            if (count($nameValue) === 2) {
                $cookies[$nameValue[0]] = $nameValue[1];
            }
        }
    }

    return [
        'status' => (int) ($info['http_code'] ?? 0),
        'headers' => $headerText,
        'body' => $body,
        'location' => extractHeader($headerText, 'Location'),
        'content_type' => (string) ($info['content_type'] ?? ''),
        'curl_error' => $error,
        'bytes' => strlen($body),
    ];
}

function extractHeader(string $headers, string $name): ?string
{
    foreach (preg_split("/\r\n|\n|\r/", $headers) as $line) {
        if (stripos($line, $name . ':') === 0) {
            return trim(substr($line, strlen($name) + 1));
        }
    }

    return null;
}

function csrfToken(string $html): ?string
{
    if (preg_match('/name="_token"\s+value="([^"]+)"/i', $html, $matches) === 1) {
        return html_entity_decode($matches[1], ENT_QUOTES);
    }

    return null;
}

function contains(string $html, string $needle): bool
{
    return stripos($html, $needle) !== false;
}

function hrefs(string $html): array
{
    preg_match_all('/href="([^"]+)"/i', $html, $matches);
    return array_values(array_unique($matches[1] ?? []));
}

function portalRouteMatches(array $hrefs, string $pattern): ?string
{
    foreach ($hrefs as $href) {
        if (preg_match($pattern, $href) === 1) {
            return $href;
        }
    }

    return null;
}

function absoluteUrl(string $base, string $href): string
{
    if (preg_match('#^https?://#i', $href) === 1) {
        return $href;
    }

    return rtrim($base, '/') . '/' . ltrim($href, '/');
}

$base = 'http://localhost/etask/public';
$desktopUa = 'Mozilla/5.0 QA Portal Auditor Desktop';
$mobileUa = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1 QA Portal Auditor Mobile';
$username = 'qa_portal_client';
$password = 'QaPortal@2026';
$cookies = [];
$results = [];

$loginPage = requestPage('GET', $base . '/login?audience=portal', $cookies, [], null, $desktopUa);
$token = csrfToken($loginPage['body']);
$loginPost = requestPage('POST', $base . '/login', $cookies, [
    '_token' => $token,
    'username' => $username,
    'password' => $password,
    'audience' => 'portal',
], $base . '/login?audience=portal', $desktopUa);

$results['login'] = [
    'page_status' => $loginPage['status'],
    'post_status' => $loginPost['status'],
    'location' => $loginPost['location'],
    'portal_login_copy' => contains($loginPage['body'], 'Portal User') || contains($loginPage['body'], 'Client Login'),
    'csrf_found' => $token !== null,
];

$dashboardUrl = $base . '/client-portal/account';
$dashboard = requestPage('GET', $dashboardUrl, $cookies, [], $base . '/login?audience=portal', $desktopUa);
$dashboardLinks = hrefs($dashboard['body']);
$results['dashboard'] = [
    'status' => $dashboard['status'],
    'title_present' => contains($dashboard['body'], 'Client Workspace'),
    'next_action' => contains($dashboard['body'], 'Next Action Required'),
    'billing_overview' => contains($dashboard['body'], 'Billing Overview'),
    'notifications' => contains($dashboard['body'], 'Notifications'),
    'profile_support' => contains($dashboard['body'], 'Profile and Support'),
    'support_link' => contains($dashboard['body'], '/client-portal/support'),
    'open_invoice_buttons' => substr_count(strtolower($dashboard['body']), 'open invoice'),
    'broken_php_warnings' => contains($dashboard['body'], 'Warning:') || contains($dashboard['body'], 'Fatal error'),
    'link_count' => count($dashboardLinks),
];

$servicesUrl = absoluteUrl($base, portalRouteMatches($dashboardLinks, '#/service-orders$#') ?? '/service-orders');
$services = requestPage('GET', $servicesUrl, $cookies, [], $dashboardUrl, $desktopUa);
$serviceLinks = hrefs($services['body']);
$serviceTrackingUrl = absoluteUrl($base, portalRouteMatches($serviceLinks, '#/service-orders/show\\?id=\\d+#') ?? '/service-orders');
$serviceTracking = requestPage('GET', $serviceTrackingUrl, $cookies, [], $servicesUrl, $desktopUa);
$serviceTrackingLinks = hrefs($serviceTracking['body']);

$documentCentreUrl = absoluteUrl($base, portalRouteMatches($dashboardLinks, '#/client-portal/documents$#') ?? '/client-portal/documents');
$documentCentre = requestPage('GET', $documentCentreUrl, $cookies, [], $dashboardUrl, $desktopUa);
$documentLinks = hrefs($documentCentre['body']);
$supportUrl = absoluteUrl($base, portalRouteMatches($dashboardLinks, '#/client-portal/support$#') ?? '/client-portal/support');
$support = requestPage('GET', $supportUrl, $cookies, [], $dashboardUrl, $desktopUa);

$invoiceUrl = absoluteUrl($base, portalRouteMatches($dashboardLinks, '#/billing/invoice\\?id=\\d+#') ?? '/billing/invoice');
$invoice = requestPage('GET', $invoiceUrl, $cookies, [], $dashboardUrl, $desktopUa);

$receiptUrl = absoluteUrl($base, portalRouteMatches($dashboardLinks, '#/billing/receipt\\?id=\\d+#') ?? '/billing/receipt');
$receipt = requestPage('GET', $receiptUrl, $cookies, [], $dashboardUrl, $desktopUa);

$results['my_services'] = [
    'status' => $services['status'],
    'has_my_services' => contains($services['body'], 'My Services'),
    'has_search' => contains($services['body'], 'Search Services'),
    'has_cards' => contains($services['body'], 'Track Service'),
];

$results['service_tracking'] = [
    'status' => $serviceTracking['status'],
    'has_progress_tracker' => contains($serviceTracking['body'], 'Progress Tracker'),
    'has_status_details' => contains($serviceTracking['body'], 'Status Details'),
    'has_pending_from_you' => contains($serviceTracking['body'], 'Pending From You'),
    'has_service_documents' => contains($serviceTracking['body'], 'Service Documents'),
    'has_recent_updates' => contains($serviceTracking['body'], 'Recent Updates'),
    'has_trust_copy' => contains($serviceTracking['body'], 'without exposing internal processing details'),
    'uses_internal_terms' => contains($serviceTracking['body'], 'PSO') || contains($serviceTracking['body'], 'Milestone') || contains($serviceTracking['body'], 'CRM Review'),
    'has_500_text' => contains($serviceTracking['body'], 'Something Went Wrong'),
];

$results['document_centre'] = [
    'status' => $documentCentre['status'],
    'title' => contains($documentCentre['body'], 'Document Centre'),
    'requested_from_you' => contains($documentCentre['body'], 'Requested From You'),
    'uploaded_by_you' => contains($documentCentre['body'], 'Uploaded By You'),
    'generated_for_you' => contains($documentCentre['body'], 'Generated For You'),
    'download_buttons' => substr_count(strtolower($documentCentre['body']), 'download'),
];

$docShowHref = portalRouteMatches(array_merge($serviceTrackingLinks, $documentLinks), '#/documents/show\\?id=\\d+#');
$downloadHref = portalRouteMatches(array_merge($serviceTrackingLinks, $documentLinks), '#/documents/\\d+/download#');
$documentShow = $docShowHref ? requestPage('GET', absoluteUrl($base, $docShowHref), $cookies, [], $serviceTrackingUrl, $desktopUa) : null;
$documentDownload = $downloadHref ? requestPage('GET', absoluteUrl($base, $downloadHref), $cookies, [], $docShowHref ? absoluteUrl($base, $docShowHref) : $documentCentreUrl, $desktopUa) : null;

$results['documents'] = [
    'show_status' => $documentShow['status'] ?? null,
    'download_status' => $documentDownload['status'] ?? null,
    'download_content_type' => $documentDownload['content_type'] ?? null,
    'download_non_html' => isset($documentDownload['content_type']) ? !str_contains(strtolower((string) $documentDownload['content_type']), 'text/html') : null,
];

$results['support'] = [
    'status' => $support['status'],
    'has_need_help' => contains($support['body'], 'Need Help?'),
    'has_raise_query' => contains($support['body'], 'Raise Query'),
    'has_request_callback' => contains($support['body'], 'Request Callback'),
    'has_contact_rm' => contains($support['body'], 'Contact Relationship Manager'),
    'has_faq' => contains($support['body'], 'Frequently Asked Topics'),
    'has_contact_info' => contains($support['body'], 'Contact Information'),
];

$results['billing'] = [
    'invoice_link_detected' => portalRouteMatches($dashboardLinks, '#/billing/invoice\\?id=\\d+#'),
    'invoice_status' => $invoice['status'],
    'invoice_visible' => contains($invoice['body'], 'Invoice'),
    'invoice_amounts' => contains($invoice['body'], 'Net Payable') || contains($invoice['body'], 'Outstanding'),
    'receipt_link_detected' => portalRouteMatches($dashboardLinks, '#/billing/receipt\\?id=\\d+#'),
    'receipt_status' => $receipt['status'],
    'receipt_visible' => contains($receipt['body'], 'Receipt'),
    'payment_cta_visible' => contains($dashboard['body'], 'Submit Payment'),
];

$results['notifications'] = [
    'dashboard_notification_area' => contains($dashboard['body'], 'Notifications'),
    'attention_copy' => contains($dashboard['body'], 'require attention') || contains($dashboard['body'], 'attention'),
];

$results['profile'] = [
    'dashboard_profile_area' => contains($dashboard['body'], 'Profile and Support'),
    'support_copy' => contains($dashboard['body'], 'support') || contains($dashboard['body'], 'contact'),
];

$criticalLinks = [
    $dashboardUrl,
    $servicesUrl,
    $serviceTrackingUrl,
    $documentCentreUrl,
    $supportUrl,
    $invoiceUrl,
    $receiptUrl,
];

$linkStatuses = [];
foreach ($criticalLinks as $url) {
    $response = requestPage('GET', $url, $cookies, [], $dashboardUrl, $desktopUa);
    $linkStatuses[$url] = $response['status'];
}
$results['route_statuses'] = $linkStatuses;

$mobileDashboard = requestPage('GET', $dashboardUrl, $cookies, [], $dashboardUrl, $mobileUa);
$mobileService = requestPage('GET', $serviceTrackingUrl, $cookies, [], $servicesUrl, $mobileUa);
$mobileDocumentCentre = requestPage('GET', $documentCentreUrl, $cookies, [], $dashboardUrl, $mobileUa);

$results['mobile'] = [
    'dashboard_status' => $mobileDashboard['status'],
    'service_tracking_status' => $mobileService['status'],
    'document_centre_status' => $mobileDocumentCentre['status'],
    'dashboard_contains_workspace' => contains($mobileDashboard['body'], 'Client Workspace'),
    'service_contains_progress' => contains($mobileService['body'], 'Progress Tracker'),
    'document_contains_title' => contains($mobileDocumentCentre['body'], 'Document Centre'),
];

echo json_encode($results, JSON_PRETTY_PRINT);
