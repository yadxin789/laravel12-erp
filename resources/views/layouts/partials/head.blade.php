<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', $title ?? 'ERP企业资源管理系统')</title>
<link rel="icon" href="{{ asset('favicon.ico') }}">
<link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/erp.css') }}">
{{-- 过渡用：兼容原 Tailwind 原子类，页面改用 Bootstrap 后可移除 --}}
<link rel="stylesheet" href="{{ asset('css/erp-utilities.css') }}">
@yield('head')
