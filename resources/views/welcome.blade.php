<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#193e34">
    <meta name="description" content="Mil Cumbres: naturaleza, comunidades y turismo regenerativo. Conoce nuestra propuesta de experiencias y consultoría.">
    <title>Mil Cumbres · Viajar para reconectar</title>
    <link rel="icon" href="{{ asset('imagenes/simbolo.svg') }}" type="image/svg+xml">
    {{-- El teléfono descarga una portada más ligera; las demás imágenes se cargan al acercarse a su sección. --}}
    <link rel="preload" as="image" href="{{ asset('imagenes/bosque-portada.webp') }}" media="(min-width: 651px)" fetchpriority="high">
    <link rel="preload" as="image" href="{{ asset('imagenes/bosque-portada-movil.webp') }}" media="(max-width: 650px)" fetchpriority="high">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="saltar-contenido" href="#contenido">Saltar al contenido</a>

    {{-- La navegación lleva a apartados de esta misma página; no requiere conexión a la base de datos. --}}
    <header class="cabecera" id="cabecera">
        <div class="contenedor cabecera-interior">
            <a class="marca" href="#inicio" aria-label="Mil Cumbres, inicio">
                <img src="{{ asset('imagenes/simbolo.svg') }}" alt="" width="47" height="47">
                <span>Mil Cumbres<small>Turismo regenerativo</small></span>
            </a>
            <button class="boton-menu" type="button" aria-expanded="false" aria-controls="navegacion-principal" aria-label="Abrir menú de navegación">
                <x-icono nombre="menu" /><span>Menú</span>
            </button>
            <nav class="navegacion" id="navegacion-principal" aria-label="Navegación principal">
                <a href="#experiencias">Experiencias</a>
                <a href="#esencia">Nuestra esencia</a>
                <a href="#comunidades">Comunidades</a>
                <a href="#guias">Guías</a>
                <a class="enlace-acceso" href="{{ route('cuenta') }}"><x-icono nombre="usuario" /> Mi cuenta</a>
                <a class="boton boton-pequeno boton-bosque" href="#organizaciones">Para organizaciones <x-icono nombre="flecha-diagonal" /></a>
            </nav>
        </div>
    </header>

    <main id="contenido">
        <section class="portada" id="inicio" aria-labelledby="titulo-portada">
            {{-- Las fotografías son referencias visuales; no representan servicios o sedes ya publicados. --}}
            <picture>
                <source media="(max-width: 650px)" srcset="{{ asset('imagenes/bosque-portada-movil.webp') }}">
                <img class="portada-fondo" src="{{ asset('imagenes/bosque-portada.webp') }}" alt="Luz del sol entre los árboles de un bosque" width="1600" height="1066" fetchpriority="high">
            </picture>
            <div class="portada-sombra"></div>
            <div class="contenedor portada-interior">
                <div class="portada-texto">
                    <p class="antetitulo antetitulo-claro"><span></span> Naturaleza. Comunidad. Propósito.</p>
                    <h1 id="titulo-portada">Volver a la tierra.<br>Conectar con<br><em>lo que importa.</em></h1>
                    <p class="portada-descripcion">Hay viajes que te llevan a un lugar.<br>Y otros que te acercan a una nueva forma de verlo.</p>
                    <a class="boton boton-tierra" href="#experiencias">Descubre Mil Cumbres <x-icono nombre="flecha" /></a>
                </div>
                <div class="portada-pie">
                    <p><span>Una manera distinta de viajar</span>Operación turística y consultoría con sentido.</p>
                    <a href="#esencia" class="enlace-bajar"><span>El camino empieza aquí</span><x-icono nombre="abajo" /></a>
                </div>
                <div class="sello-portada" aria-hidden="true"><x-icono nombre="montana" /><span>Menos prisa.<br>Más conexión.</span></div>
            </div>
        </section>

        <div class="franja-valores">
            <div class="contenedor valores-interior">
                <p><x-icono nombre="hoja" /> Naturaleza que inspira</p><span class="separador" aria-hidden="true"></span>
                <p><x-icono nombre="personas" /> Comunidades que nos conectan</p><span class="separador" aria-hidden="true"></span>
                <p><x-icono nombre="brujula" /> Experiencias con propósito</p>
            </div>
        </div>

        <section class="seccion esencia" id="esencia" aria-labelledby="titulo-esencia">
            <div class="contenedor esencia-cuadricula">
                <div><p class="antetitulo"><span></span> Nuestra esencia</p><h2 id="titulo-esencia">Viajar también<br>puede ser una<br><em>forma de cuidar.</em></h2></div>
                <div class="esencia-contenido">
                    <p class="texto-destacado">Un encuentro con la naturaleza. Una conversación con el territorio. Una conexión que continúa después del viaje.</p>
                    <p>En Mil Cumbres, el turismo regenerativo es el punto de partida para pensar nuestra relación con los lugares que visitamos y con quienes los habitan.</p>
                    <div class="vertientes">
                        <article><span class="numero-seccion">01 / Explorar</span><h3>Operadora turística</h3><p>Experiencias para acercarnos a la naturaleza y mirar el entorno con otros ojos.</p><a class="enlace-texto" href="#experiencias">Conocer la propuesta <x-icono nombre="flecha" /></a></article>
                        <article><span class="numero-seccion">02 / Acompañar</span><h3>Consultoría</h3><p>Un espacio para impulsar proyectos junto a organizaciones y comunidades.</p><a class="enlace-texto" href="#organizaciones">Explorar esta vertiente <x-icono nombre="flecha" /></a></article>
                    </div>
                </div>
            </div>
        </section>

        <section class="seccion lineas" id="propuesta" aria-labelledby="titulo-lineas">
            <div class="contenedor lineas-cuadricula">
                <figure class="imagen-senderismo">
                    <img src="{{ asset('imagenes/senderismo.webp') }}" alt="Personas recorriendo un sendero de montaña" width="1000" height="1333" loading="lazy" decoding="async">
                    <div class="etiqueta-imagen"><x-icono nombre="ruta" /><span>El paisaje se descubre<br><strong>paso a paso.</strong></span></div>
                    <figcaption>Una mirada a lo que nos mueve · Imagen de referencia</figcaption>
                </figure>
                <div class="lineas-contenido">
                    <p class="antetitulo"><span></span> Nuestra propuesta</p><h2 id="titulo-lineas">Cinco caminos.<br><em>Un mismo propósito.</em></h2>
                    <p class="introduccion-lineas">Distintas formas de encontrarnos con la naturaleza. Una propuesta que sigue tomando forma.</p>
                    <div class="lista-lineas">
                        <details class="linea-servicio" open>
                            <summary><span class="numero-linea">01</span><span>Senderismo</span><x-icono nombre="mas" /></summary>
                            <div class="detalle-linea"><p>Caminar, observar y reconectar con el entorno. Aquí presentaremos las distintas rutas, sus recorridos y la información para elegir tu experiencia.</p><a class="enlace-texto" href="#experiencias" data-ver-senderismo>Explorar las rutas <x-icono nombre="flecha" /></a></div>
                        </details>
                        {{-- Son espacios de presentación, no registros del catálogo; los otros cuatro nombres están pendientes. --}}
                        @foreach (['02', '03', '04', '05'] as $numero)
                            <div class="linea-pendiente"><span class="numero-linea">{{ $numero }}</span><span>Línea por confirmar</span><span class="etiqueta-pendiente">Próximamente</span></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="seccion experiencias" id="experiencias" aria-labelledby="titulo-experiencias">
            <div class="contenedor">
                <div class="encabezado-seccion">
                    <div><p class="antetitulo"><span></span> Explora a tu ritmo</p><h2 id="titulo-experiencias">Tu próxima historia,<br><em>al aire libre.</em></h2></div>
                    <p>Encuentra un motivo para salir de la rutina<br class="solo-escritorio"> y volver a mirar lo que te rodea.</p>
                </div>
                <div class="catalogo-cuadricula">
                    <div class="catalogo-panel">
                        <div class="pestanas" role="tablist" aria-label="Tipo de experiencia">
                            <button id="pestana-experiencias" type="button" role="tab" aria-selected="true" aria-controls="catalogo-contenido" data-catalogo="experiencias">Todas las experiencias</button>
                            <button id="pestana-senderismo" type="button" role="tab" aria-selected="false" aria-controls="catalogo-contenido" tabindex="-1" data-catalogo="senderismo"><x-icono nombre="ruta" /> Rutas de senderismo</button>
                        </div>
                        {{-- El catálogo permanece vacío: no se inventan servicios, precios ni disponibilidad. --}}
                        <div class="catalogo-vacio" id="catalogo-contenido" role="tabpanel" aria-labelledby="pestana-experiencias" tabindex="0">
                            <div class="paisaje-vacio" aria-hidden="true"><x-icono nombre="paisaje" /></div>
                            <span class="estado-publicacion"><span></span> Catálogo en preparación</span>
                            <h3 id="titulo-catalogo-vacio">Las mejores historias<br>necesitan un buen comienzo.</h3>
                            <p id="descripcion-catalogo-vacio" aria-live="polite">Todavía no hay experiencias publicadas. Aquí encontrarás los recorridos, itinerarios y detalles de cada servicio cuando estén disponibles.</p>
                            <a class="enlace-texto" href="#propuesta">Mientras tanto, conoce nuestra propuesta <x-icono nombre="flecha" /></a>
                        </div>
                    </div>
                    <aside class="proximas-salidas" aria-labelledby="titulo-salidas">
                        <div class="salidas-icono"><x-icono nombre="calendario" /></div><span class="numero-seccion">Encuentra tu momento</span>
                        <h3 id="titulo-salidas">Próximas<br><em>salidas.</em></h3><p>Los buenos encuentros también tienen su momento.</p>
                        <div class="salidas-vacio"><span class="punto-estado"></span><p>Aún no hay fechas publicadas.</p></div>
                        <p class="salidas-nota">Cuando se publiquen actividades, podrás conocer sus fechas, horarios y cupos aquí.</p>
                        <span class="etiqueta-proximamente">Calendario próximamente <x-icono nombre="sol" /></span>
                    </aside>
                </div>
            </div>
        </section>

        <section class="comunidades" id="comunidades" aria-labelledby="titulo-comunidades">
            <img class="comunidades-fondo" src="{{ asset('imagenes/bosque-comunidades.webp') }}" alt="Árboles y vegetación de un bosque" width="1100" height="733" loading="lazy" decoding="async"><div class="comunidades-sombra"></div>
            <div class="contenedor comunidades-interior">
                <div><p class="antetitulo antetitulo-claro"><span></span> Territorio y comunidad</p><h2 id="titulo-comunidades">Detrás de cada paisaje,<br><em>hay una historia.</em></h2></div>
                <div class="comunidades-texto">
                    <p>Conocer un lugar también significa acercarse a quienes lo habitan. Nuestra propuesta pone en el centro al territorio, sus saberes y sus comunidades.</p>
                    <div class="principios-comunidad"><p><span>01</span> Escuchar antes de proponer.</p><p><span>02</span> Aprender de los saberes del lugar.</p><p><span>03</span> Construir desde la colaboración.</p></div>
                    <p class="nota-comunidades">Próximamente compartiremos las comunidades y regiones que forman parte de este camino.</p>
                </div>
            </div>
            <span class="credito-imagen">Imagen de referencia</span>
        </section>

        <section class="seccion guias" id="guias" aria-labelledby="titulo-guias">
            <div class="contenedor guias-cuadricula">
                <div><p class="antetitulo"><span></span> Personas que acompañan</p><h2 id="titulo-guias">El camino cambia<br>cuando alguien<br><em>te lo comparte.</em></h2><p class="guias-descripcion">La formación, los talentos y la mirada de cada guía dan una perspectiva distinta al recorrido.</p><span class="nota-editorial"><x-icono nombre="hoja" /> Cada encuentro, una nueva forma de aprender.</span></div>
                {{-- Los perfiles se mostrarán con información autorizada; no se simulan personas. --}}
                <div class="guias-vacio"><div class="guias-ilustracion" aria-hidden="true"><x-icono nombre="personas" /><span></span></div><p class="numero-seccion">Conoce a quienes te acompañarán</p><h3>Las personas detrás<br>de la experiencia.</h3><p>Todavía no hay perfiles publicados. Aquí podrás conocer a los guías, su formación y sus talentos.</p><span class="estado-publicacion"><span></span> Perfiles próximamente</span></div>
            </div>
        </section>

        <section class="organizaciones" id="organizaciones" aria-labelledby="titulo-organizaciones">
            <div class="contenedor organizaciones-cuadricula">
                <div><p class="antetitulo"><span></span> Empresas, agencias y organizaciones</p><h2 id="titulo-organizaciones">Las buenas ideas<br>también necesitan<br><em>salir al aire libre.</em></h2><p>Un espacio para imaginar experiencias de grupo, explorar colaboraciones y dar forma a proyectos con propósito.</p></div>
                <div class="organizaciones-opciones">
                    <article><span class="numero-linea">01</span><div><h3>Experiencias para grupos</h3><p>Una propuesta que considere a las personas, sus intereses y el sentido de su encuentro.</p></div><x-icono nombre="personas" /></article>
                    <article><span class="numero-linea">02</span><div><h3>Agencias y colaboraciones</h3><p>Un punto de encuentro para acercar nuestra propuesta a nuevos viajeros.</p></div><x-icono nombre="brujula" /></article>
                    <article><span class="numero-linea">03</span><div><h3>Consultoría y proyectos</h3><p>Acompañamiento para organizaciones y comunidades que buscan construir desde su territorio.</p></div><x-icono nombre="hoja" /></article>
                    {{-- No se envían formularios ni se abre WhatsApp hasta confirmar los canales oficiales. --}}
                    <p class="aviso-contacto"><x-icono nombre="informacion" /><span><strong>Atención personalizada, próximamente.</strong> Compartiremos aquí los canales oficiales de contacto.</span></p>
                </div>
            </div>
        </section>

        <section class="seccion preguntas" aria-labelledby="titulo-preguntas">
            <div class="contenedor preguntas-cuadricula">
                <div><p class="antetitulo"><span></span> Antes de emprender el camino</p><h2 id="titulo-preguntas">Quizá te estés<br><em>preguntando…</em></h2></div>
                <div class="lista-preguntas">
                    <details><summary>¿Qué propone el turismo regenerativo?<x-icono nombre="mas" /></summary><p>Una forma de relacionarnos con los destinos que invita a cuidar la naturaleza, escuchar a las comunidades y pensar en cómo contribuimos a los lugares que visitamos.</p></details>
                    <details><summary>¿Dónde podré conocer las rutas y sus detalles?<x-icono nombre="mas" /></summary><p>En el apartado de experiencias. Cuando se publiquen las rutas de senderismo, ahí podrás consultar la información de cada recorrido. Por ahora no hay rutas ni fechas disponibles.</p></details>
                    <details><summary>¿Habrá atención para grupos y organizaciones?<x-icono nombre="mas" /></summary><p>Sí, la propuesta contempla experiencias para grupos y una vertiente de consultoría. El apartado para organizaciones reunirá los canales oficiales cuando estén confirmados.</p></details>
                </div>
            </div>
        </section>

        <section class="cierre" aria-labelledby="titulo-cierre">
            <div class="contenedor cierre-interior"><x-icono nombre="montana" /><p class="antetitulo antetitulo-claro">Un lugar para volver a conectar</p><h2 id="titulo-cierre">A veces, para encontrarte,<br><em>solo hace falta salir.</em></h2><a class="boton boton-crema" href="#experiencias">Explorar la propuesta <x-icono nombre="flecha" /></a></div>
            <div class="curvas-cierre" aria-hidden="true"></div>
        </section>
    </main>

    <footer class="pie">
        <div class="contenedor">
            <div class="pie-superior">
                <div><a class="marca" href="#inicio"><img src="{{ asset('imagenes/simbolo.svg') }}" alt="" width="47" height="47"><span>Mil Cumbres<small>Turismo regenerativo</small></span></a><p>Naturaleza que inspira.<br>Conexiones que permanecen.</p></div>
                <nav aria-label="Explorar Mil Cumbres"><span>Descubre</span><a href="#experiencias">Experiencias</a><a href="#propuesta">Nuestra propuesta</a><a href="#guias">Nuestros guías</a></nav>
                <nav aria-label="Conocer Mil Cumbres"><span>Conecta</span><a href="#esencia">Nuestra esencia</a><a href="#comunidades">Territorio y comunidad</a><a href="#organizaciones">Para organizaciones</a></nav>
                <div class="pie-nota"><span>Un proyecto que sigue creciendo</span><p>Estamos preparando las primeras experiencias. Gracias por ser parte del comienzo.</p><a class="enlace-texto" href="#inicio">Volver al inicio <x-icono nombre="arriba" /></a></div>
            </div>
            <div class="pie-inferior"><p>© {{ date('Y') }} Mil Cumbres. Todos los derechos reservados.</p><span>Hecho para volver a lo esencial.</span></div>
        </div>
    </footer>
</body>
</html>
