<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plug4MarketLog extends Model
{
    use HasFactory;
    protected $table = 'plug4market_logs';
    protected $fillable = [
        'action',
        'status',
        'message',
        'details',
        'ip_address',
        'user_agent',
        'execution_time_ms'
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get logs by action
     */
    public static function getByAction($action, $limit = 50)
    {
        return self::where('action', $action)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by status
     */
    public static function getByStatus($status, $limit = 50)
    {
        return self::where('status', $status)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent logs
     */
    public static function getRecent($limit = 100)
    {
        return self::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs from today
     */
    public static function getToday()
    {
        return self::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get logs from last 7 days
     */
    public static function getLastWeek()
    {
        return self::where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get status color for badges
     */
    public function getStatusColorAttribute()
    {
        return [
            'success' => 'success',
            'error' => 'danger',
            'warning' => 'warning',
            'info' => 'info'
        ][$this->status] ?? 'secondary';
    }

    /**
     * Get formatted execution time
     */
    public function getFormattedExecutionTimeAttribute()
    {
        if (!$this->execution_time_ms) {
            return 'N/A';
        }

        if ($this->execution_time_ms < 1000) {
            return $this->execution_time_ms . 'ms';
        }

        return round($this->execution_time_ms / 1000, 2) . 's';
    }

    /**
     * Get formatted details for display
     */
    public function getFormattedDetailsAttribute()
    {
        if (!$this->details) {
            return null;
        }

        return json_encode($this->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
} 