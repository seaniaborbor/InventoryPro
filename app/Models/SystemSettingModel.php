<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSettingModel extends Model
{
    protected $table            = 'system_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['setting_key', 'setting_value', 'setting_type', 'group'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $cache = [];

    /**
     * Get setting value by key
     */
    public function get($key, $default = null)
    {
        // Check cache first
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        
        $setting = $this->where('setting_key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        $value = $setting['setting_value'];
        
        // Cast based on type
        switch ($setting['setting_type']) {
            case 'integer':
                $value = (int) $value;
                break;
            case 'decimal':
                $value = (float) $value;
                break;
            case 'boolean':
                $value = (bool) $value;
                break;
            case 'json':
                $value = json_decode($value, true);
                break;
        }
        
        $this->cache[$key] = $value;
        
        return $value;
    }

    /**
     * Set setting value - RENAMED to saveSetting to avoid conflict
     */
    public function saveSetting($key, $value, $type = 'string', $group = 'general')
    {
        // Convert value to string for storage
        if (is_array($value)) {
            $value = json_encode($value);
            $type = 'json';
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
            $type = 'boolean';
        } elseif (is_int($value)) {
            $type = 'integer';
        } elseif (is_float($value)) {
            $type = 'decimal';
        }
        
        $existing = $this->where('setting_key', $key)->first();
        
        if ($existing) {
            return $this->update($existing['id'], [
                'setting_value' => (string) $value,
                'setting_type' => $type,
                'group' => $group
            ]);
        } else {
            return $this->insert([
                'setting_key' => $key,
                'setting_value' => (string) $value,
                'setting_type' => $type,
                'group' => $group
            ]);
        }
    }

    /**
     * Get all settings by group
     */
    public function getByGroup($group)
    {
        $settings = $this->where('group', $group)->findAll();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->get($setting['setting_key']);
        }
        
        return $result;
    }

    /**
     * Get all settings as key-value array
     */
    public function getAllSettings()
    {
        $settings = $this->findAll();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->get($setting['setting_key']);
        }
        
        return $result;
    }
    
    /**
     * Update multiple settings at once
     */
    public function updateSettings(array $settings)
    {
        foreach ($settings as $key => $value) {
            $this->saveSetting($key, $value);
        }
        return true;
    }
}