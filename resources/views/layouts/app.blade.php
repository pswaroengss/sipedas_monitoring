<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title', 'SIPEDAS Monitoring')</title>
    @include('layouts.header')
    <style>
        :root {
            --brand-bg: #3b231a;
            --brand-bg-dark: #2b1a14;
            --brand-accent: #f2c94c;
            --brand-accent-soft: rgba(242, 201, 76, 0.2);
            --brand-card: #f7f1eb;
            --brand-ink: #2b1a14;
            --brand-muted: #7b5f52;
        }
        .app-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 240px 1fr;
            background: #efe6de;
        }
        .sidebar {
            background: linear-gradient(180deg, var(--brand-bg) 0%, var(--brand-bg-dark) 100%);
            color: #f3ede7;
            padding: 24px 18px;
            box-shadow: 6px 0 20px rgba(59, 35, 26, 0.35);
        }
        .sidebar-brand {
            font-weight: 800;
            letter-spacing: 2px;
            font-size: 16px;
            color: var(--brand-accent);
            text-transform: uppercase;
            margin-bottom: 0;
        }
        .sidebar-nav {
            display: grid;
            gap: 10px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 10px;
            color: #f3ede7;
            text-decoration: none;
            background: rgba(243, 237, 231, 0.08);
            border: 1px solid rgba(243, 237, 231, 0.18);
            transition: all 0.2s ease;
        }
        .sidebar-link:hover {
            color: #fff;
            background: var(--brand-accent-soft);
            border-color: rgba(242, 201, 76, 0.6);
        }
        .app-main {
            min-height: 100vh;
            background: #efe6de;
            min-width: 0;
        }
        @media (max-width: 992px) {
            .app-shell {
                grid-template-columns: 1fr;
            }
            .sidebar {
                position: sticky;
                top: 0;
                z-index: 10;
            }
        }
    </style>
    @stack('head')
</head>
<body class="bg-light">
    @php($showSidebar = session()->has('auth_user'))
    <div class="{{ $showSidebar ? 'app-shell' : '' }}">
        @if ($showSidebar)
            <aside class="sidebar">
                <div class="sidebar-brand mb-3">SIPEDAS</div>
                <nav class="sidebar-nav">
                    <a href="/dashboard" class="sidebar-link">Dashboard</a>
                    <a href="/monitoring" class="sidebar-link">Monitoring</a>
                </nav>
            </aside>
        @endif
        <main class="app-main">
            <div class="container py-4">
                @yield('content')
            </div>
        </main>
    </div>

    @include('layouts.scripts')
</body>
</html>
