import xml.etree.ElementTree as ET

def generar_altimetria_ruta(ruta, nombre_ruta, svg_file):
    # Extraer puntos de altitud (inicio + hitos)
    puntos = []
    distancia_acumulada = 0
    
    # Punto de inicio
    inicio = ruta.find('inicio')
    if inicio is not None:
        altitud_inicio = float(inicio.find('coordenadas/altitud').text)
        puntos.append((0, altitud_inicio))
    
    # Hitos
    hitos = ruta.find('hitos')
    if hitos is not None:
        for hito in hitos.findall('hito'):
            distancia = float(hito.find('distancia').text)
            altitud = float(hito.find('coordenadas/altitud').text)
            distancia_acumulada += distancia
            puntos.append((distancia_acumulada, altitud))

    # Verificar que hay suficientes puntos
    if len(puntos) < 2:
        print(f"Advertencia: La ruta '{nombre_ruta}' tiene menos de 2 puntos")
        return

    # Calcular escalas
    max_distancia = max(p[0] for p in puntos)
    max_altitud = max(p[1] for p in puntos)
    min_altitud = min(p[1] for p in puntos)

    # Evitar división por cero
    if max_altitud == min_altitud:
        max_altitud += 10
        min_altitud -= 10

    ancho_svg = 1000
    alto_svg = 500
    margen = 50

    escala_x = (ancho_svg - 2 * margen) / max_distancia
    escala_y = (alto_svg - 2 * margen) / (max_altitud - min_altitud)

    # Convertir a coordenadas SVG
    puntos_svg = [
        (margen + x * escala_x, alto_svg - margen - (y - min_altitud) * escala_y)
        for x, y in puntos
    ]

    polilinea = " ".join(f"{x},{y}" for x, y in puntos_svg)

    # Cerrar área
    polilinea += f" {puntos_svg[-1][0]},{alto_svg - margen} {puntos_svg[0][0]},{alto_svg - margen}"

    # Generar SVG
    svg_content = f"""<svg xmlns="http://www.w3.org/2000/svg" width="{ancho_svg}" height="{alto_svg}" viewBox="0 0 {ancho_svg} {alto_svg}">
    <polygon points="{polilinea}" fill="rgba(215, 122, 97, 0.3)" stroke="#d77a61" stroke-width="2" />
    <text x="{ancho_svg/2}" y="30" text-anchor="middle" font-family="Arial" font-size="16" fill="#2c2c2c">{nombre_ruta}</text>
    <text x="{ancho_svg/2}" y="50" text-anchor="middle" font-family="Arial" font-size="12" fill="#666">Distancia: {max_distancia:.0f}m - Desnivel: {max_altitud - min_altitud:.0f}m</text>
    </svg>"""

    with open(svg_file, 'w', encoding='utf-8') as f:
        f.write(svg_content)
    
    print(f"Generado: {svg_file} - {nombre_ruta}")

def generar_todas_altimetrias(xml_file):
    # Parsear el archivo XML
    tree = ET.parse(xml_file)
    root = tree.getroot()
    
    # Procesar cada ruta
    for ruta in root.findall('ruta'):
        nombre_ruta = ruta.find('nombre').text
        
        # Obtener nombre del archivo SVG del atributo altimetria
        altimetria = ruta.find('altimetria')
        if altimetria is not None:
            svg_file = altimetria.get('archivo')
        else:
            # Si no tiene atributo, crear nombre basado en el nombre de la ruta
            svg_file = nombre_ruta.lower().replace(' ', '_').replace('ñ', 'n') + '.svg'
        
        generar_altimetria_ruta(ruta, nombre_ruta, svg_file)

# Ejecutar para todas las rutas
xml_file = "rutas.xml"
generar_todas_altimetrias(xml_file)