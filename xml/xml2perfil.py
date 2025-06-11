import xml.etree.ElementTree as ET

def generar_altimetria_ruta(ruta, nombre_ruta, svg_file):
    # Extraer puntos de altitud (inicio + hitos)
    puntos = []
    nombres_puntos = []
    distancia_acumulada = 0
    
    # Punto de inicio
    inicio = ruta.find('inicio')
    if inicio is not None:
        altitud_inicio = float(inicio.find('coordenadas/altitud').text)
        lugar_inicio = inicio.find('lugar').text if inicio.find('lugar') is not None else "Inicio"
        puntos.append((0, altitud_inicio))
        nombres_puntos.append(lugar_inicio)
    
    # Hitos
    hitos = ruta.find('hitos')
    if hitos is not None:
        for hito in hitos.findall('hito'):
            distancia = float(hito.find('distancia').text)
            altitud = float(hito.find('coordenadas/altitud').text)
            nombre_hito = hito.find('nombre').text if hito.find('nombre') is not None else "Hito"
            distancia_acumulada += distancia
            puntos.append((distancia_acumulada, altitud))
            nombres_puntos.append(nombre_hito)

    # Verificar que hay suficientes puntos
    if len(puntos) < 2:
        print(f"Advertencia: La ruta '{nombre_ruta}' tiene menos de 2 puntos")
        return

    # Calcular escalas - SIEMPRE usar 0 como mínimo (nivel del mar)
    max_distancia = max(p[0] for p in puntos)
    max_altitud = max(p[1] for p in puntos)
    min_altitud = 0  # FORZAR que el mínimo sea siempre el nivel del mar (0m)

    # Evitar división por cero - asegurar que hay diferencia de altitud
    if max_altitud == min_altitud:
        max_altitud += 100  # Añadir 100m si todo está al nivel del mar

    ancho_svg = 1000
    alto_svg = 500
    margen = 80

    escala_x = (ancho_svg - 2 * margen) / max_distancia
    escala_y = (alto_svg - 2 * margen - 40) / (max_altitud - min_altitud)

    # Convertir a coordenadas SVG
    puntos_svg = [
        (margen + x * escala_x, alto_svg - margen - 40 - (y - min_altitud) * escala_y)
        for x, y in puntos
    ]

    polilinea = " ".join(f"{x},{y}" for x, y in puntos_svg)

    # Cerrar área hasta el nivel del mar (base del gráfico)
    polilinea += f" {puntos_svg[-1][0]},{alto_svg - margen - 40} {puntos_svg[0][0]},{alto_svg - margen - 40}"

    # Calcular posición del nivel del mar (siempre estará en la base)
    nivel_mar_y = alto_svg - margen - 40  # Siempre en la base del gráfico

    # Generar SVG
    svg_content = f"""<svg xmlns="http://www.w3.org/2000/svg" width="{ancho_svg}" height="{alto_svg}" viewBox="0 0 {ancho_svg} {alto_svg}">
    <!-- Fondo del gráfico -->
    <rect x="{margen}" y="60" width="{ancho_svg - 2 * margen}" height="{alto_svg - margen - 100}" fill="#f8f9fa" stroke="#ddd" stroke-width="1"/>
    
    <!-- Línea del nivel del mar (siempre visible en la base) -->
    <line x1="{margen}" y1="{nivel_mar_y}" x2="{ancho_svg - margen}" y2="{nivel_mar_y}" stroke="#0066cc" stroke-width="2" stroke-dasharray="8,4"/>
    <text x="{margen - 5}" y="{nivel_mar_y + 4}" text-anchor="end" font-family="Arial" font-size="12" fill="#0066cc" font-weight="bold">Nivel del mar (0m)</text>
    
    <!-- Perfil altimétrico -->
    <polygon points="{polilinea}" fill="rgba(139, 58, 58, 0.3)" stroke="#8b3a3a" stroke-width="2" />
    
    <!-- Puntos de hitos -->"""
    
    # Agregar puntos y etiquetas para cada hito
    for i, ((x_svg, y_svg), nombre) in enumerate(zip(puntos_svg, nombres_puntos)):
        altitud = puntos[i][1]
        svg_content += f"""
    <circle cx="{x_svg}" cy="{y_svg}" r="5" fill="#8b3a3a" stroke="#fff" stroke-width="2"/>
    <text x="{x_svg}" y="{y_svg - 12}" text-anchor="middle" font-family="Arial" font-size="10" fill="#1a1a1a" font-weight="bold" transform="rotate(-90 {x_svg} {y_svg - 12})">{nombre}</text>
    <text x="{x_svg}" y="{y_svg + 22}" text-anchor="middle" font-family="Arial" font-size="9" fill="#5a1f1f">{altitud:.0f}m</text>"""
    
    # Líneas de cuadrícula horizontales cada 50m
    altitud_actual = 50
    while altitud_actual <= max_altitud:
        y_cuadricula = alto_svg - margen - 40 - (altitud_actual - min_altitud) * escala_y
        svg_content += f"""
    <line x1="{margen}" y1="{y_cuadricula}" x2="{ancho_svg - margen}" y2="{y_cuadricula}" stroke="#ddd" stroke-width="0.5" opacity="0.7"/>
    <text x="{margen - 10}" y="{y_cuadricula + 3}" text-anchor="end" font-family="Arial" font-size="8" fill="#666">{altitud_actual:.0f}m</text>"""
        altitud_actual += 50
    
    svg_content += f"""
    
    <!-- Títulos -->
    <text x="{ancho_svg/2}" y="30" text-anchor="middle" font-family="Arial" font-size="16" fill="#1a1a1a" font-weight="bold">{nombre_ruta}</text>
    <text x="{ancho_svg/2}" y="50" text-anchor="middle" font-family="Arial" font-size="12" fill="#5a1f1f">Distancia: {max_distancia:.0f}m - Altitud máxima: {max_altitud:.0f}m</text>
    
    <!-- Ejes -->
    <text x="{margen/2}" y="{alto_svg/2}" text-anchor="middle" font-family="Arial" font-size="12" fill="#5a1f1f" transform="rotate(-90 {margen/2} {alto_svg/2})">Altitud (m)</text>
    <text x="{ancho_svg/2}" y="{alto_svg - 10}" text-anchor="middle" font-family="Arial" font-size="12" fill="#5a1f1f">Distancia (m)</text>
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
        nombre_ruta = ruta.find('nombre')
        if nombre_ruta is not None:
            nombre_ruta = nombre_ruta.text
            
            # Obtener nombre del archivo SVG del atributo altimetria
            altimetria = ruta.find('altimetria')
            if altimetria is not None:
                svg_file = altimetria.get('archivo')
            else:
                # Si no tiene atributo, crear nombre basado en el nombre de la ruta
                svg_file = nombre_ruta.lower().replace(' ', '_').replace('ñ', 'n') + '.svg'
            
            generar_altimetria_ruta(ruta, nombre_ruta, svg_file)

# Ejecutar para todas las rutas
if __name__ == "__main__":
    xml_file = "rutas.xml"
    generar_todas_altimetrias(xml_file)