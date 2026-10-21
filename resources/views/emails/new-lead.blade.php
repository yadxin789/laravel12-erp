<x-mail::message>
# 新销售线索

收到一条新的销售线索：

- **线索名称**：{{ $leadName }}
- **联系人**：{{ $contactPerson ?: '—' }}
- **电话**：{{ $phone ?: '—' }}
- **来源**：{{ $source }}

<x-mail::button :url="url('/app/crm/leads')">
查看线索
</x-mail::button>

感谢使用电子工厂ERP系统。
</x-mail::message>
