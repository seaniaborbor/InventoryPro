<?php

namespace App\Models;

use CodeIgniter\Model;

class AdjustmentEventModel extends Model
{
    protected $table            = 'adjustment_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'product_id', 'event_type', 'quantity', 'unit_cost', 'total_value',
        'currency', 'reference', 'description', 'adjust_stock', 'event_date',
        'related_sale_id', 'related_production_job_id', 'customer_id',
        'created_by', 'updated_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Get all events with product/customer/user names
     */
    public function getEventsWithDetails(array $filters = [])
    {
        $builder = $this->select('adjustment_events.*, products.product_name, customers.customer_name AS customer_name,
                    creator.full_name AS created_by_name, updater.full_name AS updated_by_name')
            ->join('products', 'products.id = adjustment_events.product_id', 'left')
            ->join('customers', 'customers.id = adjustment_events.customer_id', 'left')
            ->join('users AS creator', 'creator.id = adjustment_events.created_by', 'left')
            ->join('users AS updater', 'updater.id = adjustment_events.updated_by', 'left');

        if (!empty($filters['product_id'])) {
            $builder->where('adjustment_events.product_id', $filters['product_id']);
        }
        if (!empty($filters['event_type'])) {
            $builder->where('adjustment_events.event_type', $filters['event_type']);
        }
        if (!empty($filters['source_type'])) {
            if ($filters['source_type'] === 'sale') {
                $builder->where('adjustment_events.related_sale_id IS NOT NULL');
            } elseif ($filters['source_type'] === 'production') {
                $builder->where('adjustment_events.related_production_job_id IS NOT NULL');
            }
        }
        if (!empty($filters['start_date'])) {
            $builder->where('adjustment_events.event_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $builder->where('adjustment_events.event_date <=', $filters['end_date']);
        }

        return $builder->orderBy('adjustment_events.event_date', 'DESC')->paginate(20);
    }

    /**
     * Get single event with all joined details
     */
    public function getEventWithDetails($eventId)
    {
        return $this->select('adjustment_events.*, products.product_name, customers.customer_name AS customer_name,
                    creator.full_name AS created_by_name, updater.full_name AS updated_by_name')
            ->join('products', 'products.id = adjustment_events.product_id', 'left')
            ->join('customers', 'customers.id = adjustment_events.customer_id', 'left')
            ->join('users AS creator', 'creator.id = adjustment_events.created_by', 'left')
            ->join('users AS updater', 'updater.id = adjustment_events.updated_by', 'left')
            ->where('adjustment_events.id', $eventId)
            ->first();
    }

    /**
     * Get summary by event type for a date range
     */
    public function getSummary($startDate, $endDate)
    {
        $builder = $this->db->table('adjustment_events')
            ->select('event_type, COUNT(*) as event_count, SUM(total_value) as total_value')
            ->where('adjustment_events.deleted_at', null);

        if ($startDate && $endDate) {
            $builder->where('adjustment_events.event_date >=', $startDate);
            $builder->where('adjustment_events.event_date <=', $endDate);
        }

        $rows = $builder->groupBy('event_type')->get()->getResultArray();

        $summary = [];
        foreach ($rows as $row) {
            $summary[$row['event_type']] = [
                'event_count' => $row['event_count'],
                'total_value' => $row['total_value'],
            ];
        }
        return $summary;
    }

    /**
     * Get total losses for financial reports
     */
    public function getTotalLosses($startDate, $endDate, $currency = null)
    {
        $builder = $this->select('currency, SUM(total_value) as total')
            ->where('adjustment_events.deleted_at', null);

        if ($startDate) $builder->where('adjustment_events.event_date >=', $startDate);
        if ($endDate)   $builder->where('adjustment_events.event_date <=', $endDate);
        if ($currency)  $builder->where('currency', $currency);

        $rows = $builder->groupBy('currency')->get()->getResultArray();

        $result = ['LRD' => 0, 'USD' => 0];
        foreach ($rows as $row) {
            $cur = $row['currency'];
            if (isset($result[$cur])) {
                $result[$cur] = $row['total'];
            }
        }
        return $result;
    }

    /**
     * Get events for a specific sale
     */
    public function getBySaleId($saleId)
    {
        return $this->select('adjustment_events.*, products.product_name, creator.full_name AS created_by_name')
            ->join('products', 'products.id = adjustment_events.product_id', 'left')
            ->join('users AS creator', 'creator.id = adjustment_events.created_by', 'left')
            ->where('adjustment_events.related_sale_id', $saleId)
            ->findAll();
    }

    /**
     * Get events for a specific production job
     */
    public function getByJobId($jobId)
    {
        return $this->select('adjustment_events.*, products.product_name, creator.full_name AS created_by_name')
            ->join('products', 'products.id = adjustment_events.product_id', 'left')
            ->join('users AS creator', 'creator.id = adjustment_events.created_by', 'left')
            ->where('adjustment_events.related_production_job_id', $jobId)
            ->findAll();
    }
}
