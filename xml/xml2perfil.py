import xml.etree.ElementTree as ET

def generar_altimetria(xml_file, svg_file):
    # Parsear el archivo XML
    tree = ET.parse(xml_file)
    root = tree.getroot()

    # Espacios de nombres del XML
    ns = {'ns': 'http://www.uniovi.es'}

    # Extraer las distancias acumuladas y altitudes
    puntos = []
    distancia_acumulada = 0
    for tramo in root.findall('ns:tramos/ns:tramo', ns):
        distancia = float(tramo.find('ns:distancia/ns:valor', ns).text.replace(",", "."))
        distancia_acumulada += distancia

        altitud = float(tramo.find('ns:coordenadas/ns:altitud', ns).text)

        puntos.append((distancia_acumulada, altitud))

    max_distancia = max(p[0] for p in puntos)
    max_altitud = max(p[1] for p in puntos)
    min_altitud = 20 

    ancho_svg = 1000
    alto_svg = 500
    margen = 50

    escala_x = (ancho_svg - 2 * margen) / max_distancia
    escala_y = (alto_svg - 2 * margen) / (max_altitud - min_altitud)

    puntos_svg = [
        (margen + x * escala_x, alto_svg - margen - (y - min_altitud) * escala_y)
        for x, y in puntos
    ]

    polilinea = " ".join(f"{x},{y}" for x, y in puntos_svg)

    polilinea += f" {puntos_svg[-1][0]},{alto_svg - margen} {puntos_svg[0][0]},{alto_svg - margen} {puntos_svg[0][0]},{puntos_svg[0][1]}"

    svg_content = f"""<svg xmlns="http://www.w3.org/2000/svg" width="{ancho_svg}" height="{alto_svg}" viewBox="0 0 {ancho_svg} {alto_svg}">
    <polyline points="{polilinea}" fill="red" stroke="black" stroke-width="2" />
    </svg>"""

    with open(svg_file, 'w', encoding='utf-8') as f:
        f.write(svg_content)

xml_file = "circuitoEsquema.xml"
svg_file = "perfil.svg"
generar_altimetria(xml_file, svg_file)