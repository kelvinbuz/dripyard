<?php
// Currency management system for DripYard

class CurrencyManager {
    private static $currencies = [
        'GHS' => [
            'name' => 'Ghanaian Cedi',
            'symbol' => 'GH₵',
            'code' => 'GHS',
            'rate' => 1.0, // Now base currency
            'flag' => '🇬🇭',
            'countries' => ['GH']
        ],
        'NGN' => [
            'name' => 'Nigerian Naira',
            'symbol' => '₦',
            'code' => 'NGN',
            'rate' => 66.67, // 1 GHS = 66.67 NGN (approximate)
            'flag' => '🇳🇬',
            'countries' => ['NG']
        ],
        'USD' => [
            'name' => 'US Dollar',
            'symbol' => '$',
            'code' => 'USD',
            'rate' => 0.087, // 1 GHS = 0.087 USD (approximate)
            'flag' => '🇺🇸',
            'countries' => ['US', 'CA', 'PR', 'VI', 'GU', 'AS']
        ],
        'GBP' => [
            'name' => 'British Pound',
            'symbol' => '£',
            'code' => 'GBP',
            'rate' => 0.067, // 1 GHS = 0.067 GBP (approximate)
            'flag' => '🇬🇧',
            'countries' => ['GB', 'GG', 'JE', 'IM']
        ],
        'EUR' => [
            'name' => 'Euro',
            'symbol' => '€',
            'code' => 'EUR',
            'rate' => 0.080, // 1 GHS = 0.080 EUR (approximate)
            'flag' => '🇪🇺',
            'countries' => ['DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'PT', 'FI', 'IE', 'GR', 'LU', 'CY', 'MT', 'SI', 'SK', 'EE', 'LV', 'LT']
        ],
        'CAD' => [
            'name' => 'Canadian Dollar',
            'symbol' => 'C$',
            'code' => 'CAD',
            'rate' => 0.120, // 1 GHS = 0.120 CAD (approximate)
            'flag' => '🇨🇦',
            'countries' => ['CA']
        ],
        'AUD' => [
            'name' => 'Australian Dollar',
            'symbol' => 'A$',
            'code' => 'AUD',
            'rate' => 0.133, // 1 GHS = 0.133 AUD (approximate)
            'flag' => '🇦🇺',
            'countries' => ['AU', 'NF', 'CX', 'CC']
        ]
    ];

    public static function detectCurrencyFromIP() {
        // Get user IP
        $ip = self::getUserIP();
        
        // For development/testing, you might want to use a fixed IP
        // $ip = '8.8.8.8'; // US IP for testing
        
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'NGN'; // Default to NGN for localhost
        }

        // Use a free IP geolocation service
        $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode");
        
        if ($response) {
            $data = json_decode($response, true);
            if ($data && isset($data['countryCode'])) {
                return self::getCurrencyByCountry($data['countryCode']);
            }
        }
        
        return 'NGN'; // Default fallback
    }

    public static function getUserIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public static function getCurrencyByCountry($countryCode) {
        foreach (self::$currencies as $currencyCode => $currency) {
            if (in_array($countryCode, $currency['countries'])) {
                return $currencyCode;
            }
        }
        return 'NGN'; // Default fallback
    }

    public static function getCurrentCurrency() {
        // Always return GHS as default currency
        return 'GHS';
    }

    public static function setCurrency($currencyCode) {
        if (isset(self::$currencies[$currencyCode])) {
            $_SESSION['user_currency'] = $currencyCode;
            // Clear auto-detection flag when user manually sets currency
            unset($_SESSION['auto_detected_currency']);
            return true;
        }
        return false;
    }

    public static function formatPrice($amountGHS, $currencyCode = null) {
        if ($currencyCode === null) {
            $currencyCode = self::getCurrentCurrency();
        }
        
        $currency = self::$currencies[$currencyCode];
        $convertedAmount = $amountGHS * $currency['rate'];
        
        // Format based on currency
        switch ($currencyCode) {
            case 'GHS':
                return $currency['symbol'] . number_format($convertedAmount, 2);
            case 'NGN':
                return $currency['symbol'] . number_format($convertedAmount, 2);
            case 'USD':
            case 'CAD':
            case 'AUD':
                return $currency['symbol'] . number_format($convertedAmount, 2);
            case 'GBP':
                return $currency['symbol'] . number_format($convertedAmount, 2);
            case 'EUR':
                return $currency['symbol'] . ' ' . number_format($convertedAmount, 2);
            default:
                return $currency['symbol'] . number_format($convertedAmount, 2);
        }
    }

    public static function getAllCurrencies() {
        return self::$currencies;
    }

    public static function getCurrencyInfo($currencyCode) {
        return self::$currencies[$currencyCode] ?? null;
    }

    public static function convertPrice($amountGHS, $toCurrency) {
        if (!isset(self::$currencies[$toCurrency])) {
            return $amountGHS;
        }
        
        return $amountGHS * self::$currencies[$toCurrency]['rate'];
    }
}

// Initialize session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
