<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>JejakKarbonmu - Carbon Footprint Calculator</title>
    
    <!-- Meta SEO -->
    <meta name="description" content="Calculate and monitor your Scope 1, Scope 2, and Scope 3 greenhouse gas emissions with JejakKarbonmu. Compatible with GHG Protocol, IPCC, and Indonesia National standards.">
    
    <!-- Google Fonts: Outfit (Headings) & Inter (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom Premium Styles -->
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            
            --scope1: #10b981;
            --scope2: #3b82f6;
            --scope3: #a855f7;
            
            --scope1-bg: rgba(16, 185, 129, 0.1);
            --scope2-bg: rgba(59, 130, 246, 0.1);
            --scope3-bg: rgba(168, 85, 247, 0.1);
        }
        
        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
        }
        
        /* Navbar Glassmorphism */
        .navbar-custom {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #10b981 0%, #3b82f6 50%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-link-custom {
            color: var(--text-muted);
            font-weight: 500;
            transition: color 0.3s ease;
            margin-left: 1.5rem;
            text-decoration: none;
        }
        
        .nav-link-custom:hover, .nav-link-custom.active {
            color: var(--text-main);
        }
        
        /* Custom Cards */
        .glass-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border-color: #475569;
        }
        
        /* Custom Buttons */
        .btn-premium {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5);
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
        }
        
        .btn-premium-outline {
            background: transparent;
            color: var(--text-main);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 8px 22px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-premium-outline:hover {
            border-color: var(--text-main);
            transform: translateY(-2px);
        }
        
        /* Scope Accents */
        .badge-scope1 { background-color: var(--scope1-bg); color: var(--scope1); border: 1px solid var(--scope1); }
        .badge-scope2 { background-color: var(--scope2-bg); color: var(--scope2); border: 1px solid var(--scope2); }
        .badge-scope3 { background-color: var(--scope3-bg); color: var(--scope3); border: 1px solid var(--scope3); }
        
        .border-scope1 { border-left: 5px solid var(--scope1) !important; }
        .border-scope2 { border-left: 5px solid var(--scope2) !important; }
        .border-scope3 { border-left: 5px solid var(--scope3) !important; }
        
        /* Footer */
        footer {
            margin-top: auto;
            border-top: 1px solid var(--border-color);
            background-color: #0b0f19;
            padding: 2rem 0;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        /* Custom Inputs */
        .form-control-custom {
            background-color: #0f172a;
            border: 1px solid var(--border-color);
            color: var(--text-main) !important;
            border-radius: 10px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }
        
        .form-control-custom:focus {
            background-color: #0f172a;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }
        
        .form-select-custom {
            background-color: #0f172a;
            border: 1px solid var(--border-color);
            color: var(--text-main) !important;
            border-radius: 10px;
            padding: 12px 16px;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
            transition: all 0.3s ease;
            appearance: none;
        }
        
        .form-select-custom:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        
        /* Micro-animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animated-fade {
            animation: fadeIn 0.5s ease forwards;
        }
        
        /* Alert customization */
        .alert-premium-success {
            background-color: var(--scope1-bg);
            border: 1px solid var(--scope1);
            color: var(--scope1);
            border-radius: 12px;
        }
        
        .alert-premium-danger {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #f87171;
            border-radius: 12px;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Glassmorphic Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fa-solid fa-leaf me-2"></i>JejakKarbonmu
            </a>
            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="navbar-nav align-items-center">
                    <a class="nav-link-custom {{ Route::is('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    <a class="nav-link-custom {{ Route::is('records.create') ? 'active' : '' }}" href="{{ route('records.create') }}">Calculator</a>
                    <a class="nav-link-custom {{ Route::is('records.history') || Route::is('records.show') ? 'active' : '' }}" href="{{ route('records.history') }}">Historical Records</a>
                    <a class="btn-premium nav-link-custom text-white ms-lg-4 mt-3 mt-lg-0" href="{{ route('records.create') }}">
                        <i class="fa-solid fa-calculator me-2"></i>Calculate
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="py-5">
        <div class="container">
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-premium-success alert-dismissible fade show animated-fade mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-circle-check me-2 fs-5"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-premium-danger alert-dismissible fade show animated-fade mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-2"><i class="fa-solid fa-leaf text-success me-2"></i><strong>JejakKarbonmu</strong> &copy; {{ date('Y') }}</p>
            <p class="mb-0 text-secondary" style="font-size: 0.8rem;">Designed for accurate carbon reporting & Tableau dashboard integration according to GHG Protocol standards.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
