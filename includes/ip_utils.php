<?php
/**
 * IP address utility functions for Neodock Recipes
 */

/**
 * Check if an IP address is in a CIDR range
 * 
 * @param string $ip IP address to check
 * @param string $range CIDR range (e.g. 192.168.1.0/24)
 * @return bool True if IP is in range, false otherwise
 */
function ip_in_range($ip, $range) {
    // If the range doesn't contain a CIDR prefix, it's a single IP
    if (strpos($range, '/') === false) {
        return $ip === $range;
    }

    list($subnet, $bits) = explode('/', $range);
    $ip_long = ip2long($ip);
    $subnet_long = ip2long($subnet);
    $mask = -1 << (32 - $bits);
    $subnet_long &= $mask; // Subnet in long format

    return ($ip_long & $mask) == $subnet_long;
}

/**
 * Get client IP address considering X-Forwarded-For header
 * 
 * @return string Client IP address
 */
function get_client_ip() {
    // Check for X-Forwarded-For header
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // X-Forwarded-For can contain multiple IPs separated by commas
        // The leftmost IP is the original client IP
        $forwarded_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $client_ip = trim($forwarded_ips[0]);

        // Validate the IP format
        if (filter_var($client_ip, FILTER_VALIDATE_IP)) {
            return $client_ip;
        }
    }

    // Fallback to REMOTE_ADDR if X-Forwarded-For is not valid
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Check if current client IP is in the allowlist
 * 
 * @return bool True if client IP is allowed, false otherwise
 */
function is_client_ip_allowed() {
    global $RATING_IP_ALLOWLIST;

    // If USE_IP_ALLOWLIST is disabled, allow all IPs
    if (!defined('USE_IP_ALLOWLIST') || !USE_IP_ALLOWLIST) {
        return true;
    }

    // If allowlist is empty, deny all
    if (empty($RATING_IP_ALLOWLIST)) {
        return false;
    }

    $client_ip = get_client_ip();

    // Check if IP is in allowlist
    foreach ($RATING_IP_ALLOWLIST as $allowed) {
        // Check if it's a hostname
        if (!filter_var($allowed, FILTER_VALIDATE_IP) && strpos($allowed, '/') === false) {
            // Try to resolve hostname to IP
            $resolved_ips = gethostbynamel($allowed);
            if ($resolved_ips && in_array($client_ip, $resolved_ips)) {
                return true;
            }
            continue;
        }

        // Check if IP is in range
        if (ip_in_range($client_ip, $allowed)) {
            return true;
        }
    }

    return false;
}
