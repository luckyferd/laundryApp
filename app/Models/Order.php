<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory;

    public const STATUS_BARU = 'baru';
    public const STATUS_PROSES = 'proses';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIAMBIL = 'diambil';

    public const STATUSES = [
        self::STATUS_BARU => 'Baru',
        self::STATUS_PROSES => 'Diproses',
        self::STATUS_SELESAI => 'Selesai',
        self::STATUS_DIAMBIL => 'Diambil',
    ];

    public const STATUS_COLORS = [
        self::STATUS_BARU => 'secondary',
        self::STATUS_PROSES => 'warning',
        self::STATUS_SELESAI => 'info',
        self::STATUS_DIAMBIL => 'success',
    ];

    public const PAYMENT_UNPAID = 'belum_lunas';
    public const PAYMENT_PAID = 'lunas';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_UNPAID => 'Belum Lunas',
        self::PAYMENT_PAID => 'Lunas',
    ];

    protected $fillable = [
        'invoice',
        'customer_id',
        'user_id',
        'status',
        'payment_status',
        'total',
        'received_at',
        'estimated_at',
        'paid_at',
        'picked_up_at',
        'notes',
    ];

    protected $casts = [
        'total' => 'integer',
        'received_at' => 'datetime',
        'estimated_at' => 'datetime',
        'paid_at' => 'datetime',
        'picked_up_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    public function scopePaidBetween($query, $from, $to)
    {
        return $query->paid()->whereBetween('paid_at', [$from, $to]);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function getPaymentLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * Generate a unique invoice number, e.g. LDR-20240101-0001.
     */
    public static function generateInvoice(?Carbon $date = null): string
    {
        $date = $date ?: now();
        $prefix = 'LDR-'.$date->format('Ymd').'-';

        $last = static::where('invoice', 'like', $prefix.'%')
            ->orderByDesc('invoice')
            ->value('invoice');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
