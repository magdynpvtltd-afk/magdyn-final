<?php
/**
 * MagDyn — Old Inventory HTTP API client helper.
 *
 * Calls api_export_assets.php on the old inventory server and returns
 * the decoded JSON response as a PHP array.
 *
 * Usage:
 *   $data  = old_inventory_api('count');
 *   $total = $data['count'];
 *
 *   $data   = old_inventory_api('assets', ['offset' => 0, 'limit' => 100]);
 *   $assets = $data['assets'];
 *
 * Throws RuntimeException on network error, bad JSON, or API-level error.
 */
function old_inventory_api(string $action, array $params = []): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config/old_inventory_api.php';
    }

    $params['action'] = $action;
    $params['token']  = $cfg['token'];

    $url = rtrim($cfg['url'], '/') . '?' . http_build_query($params);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => $cfg['timeout'],
            'header'  => "Accept: application/json\r\nConnection: close\r\n",
            'ignore_errors' => true,   // capture 4xx/5xx bodies too
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);

    if ($raw === false) {
        throw new RuntimeException(
            'Could not reach old inventory API at ' . $cfg['url'] .
            '. Check that the server is reachable and the file is deployed.'
        );
    }

    $data = json_decode($raw, true);

    if ($data === null) {
        throw new RuntimeException(
            'Old inventory API returned invalid JSON. ' .
            'Response (first 200 chars): ' . substr($raw, 0, 200)
        );
    }

    if (isset($data['error'])) {
        throw new RuntimeException('Old inventory API error: ' . $data['error']);
    }

    return $data;
}

/**
 * MagDyn — Old Inventory vendor/user export API client.
 *
 * Same contract as old_inventory_api() but targets the dedicated
 * api_export_vendors.php endpoint (config key 'vendors_url'), used to
 * import vendors, contacts, addresses and application users.
 *
 * Usage:
 *   $data    = old_inventory_vendor_api('vendor_count');
 *   $total   = $data['count'];
 *   $data    = old_inventory_vendor_api('vendors', ['offset' => 0, 'limit' => 100]);
 *   $vendors = $data['vendors'];
 *
 * Throws RuntimeException on network error, bad JSON, or API-level error.
 */
function old_inventory_vendor_api(string $action, array $params = []): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config/old_inventory_api.php';
    }

    if (empty($cfg['vendors_url'])) {
        throw new RuntimeException(
            "vendors_url not set in config/old_inventory_api.php."
        );
    }

    $params['action'] = $action;
    $params['token']  = $cfg['token'];

    $url = rtrim($cfg['vendors_url'], '/') . '?' . http_build_query($params);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => $cfg['timeout'],
            'header'  => "Accept: application/json\r\nConnection: close\r\n",
            'ignore_errors' => true,   // capture 4xx/5xx bodies too
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);

    if ($raw === false) {
        throw new RuntimeException(
            'Could not reach old inventory vendor API at ' . $cfg['vendors_url'] .
            '. Check that the server is reachable and api_export_vendors.php is deployed.'
        );
    }

    $data = json_decode($raw, true);

    if ($data === null) {
        throw new RuntimeException(
            'Old inventory vendor API returned invalid JSON. ' .
            'Response (first 200 chars): ' . substr($raw, 0, 200)
        );
    }

    if (isset($data['error'])) {
        throw new RuntimeException('Old inventory vendor API error: ' . $data['error']);
    }

    return $data;
}

/**
 * MagDyn — Old Inventory running-notes export API client.
 *
 * Same contract as old_inventory_api() but targets api_export_notes.php
 * (config key 'notes_url'), used to import legacy inv_notes + their
 * notes_attachments metadata.
 *
 * Usage:
 *   $data  = old_inventory_notes_api('notes_count');
 *   $total = $data['count'];
 *   $data  = old_inventory_notes_api('all_notes_json', ['offset' => 0, 'limit' => 500]);
 *   $notes = $data['notes'];
 *
 * Throws RuntimeException on network error, bad JSON, or API-level error.
 */
function old_inventory_notes_api(string $action, array $params = []): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config/old_inventory_api.php';
    }

    if (empty($cfg['notes_url'])) {
        throw new RuntimeException(
            "notes_url not set in config/old_inventory_api.php."
        );
    }

    $params['action'] = $action;
    $params['token']  = $cfg['token'];

    $url = rtrim($cfg['notes_url'], '/') . '?' . http_build_query($params);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => $cfg['timeout'],
            'header'  => "Accept: application/json\r\nConnection: close\r\n",
            'ignore_errors' => true,   // capture 4xx/5xx bodies too
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);

    if ($raw === false) {
        throw new RuntimeException(
            'Could not reach old inventory notes API at ' . $cfg['notes_url'] .
            '. Check that the server is reachable and api_export_notes.php is deployed.'
        );
    }

    $data = json_decode($raw, true);

    if ($data === null) {
        throw new RuntimeException(
            'Old inventory notes API returned invalid JSON. ' .
            'Response (first 200 chars): ' . substr($raw, 0, 200)
        );
    }

    if (isset($data['error'])) {
        throw new RuntimeException('Old inventory notes API error: ' . $data['error']);
    }

    return $data;
}

/**
 * MagDyn — Old Inventory invoice export API client.
 *
 * Same contract as old_inventory_api() but targets api_export_invoices.php
 * (config key 'invoices_url'), used to import the legacy purchase-invoice
 * tables approveinv (pending) + recp_inv (approved).
 *
 * Usage:
 *   $data  = old_inventory_invoices_api('invoice_count');
 *   $total = $data['count'];
 *   $data  = old_inventory_invoices_api('invoices_json', ['src' => 'recp_inv', 'offset' => 0, 'limit' => 500]);
 *   $rows  = $data['rows'];
 *
 * Throws RuntimeException on network error, bad JSON, or API-level error.
 */
function old_inventory_invoices_api(string $action, array $params = []): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config/old_inventory_api.php';
    }

    if (empty($cfg['invoices_url'])) {
        throw new RuntimeException(
            "invoices_url not set in config/old_inventory_api.php."
        );
    }

    $params['action'] = $action;
    $params['token']  = $cfg['token'];

    $url = rtrim($cfg['invoices_url'], '/') . '?' . http_build_query($params);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => $cfg['timeout'],
            'header'  => "Accept: application/json\r\nConnection: close\r\n",
            'ignore_errors' => true,   // capture 4xx/5xx bodies too
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);

    if ($raw === false) {
        throw new RuntimeException(
            'Could not reach old inventory invoices API at ' . $cfg['invoices_url'] .
            '. Check that the server is reachable and api_export_invoices.php is deployed.'
        );
    }

    $data = json_decode($raw, true);

    if ($data === null) {
        throw new RuntimeException(
            'Old inventory invoices API returned invalid JSON. ' .
            'Response (first 200 chars): ' . substr($raw, 0, 200)
        );
    }

    if (isset($data['error'])) {
        throw new RuntimeException('Old inventory invoices API error: ' . $data['error']);
    }

    return $data;
}

/**
 * MagDyn — Old Inventory inspection export API client.
 *
 * Same contract as old_inventory_api() but targets api_export_inspections.php
 * (config key 'inspections_url'), used to import the legacy `inspection`
 * table into MagDyn inspection templates (one template per product pid).
 *
 * Usage:
 *   $data  = old_inventory_inspections_api('inspection_count');
 *   $total = $data['count'];
 *   $data  = old_inventory_inspections_api('inspections_json', ['offset' => 0, 'limit' => 500]);
 *   $rows  = $data['rows'];
 *
 * Throws RuntimeException on network error, bad JSON, or API-level error.
 */
function old_inventory_inspections_api(string $action, array $params = []): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config/old_inventory_api.php';
    }

    if (empty($cfg['inspections_url'])) {
        throw new RuntimeException(
            "inspections_url not set in config/old_inventory_api.php."
        );
    }

    $params['action'] = $action;
    $params['token']  = $cfg['token'];

    $url = rtrim($cfg['inspections_url'], '/') . '?' . http_build_query($params);

    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => $cfg['timeout'],
            'header'  => "Accept: application/json\r\nConnection: close\r\n",
            'ignore_errors' => true,   // capture 4xx/5xx bodies too
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);

    if ($raw === false) {
        throw new RuntimeException(
            'Could not reach old inventory inspections API at ' . $cfg['inspections_url'] .
            '. Check that the server is reachable and api_export_inspections.php is deployed.'
        );
    }

    $data = json_decode($raw, true);

    if ($data === null) {
        throw new RuntimeException(
            'Old inventory inspections API returned invalid JSON. ' .
            'Response (first 200 chars): ' . substr($raw, 0, 200)
        );
    }

    if (isset($data['error'])) {
        throw new RuntimeException('Old inventory inspections API error: ' . $data['error']);
    }

    return $data;
}
