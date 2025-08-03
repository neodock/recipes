<?php
namespace Neodock\Recipes;

class AdminUtilities
{
    public static function IsAdmin() : bool {
        //TODO: implement
        return true;
    }

    public static function IsIPInRange($ip, $range) : bool {
        // If the range doesn't contain a CIDR prefix, it's a single IP
        if (str_contains($range, '/')) {
            return $ip === $range;
        }

        list($subnet, $bits) = explode('/', $range);
        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet_long &= $mask; // Subnet in long format

        return ($ip_long & $mask) == $subnet_long;
    }

    public static function GetClientIP() : string {
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

    public static function IsClientIPAllowed() : bool {
        $config = \Neodock\Framework\Configuration::getInstance();

        // If USE_IP_ALLOWLIST is disabled, allow all IPs
        if (!$config->get('use_ip_allowlist')) {
            return true;
        }

        $ips = $config->get('ip_allowlist');

        // If allowlist is empty, deny all
        if (empty($ips)) {
            return false;
        }

        $client_ip = self::GetClientIP();

        // Check if IP is in allowlist
        foreach ($ips as $allowed) {
            // Check if it's a hostname
            if (!filter_var($allowed, FILTER_VALIDATE_IP) && !str_contains($allowed, '/')) {
                // Try to resolve hostname to IP
                $resolved_ips = gethostbynamel($allowed);
                if ($resolved_ips && in_array($client_ip, $resolved_ips)) {
                    return true;
                }
                continue;
            }

            // Check if IP is in range
            if (self::IsIPInRange($client_ip, $allowed)) {
                return true;
            }
        }

        return false;
    }
}