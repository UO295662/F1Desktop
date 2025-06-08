import xml.etree.ElementTree as ET

def convert_rutas_xml_to_kml(xml_file, kml_file):
    tree = ET.parse(xml_file)
    root = tree.getroot()

    with open(kml_file, 'w', encoding='utf-8') as f:
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<kml xmlns="http://www.opengis.net/kml/2.2">\n')
        f.write('  <Document>\n')
        f.write('    <name>Rutas de Oviedo</name>\n')
        f.write('    <description>Rutas turísticas por Oviedo y alrededores</description>\n')
        
        # Estilos para diferentes tipos de rutas
        f.write('    <Style id="rutaArquitectura">\n')
        f.write('      <LineStyle>\n')
        f.write('        <color>ff0000ff</color>\n')  # Rojo para arquitectura
        f.write('        <width>4</width>\n')
        f.write('      </LineStyle>\n')
        f.write('      <IconStyle>\n')
        f.write('        <color>ff0000ff</color>\n')
        f.write('        <scale>1.2</scale>\n')
        f.write('      </IconStyle>\n')
        f.write('    </Style>\n')
        
        f.write('    <Style id="rutaGastronomica">\n')
        f.write('      <LineStyle>\n')
        f.write('        <color>ff00ff00</color>\n')  # Verde para gastronomía
        f.write('        <width>4</width>\n')
        f.write('      </LineStyle>\n')
        f.write('      <IconStyle>\n')
        f.write('        <color>ff00ff00</color>\n')
        f.write('        <scale>1.2</scale>\n')
        f.write('      </IconStyle>\n')
        f.write('    </Style>\n')
        
        f.write('    <Style id="rutaPaisajistica">\n')
        f.write('      <LineStyle>\n')
        f.write('        <color>ffff0000</color>\n')  # Azul para paisajística
        f.write('        <width>4</width>\n')
        f.write('      </LineStyle>\n')
        f.write('      <IconStyle>\n')
        f.write('        <color>ffff0000</color>\n')
        f.write('        <scale>1.2</scale>\n')
        f.write('      </IconStyle>\n')
        f.write('    </Style>\n')

        # Procesar cada ruta
        for ruta in root.findall('ruta'):
            nombre_ruta = ruta.find('nombre').text
            tipo_ruta = ruta.find('tipo').text
            transporte = ruta.find('medio_transporte').text
            duracion = ruta.find('duracion').text
            descripcion = ruta.find('descripcion').text.strip()
            personas = ruta.find('personas').text
            recomendacion = ruta.find('recomendacion').text
            
            # Determinar el estilo según el tipo
            if "Arquitectura" in tipo_ruta:
                estilo = "#rutaArquitectura"
            elif "Gastronómica" in tipo_ruta:
                estilo = "#rutaGastronomica"
            elif "Paisajística" in tipo_ruta:
                estilo = "#rutaPaisajistica"
            else:
                estilo = "#rutaArquitectura"  
            
            # Crear una carpeta para cada ruta
            f.write(f'    <Folder>\n')
            f.write(f'      <name>{nombre_ruta}</name>\n')
            f.write(f'      <description>\n')
            f.write(f'        Tipo: {tipo_ruta}\n')
            f.write(f'        Transporte: {transporte}\n')
            f.write(f'        Duración: {duracion}\n')
            f.write(f'        Dirigida a: {personas}\n')
            f.write(f'        Recomendación: {recomendacion}/10\n')
            f.write(f'        Descripción: {descripcion}\n')
            f.write(f'      </description>\n')

            # Punto de inicio
            inicio = ruta.find('inicio')
            if inicio is not None:
                lugar_inicio = inicio.find('lugar').text
                direccion_inicio = inicio.find('direccion').text
                coordenadas_inicio = inicio.find('coordenadas')
                lat_inicio = coordenadas_inicio.find('latitud').text
                lon_inicio = coordenadas_inicio.find('longitud').text
                alt_inicio = coordenadas_inicio.find('altitud').text

                f.write(f'      <Placemark>\n')
                f.write(f'        <name>INICIO: {lugar_inicio}</name>\n')
                f.write(f'        <description>Dirección: {direccion_inicio}</description>\n')
                f.write(f'        <styleUrl>{estilo}</styleUrl>\n')
                f.write(f'        <Point>\n')
                f.write(f'          <coordinates>{lon_inicio},{lat_inicio},{alt_inicio}</coordinates>\n')
                f.write(f'        </Point>\n')
                f.write(f'      </Placemark>\n')

            # Procesar hitos
            hitos = ruta.find('hitos')
            if hitos is not None:
                coordenadas_ruta = []
                coordenadas_ruta.append(f'{lon_inicio},{lat_inicio},{alt_inicio}')  # Añadir punto de inicio
                
                for hito in hitos.findall('hito'):
                    nombre_hito = hito.find('nombre').text
                    descripcion_hito = hito.find('descripcion').text
                    coordenadas_hito = hito.find('coordenadas')
                    lat_hito = coordenadas_hito.find('latitud').text
                    lon_hito = coordenadas_hito.find('longitud').text
                    alt_hito = coordenadas_hito.find('altitud').text
                    distancia = hito.find('distancia')
                    
                    # Añadir coordenadas a la ruta
                    coordenadas_ruta.append(f'{lon_hito},{lat_hito},{alt_hito}')
                    
                    descripcion_completa = f'{descripcion_hito}'
                    if distancia is not None:
                        dist_text = distancia.text
                        dist_unidad = distancia.get('unidad', 'm')
                        descripcion_completa += f'\nDistancia: {dist_text} {dist_unidad}'
                    
                    # Añadir información de fotos y videos si existen
                    galeria_fotos = hito.find('galeria_fotos')
                    if galeria_fotos is not None:
                        fotos = galeria_fotos.findall('foto')
                        if fotos:
                            descripcion_completa += '\nFotos disponibles: '
                            for foto in fotos:
                                archivo = foto.get('archivo')
                                descripcion_completa += f'{archivo} '
                    
                    galeria_videos = hito.find('galeria_videos')
                    if galeria_videos is not None:
                        videos = galeria_videos.findall('video')
                        if videos:
                            descripcion_completa += '\nVideos disponibles: '
                            for video in videos:
                                archivo = video.get('archivo')
                                descripcion_completa += f'{archivo} '

                    f.write(f'      <Placemark>\n')
                    f.write(f'        <name>{nombre_hito}</name>\n')
                    f.write(f'        <description>{descripcion_completa}</description>\n')
                    f.write(f'        <styleUrl>{estilo}</styleUrl>\n')
                    f.write(f'        <Point>\n')
                    f.write(f'          <coordinates>{lon_hito},{lat_hito},{alt_hito}</coordinates>\n')
                    f.write(f'        </Point>\n')
                    f.write(f'      </Placemark>\n')

                # Crear la línea de la ruta conectando todos los puntos
                if len(coordenadas_ruta) > 1:
                    f.write(f'      <Placemark>\n')
                    f.write(f'        <name>Recorrido: {nombre_ruta}</name>\n')
                    f.write(f'        <description>Línea que conecta todos los puntos de la ruta</description>\n')
                    f.write(f'        <styleUrl>{estilo}</styleUrl>\n')
                    f.write(f'        <LineString>\n')
                    f.write(f'          <tessellate>1</tessellate>\n')
                    f.write(f'          <coordinates>\n')
                    for coord in coordenadas_ruta:
                        f.write(f'            {coord}\n')
                    f.write(f'          </coordinates>\n')
                    f.write(f'        </LineString>\n')
                    f.write(f'      </Placemark>\n')

            f.write(f'    </Folder>\n')
        
        f.write('  </Document>\n')
        f.write('</kml>\n')

# Uso del script
if __name__ == "__main__":
    convert_rutas_xml_to_kml('rutas.xml', 'rutas.kml')
    print("Conversión completada. Archivo rutas.kml generado.")
