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
            font-size: 20px;
            color: var(--brand-accent);
            text-transform: uppercase;
            margin-bottom: 0;
            text-shadow:
                0 -1px 0 rgba(255, 255, 255, 0.08),
                0 1px 0 rgba(0, 0, 0, 0.6),
                0 4px 10px rgba(0, 0, 0, 0.4);
            background: linear-gradient(180deg, rgba(255, 232, 179, 0.08), rgba(255, 232, 179, 0));
            display: inline-block;
            padding: 4px 8px;
            border-radius: 8px;
            border: 1px solid rgba(255, 232, 179, 0.08);
            text-align: center;
        }
        .sidebar-brand-wrap {
            margin-bottom: 16px;
            display: flex;
            justify-content: center;
        }
        .sidebar-nav {
            display: grid;
            gap: 10px;
        }
        .sidebar-footer {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid rgba(243, 237, 231, 0.12);
        }
        .logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(242, 201, 76, 0.3);
            background: rgba(242, 201, 76, 0.12);
            color: #f3ede7;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .logout-btn:hover {
            background: rgba(242, 201, 76, 0.25);
            border-color: rgba(242, 201, 76, 0.5);
            color: #fff;
        }
        .logout-icon {
            width: 18px;
            height: 18px;
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
    @php($showSidebar = session()->has('auth_user') && !request()->is('login'))
    <div class="{{ $showSidebar ? 'app-shell' : '' }}">
        @if ($showSidebar)
            <aside class="sidebar">
                <div class="sidebar-brand-wrap">
                    <div class="sidebar-brand">SIPEDAS</div>
                </div>
                <nav class="sidebar-nav">
                    <a href="/dashboard" class="sidebar-link">Dashboard</a>
                    <a href="/monitoring" class="sidebar-link">Monitoring</a>
                    <a href="/monitoring/laporan" class="sidebar-link">Laporan</a>
                </nav>
                <div class="sidebar-footer">
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="logout-btn" aria-label="Logout">
                            <svg class="logout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <path d="M16 17l5-5-5-5"></path>
                                <path d="M21 12H9"></path>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
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
