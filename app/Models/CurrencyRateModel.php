<?php

namespace App\Models;

use CodeIgniter\Model;

class CurrencyRateModel extends Model
{
    protected $table            = 'currency_rates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['base_currency', 'target_currency', 'rate', 'date'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = false;

    /**
     * Get rate for specific date
     */
    public function getRate($baseCurrency, $targetCurrency, $date = null)
    {
        if ($baseCurrency === $targetCurrency) {
            return 1.0;
        }
        
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        $rate = $this->where('base_currency', $baseCurrency)
                     ->where('target_currency', $targetCurrency)
                     ->where('date <=', $date)
                     ->orderBy('date', 'DESC')
                     ->first();
        
        return $rate ? (float) $rate['rate'] : null;
    }

    /**
     * Convert amount between currencies
     */
    public function convert($amount, $fromCurrency, $toCurrency, $date = null)
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }
        
        $rate = $this->getRate($fromCurrency, $toCurrency, $date);
        
        if ($rate) {
            return $amount * $rate;
        }
        
        return null;
    }

    /**
     * Get latest rates
     */
    public function getLatestRates()
    {
        $rates = [];
        $currencies = ['LRD', 'USD'];
        
        foreach ($currencies as $base) {
            foreach ($currencies as $target) {
                if ($base !== $target) {
                    $rate = $this->getRate($base, $target);
                    if ($rate) {
                        $rates["{$base}_{$target}"] = $rate;
                    }
                }
            }
        }
        
        return $rates;
    }

    /**
     * Save exchange rate
     */
    public function saveRate($baseCurrency, $targetCurrency, $rate, $date = null)
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        
        // Check if rate exists for this date
        $existing = $this->where('base_currency', $baseCurrency)
                         ->where('target_currency', $targetCurrency)
                         ->where('date', $date)
                         ->first();
        
        if ($existing) {
            return $this->update($existing['id'], ['rate' => $rate]);
        } else {
            return $this->insert([
                'base_currency' => $baseCurrency,
                'target_currency' => $targetCurrency,
                'rate' => $rate,
                'date' => $date
            ]);
        }
    }
}