<?php

if (!function_exists('formatCurrency')) {
    /**
     * Format currency based on currency code
     * 
     * @param float $amount
     * @param string $currency
     * @param bool $showSymbol
     * @return string
     */
    function formatCurrency($amount, $currency = 'LRD', $showSymbol = true)
    {
        if ($amount === null || $amount === '') {
            $amount = 0;
        }
        
        $amount = (float) $amount;
        
        // Get currency symbols from session or settings
        $session = \Config\Services::session();
        $displayCurrency = $session->get('display_currency') ?? 'LRD';
        
        // If displaying in a different currency, convert
        if ($displayCurrency !== $currency && $displayCurrency !== 'LRD') {
            // Convert to display currency (simplified - you can implement proper conversion)
            $conversionRate = 1;
            if ($currency === 'USD' && $displayCurrency === 'LRD') {
                $conversionRate = 180; // Default exchange rate
            } elseif ($currency === 'LRD' && $displayCurrency === 'USD') {
                $conversionRate = 1 / 180;
            }
            $amount = $amount * $conversionRate;
            $currency = $displayCurrency;
        }
        
        // Format based on currency
        if ($currency === 'USD') {
            $symbol = '$';
            $formatted = number_format($amount, 2, '.', ',');
        } else {
            $symbol = 'L$';
            $formatted = number_format($amount, 2, '.', ',');
        }
        
        if ($showSymbol) {
            return $symbol . ' ' . $formatted;
        }
        
        return $formatted;
    }
}

if (!function_exists('formatNumber')) {
    /**
     * Format number with decimal places
     */
    function formatNumber($number, $decimals = 2)
    {
        return number_format((float) $number, $decimals, '.', ',');
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date for display
     */
    function formatDate($date, $format = 'Y-m-d')
    {
        if (!$date || $date === '0000-00-00') {
            return '-';
        }
        
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }
        
        return date($format, $timestamp);
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format datetime for display
     */
    function formatDateTime($datetime, $format = 'Y-m-d H:i')
    {
        if (!$datetime) {
            return '-';
        }
        
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return $datetime;
        }
        
        return date($format, $timestamp);
    }
}