<x-mail::message>
# 销售订单待审批

您有一个新的销售订单需要审批：

- **订单号**：{{ $orderNo }}
- **客户**：{{ $customerName }}
- **金额**：¥{{ number_format($totalAmount, 2) }}

<x-mail::button :url="url('/app/sales/orders')">
查看订单
</x-mail::button>

感谢使用电子工厂ERP系统。
</x-mail::message>
