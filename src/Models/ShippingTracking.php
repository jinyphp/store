<?php

namespace Jiny\Store\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Jiny\Store\Traits\HasAssignments;

class ShippingTracking extends Model
{
    use HasAssignments;

    protected $fillable = [
        'tracking_number',
        'order_id',
        'method_id',
        'carrier',
        'status',
        'shipped_at',
        'delivered_at',
        'delivery_notes',
        'recipient_name',
        'delivery_address',
        'tracking_events',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'tracking_events' => 'array',
    ];

    /**
     * 배송 방법 관계
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'method_id');
    }

    /**
     * 배송 라벨 관계
     */
    public function label(): HasOne
    {
        return $this->hasOne(ShippingLabel::class, 'tracking_id');
    }

    /**
     * 상태별 스코프
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * 기간별 스코프
     */
    public function scopeShippedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('shipped_at', [$startDate, $endDate]);
    }

    public function scopeDeliveredBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('delivered_at', [$startDate, $endDate]);
    }

    /**
     * 배송사별 스코프
     */
    public function scopeByCarrier($query, $carrier)
    {
        return $query->where('carrier', $carrier);
    }

    /**
     * 추적 이벤트 추가
     */
    public function addTrackingEvent($status, $description, $location = null, $timestamp = null)
    {
        $events = $this->tracking_events ?? [];

        $event = [
            'status' => $status,
            'description' => $description,
            'location' => $location,
            'timestamp' => $timestamp ?? now()->toISOString(),
        ];

        $events[] = $event;

        $this->update([
            'tracking_events' => $events,
            'status' => $status
        ]);

        // 특정 상태일 때 추가 업데이트
        if ($status === 'delivered') {
            $this->update(['delivered_at' => $timestamp ?? now()]);
        } elseif ($status === 'picked_up' && !$this->shipped_at) {
            $this->update(['shipped_at' => $timestamp ?? now()]);
        }

        return $this;
    }

    /**
     * 상태 업데이트
     */
    public function updateStatus($newStatus, $description = null, $location = null)
    {
        $this->addTrackingEvent(
            $newStatus,
            $description ?? $this->getStatusDescription($newStatus),
            $location
        );

        return $this;
    }

    /**
     * 상태 설명 가져오기
     */
    protected function getStatusDescription($status): string
    {
        return match($status) {
            'pending' => '배송 준비 중',
            'picked_up' => '상품이 픽업되었습니다',
            'in_transit' => '배송 중',
            'out_for_delivery' => '배송 출발',
            'delivered' => '배송 완료',
            'failed' => '배송 실패',
            'returned' => '반송',
            default => '상태 업데이트'
        };
    }

    /**
     * 배송 완료 처리
     */
    public function markAsDelivered($recipientName = null, $deliveryNotes = null)
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'recipient_name' => $recipientName,
            'delivery_notes' => $deliveryNotes,
        ]);

        $this->addTrackingEvent(
            'delivered',
            '배송이 완료되었습니다' . ($recipientName ? " (수령인: {$recipientName})" : ''),
            null,
            now()
        );

        return $this;
    }

    /**
     * 배송 지연 여부 확인
     */
    public function isDelayed(): bool
    {
        if (!$this->shipped_at || $this->status === 'delivered') {
            return false;
        }

        $estimatedDays = $this->method->estimated_days_max ?? 7;
        $expectedDelivery = $this->shipped_at->addDays($estimatedDays);

        return now()->isAfter($expectedDelivery);
    }

    /**
     * 배송 소요 시간 계산
     */
    public function getDeliveryDuration(): ?int
    {
        if (!$this->shipped_at || !$this->delivered_at) {
            return null;
        }

        return $this->shipped_at->diffInDays($this->delivered_at);
    }

    /**
     * 최근 추적 이벤트 가져오기
     */
    public function getLatestEvent(): ?array
    {
        $events = $this->tracking_events ?? [];
        return empty($events) ? null : end($events);
    }

    /**
     * 배송 진행률 계산 (0-100)
     */
    public function getProgressPercentage(): int
    {
        return match($this->status) {
            'pending' => 0,
            'picked_up' => 20,
            'in_transit' => 60,
            'out_for_delivery' => 90,
            'delivered' => 100,
            'failed', 'returned' => 0,
            default => 0
        };
    }
}