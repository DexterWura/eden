<?php

/**
 * Admin module keys (sidenav section keys) and route-pattern → module map
 * for staff access control. Built from sidenav.json.
 */
$sidenavPath = resource_path('views/admin/partials/sidenav.json');
$sidenav = [];
if (file_exists($sidenavPath)) {
    $sidenav = json_decode(file_get_contents($sidenavPath), true) ?: [];
}

$modules = array_keys($sidenav);
$routePatterns = [];

foreach ($sidenav as $moduleKey => $data) {
    if (empty($data)) {
        continue;
    }
    // Top-level menu_active (can be comma-separated, e.g. "admin.listing*,admin.marketplace*")
    if (!empty($data['menu_active'])) {
        $patterns = is_array($data['menu_active']) ? $data['menu_active'] : explode(',', $data['menu_active']);
        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);
            if ($pattern !== '') {
                $routePatterns[$pattern] = $moduleKey;
            }
        }
    }
    // Submenu menu_active and route_name
    if (!empty($data['submenu']) && is_array($data['submenu'])) {
        foreach ($data['submenu'] as $item) {
            if (!empty($item['menu_active'])) {
                $patterns = is_array($item['menu_active']) ? $item['menu_active'] : explode(',', $item['menu_active']);
                foreach ($patterns as $pattern) {
                    $pattern = trim($pattern);
                    if ($pattern !== '') {
                        $routePatterns[$pattern] = $moduleKey;
                    }
                }
            }
            if (!empty($item['route_name'])) {
                $routePatterns[trim($item['route_name'])] = $moduleKey;
            }
        }
    }
    // Top-level route_name
    if (!empty($data['route_name'])) {
        $routePatterns[trim($data['route_name'])] = $moduleKey;
    }
}

return [
    'modules' => $modules,
    'route_patterns' => $routePatterns,
];
