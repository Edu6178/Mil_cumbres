<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mil Cumbres | Experiencia Natural</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fuentes Premium: Playfair para títulos, Inter para textos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,700;1,500&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        brand: {
                            dark: '#064e3b', // Emerald 900
                            base: '#047857', // Emerald 600
                            accent: '#f59e0b', // Amber 500
                            light: '#ecfdf5', // Emerald 50
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .fade-up {
            opacity: 0;
            transform: translateY(50px);
            transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .ken-burns {
            animation: kenBurns 25s infinite alternate ease-in-out;
        }
        @keyframes kenBurns {
            0% { transform: scale(1); }
            100% { transform: scale(1.15); }
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Efecto de cristal para tarjetas */
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans overflow-x-hidden selection:bg-brand-accent selection:text-white">

    <!-- Navegación Corregida -->
    <nav class="fixed w-full z-50 transition-all duration-500 py-6" id="navbar">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <a href="#" id="logo" class="text-3xl font-serif font-bold text-white tracking-wide drop-shadow-md transition-colors">
                Mil Cumbres<span class="text-brand-accent">.</span>
            </a>
            
            <div class="hidden md:flex gap-10 items-center">
                <a href="#inicio" class="nav-link text-white/90 hover:text-brand-accent font-medium tracking-wide transition-all uppercase text-sm">Inicio</a>
                <a href="#descubre" class="nav-link text-white/90 hover:text-brand-accent font-medium tracking-wide transition-all uppercase text-sm">Descubre</a>
                <a href="#experiencias" class="nav-link text-white/90 hover:text-brand-accent font-medium tracking-wide transition-all uppercase text-sm">Experiencias</a>
                <a href="#galeria" class="nav-link text-white/90 hover:text-brand-accent font-medium tracking-wide transition-all uppercase text-sm">Galería</a>
                <a href="#reserva" class="bg-brand-accent hover:bg-amber-400 text-slate-900 px-6 py-2.5 rounded-full font-semibold transition-transform hover:scale-105 shadow-lg">Reservar</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="relative h-screen w-full overflow-hidden flex items-center">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1513553404607-988bf2703777?q=80&w=2070&auto=format&fit=crop" 
                 alt="Paisaje de montaña" 
                 class="w-full h-full object-cover ken-burns">
            <!-- Gradiente vibrante -->
            <div class="absolute inset-0 bg-gradient-to-r from-brand-dark/90 via-brand-dark/60 to-transparent"></div>
        </div>
        
        <div class="relative z-10 px-6 max-w-7xl mx-auto w-full fade-up">
            <span class="text-brand-accent font-semibold tracking-[0.2em] uppercase mb-4 block">Refugio de Altura</span>
            <h1 class="text-5xl md:text-8xl font-serif font-bold text-white mb-6 leading-tight max-w-3xl drop-shadow-xl">
                Donde la tierra <br>toca el <i class="text-brand-light font-light">cielo</i>.
            </h1>
            <p class="text-lg md:text-xl text-slate-200 mb-10 max-w-xl font-light border-l-2 border-brand-accent pl-6">
                Redescubre tu esencia en un santuario natural diseñado para inspirar, descansar y vibrar en sintonía con el bosque.
            </p>
            <div class="flex gap-4">
                <a href="#descubre" class="bg-white text-brand-dark hover:bg-brand-light px-8 py-4 rounded-full font-semibold transition-all shadow-[0_0_20px_rgba(255,255,255,0.3)]">
                    Iniciar Viaje
                </a>
                <button class="flex items-center gap-3 text-white hover:text-brand-accent transition-colors px-6 py-4">
                    <span class="w-12 h-12 rounded-full border border-white flex items-center justify-center backdrop-blur-sm">▶</span>
                    <span class="font-medium tracking-wide">Ver Video</span>
                </button>
            </div>
        </div>
    </section>

    <!-- Sección Descubre -->
    <section id="descubre" class="py-32 relative overflow-hidden bg-white">
        <!-- Decoración de fondo -->
        <div class="absolute top-0 right-0 -mr-32 -mt-32 w-96 h-96 rounded-full bg-brand-light blur-3xl opacity-60"></div>
        
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
            <div class="relative fade-up">
                <div class="grid grid-cols-2 gap-4 relative z-10">
                    <img src="https://images.unsplash.com/photo-1449156569124-6def0f5bb808?q=80&w=2071&auto=format&fit=crop" alt="Bosque" class="rounded-t-full rounded-bl-full shadow-2xl h-80 object-cover w-full">
                    <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?q=80&w=2070&auto=format&fit=crop" alt="Montañas" class="rounded-b-full rounded-tr-full shadow-2xl h-80 object-cover w-full mt-12">
                </div>
                <div class="absolute inset-0 bg-brand-base rounded-full blur-3xl opacity-10 -z-10"></div>
            </div>

            <div class="fade-up" style="transition-delay: 0.2s;">
                <span class="text-brand-base font-semibold tracking-widest uppercase mb-2 block">Nuestra Esencia</span>
                <h2 class="text-4xl md:text-5xl font-serif font-bold text-slate-900 mb-8 leading-tight">
                    Arquitectura que respeta el entorno.
                </h2>
                <p class="text-slate-600 text-lg leading-relaxed mb-8">
                    Mil Cumbres no es solo un destino, es una inmersión total. Nuestras instalaciones están construidas con materiales nobles que se mimetizan con el bosque de pinos, ofreciendo un lujo silencioso que cede el protagonismo a la majestuosidad del paisaje.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-12">
                    <div class="flex gap-4 items-start">
                        <div class="w-12 h-12 rounded-xl bg-brand-light text-brand-base flex items-center justify-center shrink-0 text-xl shadow-sm">🌿</div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-lg">100% Sustentable</h4>
                            <p class="text-sm text-slate-500 mt-1">Energía limpia y recolección pluvial.</p>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-brand-accent flex items-center justify-center shrink-0 text-xl shadow-sm">☕</div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-lg">Gastronomía Local</h4>
                            <p class="text-sm text-slate-500 mt-1">Ingredientes orgánicos de la región.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nueva Sección: Experiencias (Vibrante) -->
    <section id="experiencias" class="py-32 bg-brand-dark relative">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white via-transparent to-transparent"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="text-center mb-20 fade-up">
                <h2 class="text-4xl md:text-6xl font-serif font-bold text-white mb-6">Siente la montaña</h2>
                <p class="text-brand-light text-lg max-w-2xl mx-auto font-light">Diseñamos momentos que aceleran tu pulso y pacifican tu mente.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="group cursor-pointer fade-up">
                    <div class="relative h-[450px] rounded-3xl overflow-hidden shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1551632811-561732d1e306?q=80&w=2070&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>
                        <div class="absolute bottom-0 left-0 p-8 w-full">
                            <span class="text-brand-accent font-bold uppercase tracking-wider text-xs mb-2 block">Aventura</span>
                            <h3 class="text-2xl font-serif font-bold text-white mb-3">Rutas de Senderismo</h3>
                            <p class="text-slate-300 text-sm h-0 opacity-0 group-hover:h-auto group-hover:opacity-100 transition-all duration-500">Explora más de 15km de senderos marcados atravesando cascadas ocultas y miradores.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Card 2 -->
                <div class="group cursor-pointer fade-up" style="transition-delay: 0.2s;">
                    <div class="relative h-[450px] rounded-3xl overflow-hidden shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1510798831971-661eb04b3739?q=80&w=1974&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>
                        <div class="absolute bottom-0 left-0 p-8 w-full">
                            <span class="text-brand-accent font-bold uppercase tracking-wider text-xs mb-2 block">Relajación</span>
                            <h3 class="text-2xl font-serif font-bold text-white mb-3">Glamping & Spa</h3>
                            <p class="text-slate-300 text-sm h-0 opacity-0 group-hover:h-auto group-hover:opacity-100 transition-all duration-500">Masajes con piedras volcánicas y cabañas con tina de hidromasaje al aire libre.</p>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="group cursor-pointer fade-up" style="transition-delay: 0.4s;">
                    <div class="relative h-[450px] rounded-3xl overflow-hidden shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1504280387968-077fae9b3a77?q=80&w=2070&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>
                        <div class="absolute bottom-0 left-0 p-8 w-full">
                            <span class="text-brand-accent font-bold uppercase tracking-wider text-xs mb-2 block">Conexión</span>
                            <h3 class="text-2xl font-serif font-bold text-white mb-3">Fogatas Nocturnas</h3>
                            <p class="text-slate-300 text-sm h-0 opacity-0 group-hover:h-auto group-hover:opacity-100 transition-all duration-500">Observación astronómica acompañada de vino caliente y música acústica.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Galería Horizontal Dinámica -->
    <section id="galeria" class="py-32 bg-slate-100">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-end mb-16 fade-up">
                <div>
                    <h2 class="text-4xl md:text-5xl font-serif font-bold text-slate-900 mb-4">Un vistazo al paraíso</h2>
                    <p class="text-slate-600 text-lg">Espacios diseñados para tu desconexión total.</p>
                </div>
                <div class="hidden md:flex gap-4">
                    <button id="prevBtn" class="w-14 h-14 rounded-full border-2 border-slate-300 flex items-center justify-center text-slate-600 hover:bg-brand-base hover:border-brand-base hover:text-white transition-all shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    <button id="nextBtn" class="w-14 h-14 rounded-full border-2 border-slate-300 flex items-center justify-center text-slate-600 hover:bg-brand-base hover:border-brand-base hover:text-white transition-all shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Carrusel -->
            <div class="relative w-full overflow-hidden fade-up" style="transition-delay: 0.2s;">
                <div id="carouselTrack" class="flex gap-6 overflow-x-auto no-scrollbar scroll-smooth pb-12 pt-4 px-4 -mx-4 snap-x snap-mandatory">
                    <div class="min-w-[85vw] md:min-w-[50vw] lg:min-w-[35vw] h-[500px] relative rounded-2xl overflow-hidden snap-center group shadow-xl">
                        <img src="https://images.unsplash.com/photo-1542224566-6e85f2e6772f?q=80&w=1976&auto=format&fit=crop" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                    </div>
                    <div class="min-w-[85vw] md:min-w-[50vw] lg:min-w-[35vw] h-[500px] relative rounded-2xl overflow-hidden snap-center group shadow-xl">
                        <img src="https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?q=80&w=2070&auto=format&fit=crop" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                    </div>
                    <div class="min-w-[85vw] md:min-w-[50vw] lg:min-w-[35vw] h-[500px] relative rounded-2xl overflow-hidden snap-center group shadow-xl">
                        <img src="https://images.unsplash.com/photo-1521401830884-6c03c1c87ebb?q=80&w=2070&auto=format&fit=crop" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                    </div>
                    <div class="min-w-[85vw] md:min-w-[50vw] lg:min-w-[35vw] h-[500px] relative rounded-2xl overflow-hidden snap-center group shadow-xl">
                        <img src="https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?q=80&w=2071&auto=format&fit=crop" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Banner Cierre -->
    <section class="py-24 bg-brand-base relative overflow-hidden">
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=2070&auto=format&fit=crop" class="w-full h-full object-cover opacity-20 mix-blend-overlay">
        </div>
        <div class="relative z-10 max-w-4xl mx-auto px-6 text-center fade-up">
            <h2 class="text-4xl md:text-6xl font-serif font-bold text-white mb-8">¿Listo para elevarte?</h2>
            <p class="text-brand-light text-xl mb-10">Reserva tu estancia hoy y obtén un tour de senderismo guiado en cortesía.</p>
            <a href="#reserva" class="inline-block bg-white text-brand-dark px-10 py-5 rounded-full text-lg font-bold hover:bg-brand-accent hover:text-white transition-all shadow-2xl hover:-translate-y-1">
                Ver Disponibilidad
            </a>
        </div>
    </section>

    <!-- Footer Completo -->
    <footer class="bg-slate-950 text-slate-400 py-20 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12">
            <div class="col-span-1 md:col-span-2">
                <a href="#" class="text-3xl font-serif font-bold text-white mb-6 inline-block">Mil Cumbres<span class="text-brand-accent">.</span></a>
                <p class="max-w-sm mb-8">Un refugio exclusivo en el corazón del bosque. Tu escape perfecto para reconectar con la naturaleza.</p>
                <div class="flex gap-4">
                    <!-- Redes sociales dummy -->
                    <div class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center hover:bg-brand-accent hover:text-white cursor-pointer transition-colors">IN</div>
                    <div class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center hover:bg-brand-accent hover:text-white cursor-pointer transition-colors">FB</div>
                    <div class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center hover:bg-brand-accent hover:text-white cursor-pointer transition-colors">IG</div>
                </div>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Explora</h4>
                <ul class="space-y-4">
                    <li><a href="#" class="hover:text-brand-accent transition-colors">Nuestras Cabañas</a></li>
                    <li><a href="#" class="hover:text-brand-accent transition-colors">Gastronomía</a></li>
                    <li><a href="#" class="hover:text-brand-accent transition-colors">Spa & Relax</a></li>
                    <li><a href="#" class="hover:text-brand-accent transition-colors">Ubicación</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Contacto</h4>
                <ul class="space-y-4">
                    <li>reservas@milcumbres.com</li>
                    <li>+52 (55) 1234 5678</li>
                    <li>Carretera Federal Km 45, Bosque Alto.</li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 mt-16 pt-8 border-t border-slate-900 text-sm text-center md:text-left flex flex-col md:flex-row justify-between">
            <p>&copy; 2026 Mil Cumbres. Todos los derechos reservados.</p>
            <div class="flex gap-6 mt-4 md:mt-0 justify-center">
                <a href="#" class="hover:text-white">Privacidad</a>
                <a href="#" class="hover:text-white">Términos</a>
            </div>
        </div>
    </footer>

    <script>
        // 1. Corrección del Navbar para visibilidad y contraste
        const navbar = document.getElementById('navbar');
        const navLinks = document.querySelectorAll('.nav-link');
        const logo = document.getElementById('logo');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                // Fondo blanco con desenfoque
                navbar.classList.add('bg-white/95', 'backdrop-blur-md', 'shadow-lg', 'py-4');
                navbar.classList.remove('py-6');
                
                // Texto oscuro para el logo
                logo.classList.replace('text-white', 'text-brand-dark');
                
                // Texto oscuro para los enlaces (corrigiendo el error de la imagen)
                navLinks.forEach(link => {
                    link.classList.remove('text-white/90');
                    link.classList.add('text-slate-700');
                });
            } else {
                // Estado transparente inicial
                navbar.classList.remove('bg-white/95', 'backdrop-blur-md', 'shadow-lg', 'py-4');
                navbar.classList.add('py-6');
                
                logo.classList.replace('text-brand-dark', 'text-white');
                
                navLinks.forEach(link => {
                    link.classList.add('text-white/90');
                    link.classList.remove('text-slate-700');
                });
            }
        });

        // 2. Animaciones al scroll fluidas
        const observerOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

        // 3. Controles del carrusel de galería
        const track = document.getElementById('carouselTrack');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        if(prevBtn && nextBtn && track) {
            const getScrollAmount = () => track.firstElementChild.getBoundingClientRect().width + 24;
            nextBtn.addEventListener('click', () => track.scrollBy({ left: getScrollAmount(), behavior: 'smooth' }));
            prevBtn.addEventListener('click', () => track.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' }));
        }
    </script>
</body>
</html>