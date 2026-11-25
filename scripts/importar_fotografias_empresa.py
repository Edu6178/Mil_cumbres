"""Prepara exclusivamente el lote autorizado; conserva las fotos originales y retira metadatos de las copias web."""
import argparse
import hashlib
import json
from pathlib import Path
from PIL import Image, ImageOps

FOTOS = [
    ('7866', 'Aprender a reconocer las aves', 'Explicación con una guía ilustrada de aves durante un recorrido por el bosque.', 'aprendizaje', 46, 48),
    ('7886', 'Mirar juntos hacia las copas', 'Grupo con binoculares observando las copas de los árboles entre la vegetación.', 'aves', 50, 65),
    ('7907', 'Una explicación en el camino', 'Una persona comparte una explicación con el grupo entre los árboles.', 'aprendizaje', 52, 43),
    ('7927', 'La curiosidad nos reúne', 'Participantes señalan y observan las ramas del bosque con binoculares.', 'aves', 50, 65),
    ('7947', 'Pequeños acentos de color', 'Detalle de una flor tubular naranja sobre un fondo de vegetación desenfocada.', 'biodiversidad', 50, 49),
    ('7967', 'Descubrir otra forma de mirar', 'Cuatro participantes observan las ramas con binoculares y un teléfono.', 'aves', 50, 51),
    ('7991', 'Compartir lo que observamos', 'Explicación con gestos durante una actividad de observación en el bosque.', 'aprendizaje', 50, 42),
    ('8013', 'Una pausa sobre la rama', 'Ave de plumaje café posada sobre una rama cubierta de líquenes.', 'aves', 50, 52),
    ('8037', 'Atención a lo que nos rodea', 'Un participante usa binoculares sentado sobre una roca junto al agua.', 'aves', 49, 49),
    ('8058', 'El bosque también nos observa', 'Ave de plumaje café sobre una rama, con el bosque desenfocado al fondo.', 'aves', 51, 43),
    ('8078', 'La conversación también es parte', 'Una explicación al aire libre junto a un pequeño cauce rodeado de vegetación.', 'aprendizaje', 42, 44),
    ('8097', 'Un mundo a otra escala', 'Un participante examina la corteza de un árbol con una lupa.', 'aprendizaje', 49, 51),
    ('8116', 'Compañía en una pausa', 'Un perro descansa sobre el suelo cubierto de hojas y raíces del bosque.', 'encuentros', 49, 43),
    ('8131', 'Acercarse para comprender', 'Participantes observan de cerca la corteza de un árbol con lupas.', 'aprendizaje', 52, 52),
    ('8146', 'Tiempo junto al agua', 'Un participante descansa sobre una roca con un cuerpo de agua al fondo.', 'encuentros', 48, 48),
    ('8172', 'El bosque bajo nuestros pies', 'Participantes fotografían un hongo claro entre hojas, pinos y plantas del suelo.', 'biodiversidad', 54, 38),
    ('8192', 'Otra forma de disfrutar el agua', 'Un perro nada con una rama en el hocico.', 'encuentros', 47, 46),
    ('8216', 'Detalles que merecen una pausa', 'Un hongo de sombrero claro crece entre las plantas del bosque.', 'biodiversidad', 51, 48),
    ('8238', 'Un encuentro en el sendero', 'Retrato de un perro en un claro del bosque.', 'encuentros', 57, 45),
    ('8256', 'Escuchar, esperar, observar', 'Una persona observa el entorno con binoculares sentada en unos escalones.', 'aves', 51, 49),
    ('8271', 'El color de lo pequeño', 'Detalle de una flor roja con centro amarillo sobre un fondo oscuro.', 'biodiversidad', 49, 47),
    ('8289', 'El recorrido se comparte', 'Tres participantes observan el bosque en un sendero rodeado de árboles.', 'recorridos', 49, 54),
    ('8306', 'Aprender en grupo', 'Dos personas comparten una explicación con participantes durante el recorrido.', 'aprendizaje', 58, 47),
    ('8327', 'Siempre hay algo por descubrir', 'Un grupo señala y observa las copas de los árboles desde el sendero.', 'recorridos', 47, 51),
    ('8345', 'Una pausa para volver a conectar', 'Un participante sonríe mientras descansa sobre una roca junto al agua.', 'encuentros', 51, 51),
    ('8364', 'La naturaleza despierta preguntas', 'Una participante muestra en su teléfono una herramienta de reconocimiento de aves.', 'aprendizaje', 49, 47),
    ('8381', 'Miradas desde la orilla', 'Una persona observa con binoculares desde una roca junto al agua.', 'aves', 50, 50),
    ('8398', 'El camino empieza al compartirlo', 'Tres participantes caminan de espaldas por un sendero entre árboles y luz natural.', 'recorridos', 50, 58),
    ('8415', 'Compartir el descanso', 'Un participante descansa en un tronco acompañado por un perro.', 'encuentros', 51, 49),
    ('8432', 'El agua sigue su camino', 'Cascada entre rocas, árboles y vegetación del bosque.', 'recorridos', 53, 54),
    ('8451', 'Descubrir el bosque en compañía', 'Grupo observando el follaje con binoculares durante una caminata.', 'aves', 50, 60),
    ('8469', 'Una invitación a aprender', 'Una persona explica una actividad de observación con una guía ilustrada en la mano.', 'aprendizaje', 49, 45),
    ('8487', 'Conversaciones en una pausa', 'Un participante toma un descanso en un tronco junto a un perro.', 'encuentros', 51, 48),
    ('8503', 'La alegría de estar afuera', 'Un participante sonríe sentado en una roca de la orilla.', 'encuentros', 50, 48),
    ('8521', 'Una pausa en la orilla', 'Un participante con mochila y binoculares junto al agua.', 'encuentros', 49, 49),
    ('8536', 'El ritmo tranquilo del bosque', 'Un perro descansa entre hojas y raíces del bosque.', 'encuentros', 43, 44),
    ('8555', 'Viajo y algo en mí se despierta', 'Camiseta de Mil Cumbres con el mensaje Viajo y algo en mí se despierta.', 'encuentros', 53, 53),
    ('8572', 'Momentos que permanecen', 'Una persona adulta abraza a una niña durante una pausa entre los árboles.', 'encuentros', 53, 53),
    ('8589', 'Una mirada desde la orilla', 'Un participante sostiene binoculares mientras descansa junto al agua.', 'aves', 49, 49),
    ('8606', 'El agua, a otro ritmo', 'Un perro nada hacia la orilla con una rama en el hocico.', 'encuentros', 56, 50),
    ('8621', 'Los pequeños frutos del bosque', 'Detalle de un fruto rojo junto a una hoja verde.', 'biodiversidad', 49, 51),
    ('8637', 'La sorpresa de mirar arriba', 'Un participante con binoculares observa las copas de los árboles.', 'aves', 52, 47),
]

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--origen', required=True, type=Path)
    args = parser.parse_args()
    raiz = Path(__file__).resolve().parents[1]
    archivos = [(args.origen / ('179539610' + sufijo + '.jpg'), datos) for sufijo, *datos in FOTOS]
    repetida = args.origen / 'WhatsApp Image 2026-11-22 at 7.07.03 PM.jpeg'
    for ruta in [repetida] + [ruta for ruta, _ in archivos]:
        if not ruta.is_file():
            raise FileNotFoundError(ruta.name)
    salida = raiz / 'public/imagenes/empresa'
    salida.mkdir(parents=True, exist_ok=True)
    fotos, huellas = {}, {}
    for indice, (ruta, datos) in enumerate(archivos, 1):
        titulo, alternativo, album, x, y = datos
        with Image.open(ruta) as original:
            imagen = ImageOps.exif_transpose(original).convert('RGB')
        huella = hashlib.sha256(imagen.tobytes()).hexdigest()
        clave = 'empresa-' + ruta.stem
        if huella in huellas:
            fotos[huellas[huella]]['originales'].append(ruta.name)
            continue
        huellas[huella] = clave
        foto = dict(id=clave, titulo=titulo, alternativo=alternativo, album=album, foco_x=x, foco_y=y,
                    ancho=imagen.width, alto=imagen.height, posicion=indice * 10, publicada=True,
                    destacada=ruta.stem[-4:] in ['7967', '8058', '8097', '8327', '8432', '8572'],
                    archivo='imagenes/empresa/' + clave + '.webp', miniatura='imagenes/empresa/' + clave + '-640.webp',
                    originales=[ruta.name], huella=huella)
        for maximo, sufijo in [(1600, ''), (640, '-640')]:
            copia = imagen.copy()
            copia.thumbnail((maximo, maximo), Image.Resampling.LANCZOS)
            copia.save(salida / (clave + sufijo + '.webp'), 'WEBP', quality=84, method=6)
        fotos[clave] = foto
    with Image.open(repetida) as imagen:
        huella = hashlib.sha256(ImageOps.exif_transpose(imagen).convert('RGB').tobytes()).hexdigest()
    if huella not in huellas:
        raise ValueError('La primera fotografía no coincide con el duplicado revisado; revisar antes de omitirla.')
    fotos[huellas[huella]]['originales'].append(repetida.name)
    datos = dict(
        archivo=None,
        albumes={'recorridos': 'Recorridos y paisajes', 'aves': 'Observación de aves', 'aprendizaje': 'Aprender en la naturaleza', 'biodiversidad': 'Detalles del bosque', 'encuentros': 'Momentos compartidos'},
        espacios={
            'inicio-portada': ['Portada de inicio', '8398'], 'inicio-propuesta': ['Inicio · Cinco caminos', '7886'],
            'inicio-comunidades': ['Inicio · Territorio y comunidad', '8327'], 'acceso': ['Iniciar sesión y registro', '8398'],
            'bosque': ['Bosque de bienvenida', '8398'], 'hero-catalogo': ['Catálogo de experiencias', '7967'],
            'hero-nosotros': ['Nuestra esencia', '8555'], 'hero-metodologia': ['Turismo regenerativo', '8621'],
            'hero-consultoria': ['Talleres y cursos', '7866'], 'hero-operadora': ['Operadora turística', '8327'],
            'hero-guias': ['Presentación del equipo de guías', '8306'], 'hero-comunidades': ['Territorio y comunidades', '8398'],
        },
        servicios={
            'sendero-del-bosque-de-pinos': '8398', 'miradores-al-amanecer': '8381', 'travesia-de-alta-montana': '8289',
            'caminata-interpretativa-entre-encinos': '8097', 'ruta-de-las-cascadas': '8432', 'senderismo': '8398',
            'avistamiento-de-aves': '8058', 'avistamiento-de-mariposa-monarca': '8621', 'avistamiento-de-luciernagas': '7947',
            'recoleccion-de-hongos-silvestres-comestibles': '8216', 'practica-de-movimiento-en-la-naturaleza': '8432',
            'terapia-de-naturaleza': '8432', 'regeneracion-organizacional': '8306', 'pedagogia-de-naturaleza': '8131',
            'ecovoluntariado': '8172', 'talleres-y-cursos-de-turismo': '7866', 'formacion-turistica': '7866',
            'consultoria-turistica': '8078', 'experiencias-de-turismo-alternativo': '7967',
        },
        fotos=fotos,
    )
    for clave, (nombre, sufijo) in datos['espacios'].items():
        datos['espacios'][clave] = {'nombre': nombre, 'foto': 'empresa-179539610' + sufijo}
    datos['servicios'] = {k: 'empresa-179539610' + v for k, v in datos['servicios'].items()}
    json_path = raiz / 'config/fotografias-empresa.json'
    json_path.write_text(json.dumps(datos, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    print(f'{len(fotos)} fotografías únicas, {sum(len(f["originales"]) for f in fotos.values())} archivos representados; copias WebP sin EXIF.')
    print(f'Tamaño web total: {sum(p.stat().st_size for p in salida.glob("*.webp")) / 1024 / 1024:.2f} MB.')

if __name__ == '__main__':
    main()
