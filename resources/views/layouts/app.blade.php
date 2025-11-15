<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Social-Account') - {{ session('company_name', 'Social-Account') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS v2.2.19 -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
     <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>



    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Sidebar */
        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-closed {
            transform: translateX(-100%);
        }

        .fixed-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 40;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
        }

        .userData {
            z-index: 40;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
        }

        .nav-item {
            position: relative;
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateX(4px);
        }

        .nav-item.active {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.2), transparent);
            border-left: 3px solid #3b82f6;
        }

        .nav-item:hover .nav-icon {
            color: #60a5fa;
            transform: scale(1.1);
        }

        .nav-icon {
            transition: all 0.2s ease;
        }

        /* Dropdown */
        .dropdown-menu {
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 0.3s ease, padding 0.3s ease;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            padding: 0;
        }

        .dropdown-menu.active {
            max-height: 800px;
            opacity: 1;
            padding: 0.5rem 0;
        }

        .nested-item {
            position: relative;
            transition: all 0.2s ease;
        }

        .nested-item:hover {
            background: rgba(59, 130, 246, 0.15);
            padding-left: 1rem;
        }

        .nested-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 0;
            height: 2px;
            background: #3b82f6;
            transition: width 0.3s ease;
        }

        .nested-item:hover::before {
            width: 8px;
        }

        /* Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Header */
        .header-blur {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .notification-badge {
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }
        }

        .search-bar {
            transition: all 0.3s ease;
        }

        .search-bar:focus-within {
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 20rem;
        }

        .chevron-rotate {
            transition: transform 0.3s ease;
        }

        /* Profile Dropdown */
        .profile-dropdown {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(-10px);
            opacity: 0;
            pointer-events: none;
        }

        .profile-dropdown.show {
            transform: translateY(0);
            opacity: 1;
            pointer-events: all;
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">

    @php
        $userName = session('user_name');
        $email = session('email');
        $companyName = 'Social-Automation';
        $isLoggedIn = auth()->check();
    @endphp

    @if (!$isLoggedIn)
        <script> window.location.href = "{{ route('login') }}"; </script>
        @php return; @endphp
    @endif

    <!-- Header -->
    <header class="header-blur shadow-sm border-b border-gray-200 fixed w-full top-0 z-30">
        <div class="flex items-center justify-between px-6 py-3">
            <div class="flex items-center space-x-4">
                <button id="sidebarToggle"
                    class="md:hidden text-gray-600 hover:text-blue-600 focus:outline-none transition-colors">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="flex items-center space-x-3">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-700 p-2.5 rounded-xl shadow-lg">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">{{ $companyName }}</h1>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-6">
                <!-- Search -->
                <div class="hidden md:flex items-center bg-gray-100 rounded-xl px-4 py-2.5 search-bar">
                    <i class="fas fa-search text-gray-400 mr-2"></i>
                    <input type="text" placeholder="Search anything..."
                        class="bg-transparent outline-none text-sm w-64">
                    <kbd class="ml-2 px-2 py-0.5 text-xs bg-gray-200 rounded">Ctrl+K</kbd>
                </div>

                <!-- Notifications -->
                <button
                    class="relative text-gray-600 hover:text-blue-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-bell text-xl"></i>
                    <span
                        class="notification-badge absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold shadow-lg">3</span>
                </button>

                <!-- Profile -->
                <div class="relative">
                    <div id="profileToggle"
                        class="flex items-center space-x-3 cursor-pointer hover:bg-gray-100 rounded-xl p-2 transition-all">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-md overflow-hidden">

                            <div
                                class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                {{ substr($userName, 0, 1) }}
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <p class="text-sm font-semibold text-gray-800">{{ $userName }}</p>
                            <p class="text-xs text-gray-500">{{ $email }}</p>
                        </div>
                        <i id="arrowIcon" class="fas fa-chevron-down text-gray-400 text-sm chevron-rotate"></i>
                    </div>

                    <div id="profileDetails"
                        class="profile-dropdown absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden">
                        <div class="p-4 userData text-white">
                            <p class="font-semibold">{{ $userName }}</p>
                            <p class="text-blue-100 text-sm">{{ $email }}</p>
                            <p class="text-blue-100 text-xs mt-1">{{ $companyName }}</p>
                        </div>
                        <div class="p-2">
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                    class="flex items-center px-4 py-2.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium w-full text-left">
                                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex pt-16">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar-transition fixed-sidebar w-72">
            <div class="h-full overflow-y-auto custom-scrollbar">
                <!-- User Profile Card -->
                <div class="p-6 border-b border-gray-700">
                    <div class="flex items-center space-x-4">

                        <div class="flex-1">
                            <h3 class="font-bold text-white text-lg">{{ $companyName }}</h3>
                            <p class="text-sm text-blue-300">{{ $email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="py-4">
                    <div class="px-6 mb-3">
                        <h4 class="text-xs uppercase tracking-wider text-gray-400 font-bold">Main Menu</h4>
                    </div>
                    <ul class="space-y-1 px-3">
                        <li>
                            <a href="{{ route('home') }}"
                                class="nav-item flex items-center px-4 py-3 text-gray-300 rounded-xl group {{ request()->routeIs('home') ? 'active' : '' }}">
                                <i class="fas fa-home mr-4 text-blue-400 nav-icon text-lg"></i>
                                <span class="font-medium">Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('socialAccount') }}"
                                class="nav-item flex items-center px-4 py-3 text-gray-300 rounded-xl group {{ request()->routeIs('social-account') ? 'active' : '' }}">
                                <i class="fas fa-user-friends mr-4 text-blue-400 nav-icon text-lg"></i>
                                <span class="font-medium">Social Account</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('proxy') }}"
                                class="nav-item flex items-center px-4 py-3 text-gray-300 rounded-xl group {{ request()->routeIs('proxy') ? 'active' : '' }}">
                                <i class="fas fa-server mr-4 text-blue-400 nav-icon text-lg"></i>
                                <span class="font-medium">Proxy</span>
                            </a>
                        </li>
                    </ul>


                    <div class="px-6 mt-6 mb-3">
                        <h4 class="text-xs uppercase tracking-wider text-gray-400 font-bold">Management</h4>
                    </div>
                    <ul class="space-y-1 px-3">
                        <!-- Settings -->
                        <li>
                            <button id="settingsToggle"
                                class="nav-item flex items-center justify-between w-full px-4 py-3 text-gray-300 rounded-xl focus:outline-none">
                                <span class="flex items-center">
                                    <i class="fas fa-cog mr-4 text-yellow-400 nav-icon text-lg"></i>
                                    <span class="font-medium">Settings</span>
                                </span>
                                <i id="settingsChevron" class="fas fa-chevron-down text-gray-400 chevron-rotate"></i>
                            </button>
                            <ul id="settingsDropdown" class="dropdown-menu ml-4 mt-1 space-y-1">
                                <!-- Account -->
                                <li>
                                    <button id="accountToggle"
                                        class="flex items-center justify-between w-full px-4 py-2 text-gray-400 rounded-lg hover:bg-gray-700 transition-colors">
                                        <span class="flex items-center text-sm"><i
                                                class="fas fa-wallet mr-3 text-blue-300"></i> Account</span>
                                        <i id="accountChevron"
                                            class="fas fa-chevron-down text-gray-500 text-xs chevron-rotate"></i>
                                    </button>
                                    <ul id="accountDropdown" class="dropdown-menu ml-4 space-y-1">
                                        <li><a href=""
                                                class="nested-item flex items-center px-4 py-2 text-gray-400 rounded-lg text-sm"><i
                                                    class="fas fa-sitemap mr-3 text-xs"></i>Account Type</a></li>

                                    </ul>
                                </li>
                                <!-- Master, Item, Admin... (same structure) -->
                                <!-- Add others as needed -->
                            </ul>
                        </li>

                        <li>
                            <a href="#" class="nav-item flex items-center px-4 py-3 text-gray-300 rounded-xl">
                                <i class="fas fa-question-circle mr-4 text-cyan-400 nav-icon text-lg"></i>
                                <span class="font-medium">Help Center</span>
                            </a>
                        </li>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="flex items-center px-4 py-2.5 text-red-400 hover:bg-red-50 rounded-lg transition-colors font-medium w-full text-left">
                                <i class="fas fa-sign-out-alt mr-3"></i> Logout
                            </button>
                        </form>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 md:ml-72 flex flex-col min-h-screen">
            <div class="flex-1 p-8">
                @yield('content')
            </div>
            <footer class="bg-white border-t border-gray-200 shadow-inner">
                <div class="px-8 py-4 flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        &copy; {{ date('Y') }} <span class="font-semibold">{{ $companyName }}</span>. All rights
                        reserved.
                    </div>
                    <div class="text-xs text-gray-400">Powered by Social-Account System v2.0</div>
                </div>
            </footer>
        </main>
    </div>

    <!-- Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden hidden transition-opacity">
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    @if (isset($modules))
        @foreach ($modules as $module)
            <script src="{{ asset('assets/app_module/' . $module) }}"></script>
        @endforeach
    @endif
    {{-- custom.js --}}
    <script src="{{ asset('assets/app_module/custom.js') }}"></script>
    <script>
        // Profile Dropdown
        $('#profileToggle').on('click', function (e) {
            e.stopPropagation();
            $('#profileDetails').toggleClass('show');
            $('#arrowIcon').css('transform', $('#profileDetails').hasClass('show') ? 'rotate(180deg)' : 'rotate(0deg)');
        });
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#profileToggle, #profileDetails').length) {
                $('#profileDetails').removeClass('show');
                $('#arrowIcon').css('transform', 'rotate(0deg)');
            }
        });

        // Dropdown Toggle
        function setupDropdown(toggleId, dropdownId, chevronId) {
            $(`#${toggleId}`).on('click', function (e) {
                e.stopPropagation();
                $(`#${dropdownId}`).toggleClass('active');
                $(`#${chevronId}`).css('transform', $(`#${dropdownId}`).hasClass('active') ? 'rotate(180deg)' : 'rotate(0deg)');
            });
        }
        setupDropdown('purchaseToggle', 'purchaseDropdown', 'purchaseChevron');
        setupDropdown('salesToggle', 'salesDropdown', 'salesChevron');
        setupDropdown('inventoryToggle', 'inventoryDropdown', 'inventoryChevron');
        setupDropdown('journalVoucherToggle', 'journalVoucherDropdown', 'journalVoucherChevron');
        setupDropdown('settingsToggle', 'settingsDropdown', 'settingsChevron');
        setupDropdown('accountToggle', 'accountDropdown', 'accountChevron');

        // Mobile Sidebar
        $('#sidebarToggle, #sidebarOverlay').on('click', function () {
            $('#sidebar').toggleClass('sidebar-closed');
            $('#sidebarOverlay').toggleClass('hidden');
        });

        // Ctrl+K Search
        $(document).on('keydown', function (e) {
            if (e.ctrlKey && e.key === 'k') {
                e.preventDefault();
                $('.search-bar input').focus();
            }
        });

        // Active Link
        const path = window.location.pathname;
        $('.nav-item').each(function () {
            if ($(this).attr('href') === path) {
                $(this).addClass('active');
            }
        });
    </script>

    @yield('scripts')
</body>

</html>