<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') - {{ config('app.name') }}</title>

    {{-- Favicon - Google compliant --}}
    @php
        $favicon = isset($siteSettings['favicon']) ? asset('storage/' . $siteSettings['favicon']) : asset('images/favo.png');
    @endphp
    <link rel="icon" type="image/png" sizes="48x48" href="{{ $favicon }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $favicon }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $favicon }}">
    <link rel="shortcut icon" type="image/png" href="{{ $favicon }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $favicon }}">
    <meta name="msapplication-TileImage" content="{{ $favicon }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    
    <!-- Google Fonts - Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom Styles -->
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .sidebar-link.active { background-color: rgb(59, 130, 246); color: white; }
        .sidebar-link:hover:not(.active) { background-color: rgb(243, 244, 246); }
        [x-cloak] { display: none !important; }
    </style>
    
    @stack('styles')
    
    {{-- Instant Scroll Restoration --}}
    <script>
        // Force manual scroll restoration to prevent browser interference
        if (history.scrollRestoration) {
            history.scrollRestoration = 'manual';
        }
        
        // Restore scroll immediately if saved
        const savedPos = sessionStorage.getItem('scroll_pos');
        if (savedPos) {
            window.scrollTo(0, parseInt(savedPos));
            sessionStorage.removeItem('scroll_pos');
        }

        // Global Helpers (sessionStorage)
        window.saveScrollPos = function() {
            sessionStorage.setItem('scroll_pos', window.scrollY);
        }
        window.submitWithScroll = function(element) {
            saveScrollPos();
            if(element.form) element.form.submit();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const maintainScrollElements = document.querySelectorAll('[data-maintain-scroll="true"]');
            maintainScrollElements.forEach(el => {
                if (el.tagName === 'A') {
                    el.addEventListener('click', saveScrollPos);
                }
                if (el.tagName === 'FORM') {
                    el.addEventListener('submit', saveScrollPos);
                }
            });
        });
    </script>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: window.innerWidth >= 1024, darkMode: false }">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Mobile Backdrop -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
             x-cloak>
        </div>

        <!-- Sidebar -->
        <aside 
            class="fixed inset-y-0 right-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 lg:relative lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0 lg:w-20'"
        >
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 bg-blue-600">
                <a href="{{ route('dashboard.home') }}" class="text-white text-xl font-bold flex items-center justify-center w-full">
                    <span x-show="sidebarOpen" x-transition.opacity>إيه القصه؟</span>
                    <span x-show="!sidebarOpen" class="text-2xl lg:block hidden">📰</span>
                </a>
            </div>
            
            <!-- Navigation -->
            <nav class="mt-6 px-3 h-[calc(100vh-4rem)] overflow-y-auto custom-scrollbar pb-24">
                <a href="{{ route('dashboard.home') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.home') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">الرئيسية</span>
                </a>
                
                @if(auth('admin')->user()->hasPermission('manage_articles'))
                <a href="{{ route('dashboard.articles.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.articles.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">المقالات</span>
                </a>
                @endif
                
                @if(auth('admin')->user()->hasPermission('manage_categories'))
                <a href="{{ route('dashboard.categories.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.categories.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">الأقسام</span>
                </a>
                @endif
                
                @if(auth('admin')->user()->hasPermission('manage_tags'))
                <a href="{{ route('dashboard.tags.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.tags.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">الوسوم</span>
                </a>
                @endif

                @if(auth('admin')->user()->hasPermission('view_trends'))
                <a href="{{ route('dashboard.keywords.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.keywords.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">رادار الترندات الذكي</span>
                </a>
                @endif

                <a href="{{ route('dashboard.trending-searches.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.trending-searches.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10h4l-2-2 2-2h-4"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">طلبات البحث الرائجة</span>
                </a>


                
                @if(auth('admin')->user()->hasPermission('manage_headlines'))
                <a href="{{ route('dashboard.headlines.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.headlines.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">عناوين Google Discover</span>
                </a>
                @endif

                @if(auth('admin')->user()->hasPermission('manage_campaigns'))
                <a href="{{ route('dashboard.campaigns.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.campaigns.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">إنتاج محتوى تلقائي</span>
                </a>
                @endif

                @if(auth('admin')->user()->hasPermission('manage_settings'))
                <a href="{{ route('dashboard.social-publisher.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.social-publisher.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">ردار السوشيال ميديا</span>
                </a>
                @endif


                @if(auth('admin')->user()->hasPermission('view_analytics'))
                <a href="{{ route('dashboard.analytics') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.analytics') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">تحليلات المشاهدات</span>
                </a>
                @endif
                
                @if(auth('admin')->user()->hasPermission('manage_pages'))
                <a href="{{ route('dashboard.pages.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.pages.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">الصفحات</span>
                </a>
                @endif
                
                @if(auth('admin')->user()->hasPermission('manage_media'))
                <a href="{{ route('dashboard.media.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.media.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">الوسائط</span>
                </a>
                @endif
                
                <div class="border-t border-gray-200 my-4"></div>
                
                @if(auth('admin')->user()->hasPermission('manage_settings'))
                <a href="{{ route('dashboard.settings.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.settings.index') || (request()->routeIs('dashboard.settings.*') && !request()->routeIs('dashboard.settings.ads') && !request()->routeIs('dashboard.settings.cache')) ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">الإعدادات العامة</span>
                </a>

                <a href="{{ route('dashboard.settings.cache') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.settings.cache') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">الكاش والأداء</span>
                </a>

                <a href="{{ route('dashboard.settings.ads') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.settings.ads') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">إدارة الإعلانات</span>
                </a>
                @endif
                
                @if(auth('admin')->user()->hasPermission('manage_admins'))
                <a href="{{ route('dashboard.admins.index') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.admins.index') || request()->routeIs('dashboard.admins.create') || request()->routeIs('dashboard.admins.edit') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">المشرفين</span>
                </a>

                <a href="{{ route('dashboard.admins.permissions') }}" 
                   class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg mb-1 text-gray-700 {{ request()->routeIs('dashboard.admins.permissions') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="whitespace-nowrap">صلاحيات الأدوار</span>
                </a>
                @endif
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-xl font-semibold text-gray-800">@yield('page-title', 'لوحة التحكم')</h1>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- View Site -->
                    <a href="{{ url('/') }}" target="_blank" class="text-gray-500 hover:text-blue-600" title="عرض الموقع">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    
                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 text-gray-700 hover:text-gray-900">
                            <img src="{{ auth()->guard('admin')->user()->avatar_url }}" 
                                 alt="{{ auth()->guard('admin')->user()->name }}" 
                                 class="w-8 h-8 rounded-full">
                            <span class="hidden sm:inline">{{ auth()->guard('admin')->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                            <a href="{{ route('dashboard.profile') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                الملف الشخصي
                            </a>
                            <form action="{{ route('dashboard.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-right px-4 py-2 text-red-600 hover:bg-gray-100">
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">&times;</button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">&times;</button>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                    <div class="font-bold mb-2">يرجى تصحيح الأخطاء التالية:</div>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/cx145i07yl76pwohh4pq3lwfvdk99zu0zg8eeri7exzsz186/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    
    @yield('extra-html')
    @stack('scripts')
</body>
</html>
