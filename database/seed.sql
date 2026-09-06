-- Datos de ejemplo para probar la Fase 1 (galería + dibujo guiado)
INSERT INTO plantillas (nombre, nivel_dificultad, svg_path, zonas_color) VALUES
('Estrella', 'facil', 'estrella.svg', JSON_ARRAY(
  JSON_OBJECT('id', 'zona-cuerpo', 'nombre', 'Cuerpo'),
  JSON_OBJECT('id', 'zona-centro', 'nombre', 'Centro')
));
