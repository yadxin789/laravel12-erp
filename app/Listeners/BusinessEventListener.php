<?php

namespace App\Listeners;

use App\Events\OrderSubmitted;
use App\Events\OrderApproved;
use App\Events\ShipmentConfirmed;
use App\Events\QualityCompleted;
use App\Models\System\AuditLog;
use App\Services\NotificationService;

class BusinessEventListener
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * 订单提交 - 通知审批人
     */
    public function handleOrderSubmitted(OrderSubmitted $event)
    {
        $order = $event->order;
        $type = $event->orderType;

        AuditLog::create([
            'action' => 'submitted',
            'module' => $type === 'sales' ? 'SalesOrder' : ($type === 'purchase' ? 'PurchaseOrder' : ucfirst($type)),
            'user_id' => $order->created_by ?? auth()->id(),
            'user_name' => auth()->user()?->name,
            'record_id' => $order->id,
            'record_type' => get_class($order),
            'remark' => "{$type} order #{$order->id} submitted",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // 通知有审批权限的用户
        $permissionName = $type === 'sales' ? 'approve sales orders'
            : ($type === 'purchase' ? 'approve purchase orders' : 'approve production orders');

        $orderNo = $order->order_no ?? $order->id;
        $this->notificationService->sendToPermission(
            $permissionName,
            ucfirst($type) . ' Order Submitted',
            "Order {$orderNo} has been submitted for approval.",
            'info',
            null
        );
    }

    /**
     * 订单审批 - 通知创建人
     */
    public function handleOrderApproved(OrderApproved $event)
    {
        $order = $event->order;

        AuditLog::create([
            'action' => 'approved',
            'module' => get_class($order),
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'record_id' => $order->id,
            'record_type' => get_class($order),
            'remark' => "Order #{$order->id} approved",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if ($order->created_by) {
            $orderNo = $order->order_no ?? $order->id;
            $this->notificationService->send(
                $order->created_by,
                'Order Approved',
                "Order {$orderNo} has been approved.",
                'success',
                null
            );
        }
    }

    /**
     * 发货确认 - 审计记录
     */
    public function handleShipmentConfirmed(ShipmentConfirmed $event)
    {
        $shipment = $event->shipment;

        AuditLog::create([
            'action' => 'confirmed',
            'module' => 'Shipment',
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'record_id' => $shipment->id,
            'record_type' => get_class($shipment),
            'remark' => "Shipment #{$shipment->id} confirmed, inventory updated",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * 质检完成
     */
    public function handleQualityCompleted(QualityCompleted $event)
    {
        $inspection = $event->inspection;

        AuditLog::create([
            'action' => 'completed',
            'module' => 'QualityInspection',
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'record_id' => $inspection->id,
            'record_type' => get_class($inspection),
            'remark' => "Quality inspection #{$inspection->id} completed",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): array
    {
        return [
            OrderSubmitted::class => 'handleOrderSubmitted',
            OrderApproved::class => 'handleOrderApproved',
            ShipmentConfirmed::class => 'handleShipmentConfirmed',
            QualityCompleted::class => 'handleQualityCompleted',
        ];
    }
}
