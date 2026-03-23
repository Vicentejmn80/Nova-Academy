<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVA ACADEMY · IA Educativa para Profesores</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* Design System Nova - Colores del Hub */
        :root {
            --nova-deep: #0A0A1F;
            --nova-dark: #12122B;
            --nova-medium: #1E1A3A;
            --nova-light: #2D1F4A;
            --nova-violet: #6C4AE0;
            --nova-fuchsia: #C455ED;
            --nova-cyan: #3BC9DB;
            --nova-success: #22C55E;
            --nova-warning: #F59E0B;
            --nova-gradient: linear-gradient(135deg, #6C4AE0 0%, #C455ED 70%, #3BC9DB 100%);
            --nova-glass: rgba(255, 255, 255, 0.03);
            --nova-glass-border: rgba(108, 74, 224, 0.15);
            
            --text-primary: rgba(255, 255, 255, 1);
            --text-secondary: rgba(255, 255, 255, 0.7);
            --text-tertiary: rgba(255, 255, 255, 0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--nova-deep);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* Fondo dinámico Nova - Igual al hub */
        .nova-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            overflow: hidden;
        }

        .nova-bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: glow-pulse 8s ease-in-out infinite;
        }

        .nova-bg-orb:nth-child(1) {
            top: -10%;
            left: -5%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(108, 74, 224, 0.4) 0%, transparent 70%);
        }

        .nova-bg-orb:nth-child(2) {
            bottom: -10%;
            right: -5%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(196, 85, 237, 0.3) 0%, transparent 70%);
            animation-delay: 2s;
        }

        .nova-bg-orb:nth-child(3) {
            top: 40%;
            right: 20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(59, 201, 219, 0.2) 0%, transparent 70%);
            animation-delay: 4s;
        }

        .nova-grid {
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(var(--nova-glass-border) 1px, transparent 1px),
                linear-gradient(90deg, var(--nova-glass-border) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.3;
        }

        @keyframes glow-pulse {
            0%, 100% { opacity: 0.3; filter: blur(80px); }
            50% { opacity: 0.7; filter: blur(100px); }
        }

        .welcome-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 2;
        }

        /* Header - Actualizado */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            margin-bottom: 3rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: var(--nova-gradient);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px -5px var(--nova-violet);
        }

        .logo-icon::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .logo:hover .logo-icon::before {
            transform: translateX(100%);
        }

        .logo-icon i {
            font-size: 24px;
            color: white;
            position: relative;
            z-index: 2;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 800;
            background: var(--nova-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2.5rem;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--nova-gradient);
            transition: width 0.3s;
        }

        .nav-links a:hover {
            color: white;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a.highlight {
            background: var(--nova-glass);
            border: 1px solid var(--nova-glass-border);
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            color: white;
        }

        .nav-links a.highlight:hover {
            background: var(--nova-gradient);
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -8px var(--nova-violet);
        }

        /* Hero Section */
        .hero {
            display: flex;
            align-items: center;
            gap: 4rem;
            padding: 2rem 0 4rem;
        }

        .hero-content {
            flex: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--nova-glass);
            border: 1px solid var(--nova-glass-border);
            border-radius: 30px;
            padding: 0.5rem 1rem;
            margin-bottom: 2rem;
            font-size: 0.85rem;
            color: var(--nova-cyan);
        }

        .hero-badge i {
            font-size: 0.75rem;
        }

        .hero-title {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, white 0%, var(--nova-cyan) 50%, var(--nova-fuchsia) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            max-width: 550px;
            line-height: 1.6;
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
            margin: 2.5rem 0;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, white, var(--nova-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-tertiary);
            margin-top: 0.5rem;
        }

        .hero-cta {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .btn-primary {
            background: var(--nova-gradient);
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .btn-primary:hover::before {
            transform: translateX(100%);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 30px -10px var(--nova-violet);
        }

        .btn-secondary {
            background: var(--nova-glass);
            border: 1px solid var(--nova-glass-border);
            padding: 1rem 2rem;
            border-radius: 40px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: var(--nova-glass);
            border-color: var(--nova-violet);
            transform: translateY(-3px);
        }

        .hero-image {
            flex: 1;
            position: relative;
        }

        .hero-image img {
            width: 100%;
            border-radius: 30px;
            box-shadow: 0 30px 60px -15px rgba(108, 74, 224, 0.4);
            border: 1px solid var(--nova-glass-border);
        }

        .floating-card {
            position: absolute;
            bottom: -20px;
            left: -20px;
            background: var(--nova-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--nova-glass-border);
            border-radius: 20px;
            padding: 1.2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: float 6s ease-in-out infinite;
        }

        .floating-card i {
            font-size: 2rem;
            color: var(--nova-cyan);
        }

        .floating-card p {
            font-size: 0.9rem;
            color: white;
        }

        .floating-card strong {
            color: var(--nova-fuchsia);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Features Section */
        .features {
            padding: 5rem 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, white, var(--nova-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .section-header p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            background: var(--nova-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--nova-glass-border);
            border-radius: 30px;
            padding: 2.5rem 2rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--nova-gradient);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .feature-card:hover::before {
            transform: translateX(100%);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: var(--nova-violet);
            box-shadow: 0 20px 40px -15px var(--nova-violet);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--nova-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }

        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Pricing - Igual al estilo del hub */
        .pricing {
            padding: 5rem 0;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .pricing-card {
            background: var(--nova-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--nova-glass-border);
            border-radius: 30px;
            padding: 2.5rem 2rem;
            position: relative;
            transition: all 0.3s;
        }

        .pricing-card.popular {
            transform: scale(1.05);
            border-color: var(--nova-violet);
            background: linear-gradient(135deg, rgba(108,74,224,0.1), rgba(196,85,237,0.1));
        }

        .popular-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--nova-gradient);
            color: white;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.3rem 1rem;
            border-radius: 30px;
            white-space: nowrap;
        }

        .pricing-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .pricing-price {
            margin-bottom: 2rem;
        }

        .price {
            font-size: 3rem;
            font-weight: 800;
            color: white;
        }

        .price span {
            font-size: 1rem;
            color: var(--text-tertiary);
            font-weight: 400;
        }

        .pricing-features {
            list-style: none;
            margin-bottom: 2rem;
        }

        .pricing-features li {
            color: var(--text-secondary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .pricing-features i.fa-check {
            color: var(--nova-success);
        }

        .pricing-features i.fa-times {
            color: var(--text-tertiary);
        }

        .pricing-card .btn-secondary {
            width: 100%;
            justify-content: center;
        }

        /* Footer */
        .footer {
            padding: 4rem 0 2rem;
            border-top: 1px solid var(--nova-glass-border);
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-brand p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin: 1rem 0;
            line-height: 1.6;
        }

        .footer-links h4 {
            color: white;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: var(--nova-cyan);
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 2rem;
            border-top: 1px solid var(--nova-glass-border);
            color: var(--text-tertiary);
            font-size: 0.9rem;
        }

        .social-links {
            display: flex;
            gap: 1.5rem;
        }

        .social-links a {
            color: var(--text-tertiary);
            transition: color 0.3s;
        }

        .social-links a:hover {
            color: var(--nova-cyan);
        }

        @media (max-width: 1024px) {
            .hero {
                flex-direction: column;
                text-align: center;
            }
            
            .hero-subtitle {
                margin: 0 auto 2rem;
            }
            
            .hero-stats {
                justify-content: center;
            }
            
            .hero-cta {
                justify-content: center;
            }
            
            .features-grid,
            .pricing-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .features-grid,
            .pricing-grid {
                grid-template-columns: 1fr;
            }
            
            .pricing-card.popular {
                transform: scale(1);
            }
            
            .footer-content {
                grid-template-columns: 1fr;
            }
            
            .footer-bottom {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Fondo dinámico Nova (igual al hub) -->
    <div class="nova-bg">
        <div class="nova-bg-orb"></div>
        <div class="nova-bg-orb"></div>
        <div class="nova-bg-orb"></div>
        <div class="nova-grid"></div>
    </div>

    <div class="welcome-container">
        <!-- Header -->
        <header class="header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <span class="logo-text">NOVA ACADEMY</span>
            </div>
            <nav class="nav-links">
                <a href="#features">Características</a>
                <a href="#pricing">Precios</a>
                <a href="{{ route('login') }}" class="highlight">
                    <i class="fa-regular fa-circle-user"></i> Iniciar Sesión
                </a>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fa-regular fa-star"></i>
                    <span>El futuro de la planificación educativa</span>
                </div>
                <h1 class="hero-title">
                    Planifica un Mes de Clases <br>en 5 Minutos
                </h1>
                <p class="hero-subtitle">
                    Deja atrás las horas de planificación. Nova transforma tus ideas en planificaciones completas, 
                    personalizadas y listas para usar con inteligencia artificial especializada en educación.
                </p>
                
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">+15k</span>
                        <span class="stat-label">Profesores</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">50k+</span>
                        <span class="stat-label">Planificaciones</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Satisfacción</span>
                    </div>
                </div>

                <div class="hero-cta">
                    <a href="{{ route('register') }}" class="btn-primary">
                        <i class="fa-regular fa-rocket"></i>
                        Comenzar Gratis
                    </a>
                    <a href="#demo" class="btn-secondary">
                        <i class="fa-regular fa-circle-play"></i>
                        Ver Demo
                    </a>
                </div>
            </div>

            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" alt="Nova Academy Platform">
                <div class="floating-card">
                    <i class="fa-regular fa-clock"></i>
                    <p><strong>+15 horas ahorradas</strong> esta semana</p>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features" id="features">
            <div class="section-header">
                <h2>Todo lo que necesitas en un solo lugar</h2>
                <p>Una plataforma completa potenciada por inteligencia artificial para educadores modernos</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-regular fa-bolt"></i>
                    </div>
                    <h3>5x Más Rápido</h3>
                    <p>Reduce el tiempo de planificación de horas a minutos con nuestra IA especializada en educación.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-regular fa-magic"></i>
                    </div>
                    <h3>IA Especializada</h3>
                    <p>Algoritmos entrenados específicamente con miles de planificaciones y estándares educativos.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-regular fa-graduation-cap"></i>
                    </div>
                    <h3>Adaptado a Ti</h3>
                    <p>Se ajusta automáticamente a tu estilo de enseñanza, materia y nivel educativo.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-regular fa-calendar"></i>
                    </div>
                    <h3>Calendario Inteligente</h3>
                    <p>Visualiza todas tus actividades y planificaciones en un calendario interactivo.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-regular fa-users"></i>
                    </div>
                    <h3>Gestión de Alumnos</h3>
                    <p>Administra cursos, estudiantes y calificaciones desde un solo panel.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa-regular fa-file-lines"></i>
                    </div>
                    <h3>Exportación Profesional</h3>
                    <p>Exporta tus planificaciones a PDF, Word o Google Docs con un clic.</p>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section class="pricing" id="pricing">
            <div class="section-header">
                <h2>Planes para cada necesidad</h2>
                <p>Desde profesores individuales hasta instituciones educativas completas</p>
            </div>

            <div class="pricing-grid">
                <div class="pricing-card">
                    <h3>Gratis</h3>
                    <div class="pricing-price">
                        <span class="price">0€ <span>/mes</span></span>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fa-regular fa-check"></i> 10 planificaciones/mes</li>
                        <li><i class="fa-regular fa-check"></i> IA Básica</li>
                        <li><i class="fa-regular fa-check"></i> 1 curso activo</li>
                        <li><i class="fa-regular fa-times"></i> Exportación limitada</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn-secondary">Comenzar</a>
                </div>

                <div class="pricing-card popular">
                    <div class="popular-badge">MÁS POPULAR</div>
                    <h3>Pro</h3>
                    <div class="pricing-price">
                        <span class="price">9.99€ <span>/mes</span></span>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fa-regular fa-check"></i> Planificaciones ilimitadas</li>
                        <li><i class="fa-regular fa-check"></i> IA Avanzada</li>
                        <li><i class="fa-regular fa-check"></i> Cursos ilimitados</li>
                        <li><i class="fa-regular fa-check"></i> Exportación completa</li>
                        <li><i class="fa-regular fa-check"></i> Soporte prioritario</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn-primary">Elegir Pro</a>
                </div>

                <div class="pricing-card">
                    <h3>Institucional</h3>
                    <div class="pricing-price">
                        <span class="price">Personalizado</span>
                    </div>
                    <ul class="pricing-features">
                        <li><i class="fa-regular fa-check"></i> Para colegios/universidades</li>
                        <li><i class="fa-regular fa-check"></i> Dashboard compartido</li>
                        <li><i class="fa-regular fa-check"></i> API acceso</li>
                        <li><i class="fa-regular fa-check"></i> Formación incluida</li>
                        <li><i class="fa-regular fa-check"></i> Soporte 24/7</li>
                    </ul>
                    <a href="#contacto" class="btn-secondary">Contactar</a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="logo">
                        <div class="logo-icon" style="width: 40px; height: 40px;">
                            <i class="fa-solid fa-robot" style="font-size: 20px;"></i>
                        </div>
                        <span class="logo-text" style="font-size: 1.4rem;">NOVA ACADEMY</span>
                    </div>
                    <p>Transformando la educación con inteligencia artificial. Más tiempo para enseñar, menos para planificar.</p>
                </div>
                <div class="footer-links">
                    <h4>Producto</h4>
                    <ul>
                        <li><a href="#features">Características</a></li>
                        <li><a href="#pricing">Precios</a></li>
                        <li><a href="#demo">Demo</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Recursos</h4>
                    <ul>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Guías</a></li>
                        <li><a href="#">Soporte</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Términos</a></li>
                        <li><a href="#">Privacidad</a></li>
                        <li><a href="#">Cookies</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2026 NOVA ACADEMY. Todos los derechos reservados.</p>
                <div class="social-links">
                    <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>