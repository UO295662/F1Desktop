import xml.etree.ElementTree as ET

def convert_xml_to_kml(xml_file, kml_file):
    tree = ET.parse(xml_file)
    root = tree.getroot()
    ns = {'': 'http://www.uniovi.es'}

    # Extract general information about the circuit
    circuit_name = root.find('nombre', ns).text
    country = root.find('pais', ns).text
    locality = root.find('localidad', ns).text
    track_length = root.find('longitud_pista', ns).text
    date = root.find('fecha', ns).text
    time = root.find('hora', ns).text

    with open(kml_file, 'w') as f:
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
        f.write('<kml xmlns="http://www.opengis.net/kml/2.2">\n')
        f.write('  <Document>\n')
        f.write(f'    <name>{circuit_name}</name>\n')
        f.write(f'    <description>Circuito en {locality}, {country}.\n')
        f.write(f'      Longitud de la pista: {track_length} m\n')
        f.write(f'      Fecha: {date} Hora: {time}\n')
        f.write('    </description>\n')
        
        f.write('    <Style id="yellowLine">\n')
        f.write('      <LineStyle>\n')
        f.write('        <color>7f00ffff</color>\n')
        f.write('        <width>4</width>\n')
        f.write('      </LineStyle>\n')
        f.write('    </Style>\n')
        
        centro = root.find('coordenadas_centro', ns)
        if centro is not None:
            lat_centro = centro.find('latitud', ns).text
            lon_centro = centro.find('longitud', ns).text
            alt_centro = centro.find('altitud', ns).text

            f.write('     <Placemark id="centro_circuito">\n')
            f.write('      <name>Centro del Circuito</name>\n')
            f.write(f'      <description>Ubicado en {locality}, {country}</description>\n')
            f.write('      <Point>\n')
            f.write(f'        <coordinates>{lon_centro},{lat_centro},{alt_centro}</coordinates>\n')
            f.write('      </Point>\n')
            f.write('    </Placemark>\n')

        f.write('    <Placemark id="ruta_circuito">\n')
        f.write('      <name>Ruta del Circuito</name>\n')
        f.write('      <styleUrl>#yellowLine</styleUrl>\n')
        f.write('      <LineString>\n')
        f.write('        <coordinates>\n')

        for tramo in root.findall('tramos/tramo', ns):
            lat = tramo.find('coordenadas/latitud', ns).text
            lon = tramo.find('coordenadas/longitud', ns).text
            alt = tramo.find('coordenadas/altitud', ns).text
            f.write(f'          {lon},{lat},{alt}\n')

        f.write('        </coordinates>\n')
        f.write('      </LineString>\n')
        f.write('    </Placemark>\n')
        
        f.write('  </Document>\n')
        f.write('</kml>\n')

# Usage
convert_xml_to_kml('circuitoEsquema.xml', 'circuito.kml')
