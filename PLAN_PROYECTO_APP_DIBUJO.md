# Plan de Proyecto: App de Aprender a Dibujar (Web App)

> Documento de referencia para trabajar con Claude Code en VS Code. Úsalo como "fuente de verdad" del proyecto: pégalo en la raíz del repo como `PLAN.md` y ve marcando el checklist a medida que avanzas.

---

## 1. Resumen del producto

Aplicación web donde el usuario:
1. Escoge un dibujo de una galería de plantillas.
2. La app le muestra, sombreado/resaltado, el trazo que debe seguir paso a paso.
3. Al completar el trazado, se desbloquea una paleta de colores para colorear las zonas del dibujo.
4. Guarda su dibujo terminado.
5. Participa en **grupos de máximo 10 personas**, donde todos dibujan la misma plantilla (o plantillas del reto activo).
6. Los miembros del grupo votan los dibujos de los demás con **1 a 5 estrellas**.
7. Se genera un **ranking de 1° a 3° lugar** por grupo según puntaje promedio/total.
8. Los ganadores reciben **monedas virtuales** según su puesto.

---

## 2. Decisión técnica

- **Tipo de app:** Web App (HTML5 + Canvas + Backend PHP/MySQL), responsive (mobile-first), empaquetable después con **Capacitor** para generar el APK de Android sin rehacer el código.
- **Motivo:** aprovecha tu stack actual (PHP/MySQL), evita duplicar código para Android/iOS, y el dibujo guiado + paleta de colores se resuelve bien con `<canvas>`.

---

## 3. Stack tecnológico propuesto

| Capa | Tecnología |
|---|---|
| Frontend | HTML5 + CSS + JavaScript (Vanilla o Alpine.js para reactividad ligera) |
| Dibujo | Canvas API (`<canvas>`), SVG para las plantillas base |
| Backend | PHP 8.x (estructura MVC simple o un microframework tipo Slim) |
| Base de datos | MySQL / MariaDB |
| Autenticación | Sesiones PHP o JWT si luego se separa API/Frontend |
| Almacenamiento de imágenes | Filesystem del servidor (`/uploads/`) organizado por carpetas, o S3-compatible si escala |
| Empaquetado móvil (fase posterior) | Capacitor (Android/iOS) |
| Control de versiones | Git + GitHub |

---

## 4. Estructura de carpetas sugerida

```
dibujo-app/
├── PLAN.md                     <- este archivo
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   │   ├── canvas-draw.js       (lógica de sombreado guiado)
│   │   │   ├── color-palette.js     (lógica de coloreado)
│   │   │   └── grupos.js
│   │   └── img/
│   │       └── plantillas/          (imágenes/SVG de las plantillas)
│   └── uploads/
│       └── dibujos_usuarios/        (dibujos finales subidos por usuarios)
├── src/
│   ├── Controllers/
│   │   ├── DibujoController.php
│   │   ├── GrupoController.php
│   │   ├── VotoController.php
│   │   └── RankingController.php
│   ├── Models/
│   │   ├── Usuario.php
│   │   ├── Plantilla.php
│   │   ├── DibujoEnviado.php
│   │   ├── Grupo.php
│   │   ├── Voto.php
│   │   └── Moneda.php
│   ├── Services/
│   │   ├── RankingService.php       (calcula 1°,2°,3° lugar)
│   │   └── MonedaService.php        (asigna monedas según puesto)
│   └── Database/
│       └── conexion.php
├── database/
│   └── schema.sql
├── .env.example
└── README.md
```

---

## 5. Modelo de base de datos (borrador)

```sql
-- Usuarios
CREATE TABLE usuarios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  password_hash VARCHAR(255),
  monedas INT DEFAULT 0,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Plantillas de dibujo (los "trazos guía")
CREATE TABLE plantillas (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100),
  nivel_dificultad ENUM('facil','medio','dificil'),
  svg_path VARCHAR(255),        -- ruta al SVG/imagen con las guías de trazo
  zonas_color JSON,             -- definición de zonas para colorear (paths + id)
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Grupos (máx 10 participantes)
CREATE TABLE grupos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100),
  plantilla_id INT,
  estado ENUM('abierto','cerrado','finalizado') DEFAULT 'abierto',
  max_participantes INT DEFAULT 10,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (plantilla_id) REFERENCES plantillas(id)
);

CREATE TABLE grupo_miembros (
  id INT PRIMARY KEY AUTO_INCREMENT,
  grupo_id INT,
  usuario_id INT,
  fecha_union DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (grupo_id) REFERENCES grupos(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  UNIQUE KEY unico_miembro (grupo_id, usuario_id)
);

-- Dibujos enviados (resultado final del usuario)
CREATE TABLE dibujos_enviados (
  id INT PRIMARY KEY AUTO_INCREMENT,
  usuario_id INT,
  grupo_id INT,
  plantilla_id INT,
  imagen_path VARCHAR(255),     -- ruta del PNG final (canvas.toDataURL guardado)
  fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (grupo_id) REFERENCES grupos(id),
  FOREIGN KEY (plantilla_id) REFERENCES plantillas(id)
);

-- Votos (1 a 5 estrellas)
CREATE TABLE votos (
  id INT PRIMARY KEY AUTO_INCREMENT,
  dibujo_id INT,
  votante_id INT,
  estrellas TINYINT CHECK (estrellas BETWEEN 1 AND 5),
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (dibujo_id) REFERENCES dibujos_enviados(id),
  FOREIGN KEY (votante_id) REFERENCES usuarios(id),
  UNIQUE KEY un_voto_por_dibujo (dibujo_id, votante_id)  -- evita votar 2 veces el mismo dibujo
);

-- Ranking final por grupo
CREATE TABLE ranking_grupo (
  id INT PRIMARY KEY AUTO_INCREMENT,
  grupo_id INT,
  dibujo_id INT,
  puesto TINYINT,               -- 1, 2 o 3
  promedio_estrellas DECIMAL(3,2),
  monedas_otorgadas INT,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (grupo_id) REFERENCES grupos(id),
  FOREIGN KEY (dibujo_id) REFERENCES dibujos_enviados(id)
);

-- Historial de monedas
CREATE TABLE monedas_transacciones (
  id INT PRIMARY KEY AUTO_INCREMENT,
  usuario_id INT,
  cantidad INT,             -- positivo (ganancia) o negativo (gasto futuro)
  motivo VARCHAR(150),      -- ej: '1er lugar grupo #12'
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

**Regla anti-trampa importante (definir antes de programar):**
- Un usuario **no puede votar su propio dibujo** (validar `votante_id != dibujo.usuario_id`).
- Considerar exigir un mínimo de votos recibidos (ej. al menos 3 de los 9 restantes) para que un dibujo entre al ranking, evitando manipulación en grupos pequeños.
- Ventana de tiempo límite para votar (ej. 24-48h desde que el grupo se cierra) antes de calcular el ranking.

---

## 6. Manejo de imágenes (dónde y cómo)

Tienes dos tipos de imágenes distintos, no los mezcles:

1. **Imágenes de plantillas (las que tú/admin subes):**
   - Carpeta: `public/assets/img/plantillas/`
   - Formato recomendado: **SVG** (permite definir cada trazo y zona de color como un `<path>` independiente, ideal para el sombreado guiado y el "colorear por zona").
   - Un panel de administración simple (`/admin/plantillas`) para subir nuevas plantillas sin tocar código.

2. **Dibujos finales de usuarios (lo que ellos generan):**
   - Se capturan desde el canvas con `canvas.toDataURL('image/png')`.
   - Se envían por POST (base64 o `FormData` con `Blob`) a `DibujoController.php`.
   - Se guardan en `public/uploads/dibujos_usuarios/{usuario_id}/{dibujo_id}.png`.
   - Guardar solo la **ruta** en la base de datos (`imagen_path`), nunca el binario en la BD.
   - Validar: tamaño máximo (ej. 2MB), tipo MIME real (no confiar solo en la extensión), y sanitizar el nombre de archivo.

---

## 7. Mecánica del dibujo guiado (lo más delicado técnicamente)

Enfoque recomendado por fases:

**Fase simple (MVP):**
- La plantilla SVG tiene los trazos definidos como `<path>` con un `stroke-dasharray` que simula "camino punteado".
- Se muestra el trazo completo semi-transparente como guía fija (no necesita detectar precisión al inicio).
- El usuario dibuja libremente sobre el canvas superpuesto siguiendo la guía.
- Al terminar, presiona "Finalizar trazo" → se habilita la paleta de colores.

**Fase avanzada (implementada):**
- Se calculan puntos a lo largo del `path.guia-trazo` con `getPointAtLength` (cálculo manual, sin librería externa) y se marcan como "cubiertos" cuando el trazo del usuario pasa cerca (tolerancia relativa al tamaño del lienzo).
- Los puntos se pintan en rojo (sin cubrir) o verde (cubiertos) sobre una capa de canvas independiente, dando feedback visual inmediato.
- El botón "Finalizar trazo" queda deshabilitado hasta cubrir al menos el 60% de la guía; un contador de porcentaje en vivo lo indica.
- Pendiente opcional a futuro: revelar el trazo progresivamente en vez de mostrarlo completo desde el inicio (actualmente se muestra todo el camino punteado de una vez, no por segmentos).

**Coloreado por zonas:**
- Cada zona coloreable es un `<path>` cerrado con un `id` único, mapeado en el JSON `zonas_color` de la plantilla.
- Al seleccionar un color de la paleta y hacer click/tap dentro de una zona, se rellena ese `path` (`fill`) con el color elegido — técnica "flood fill por región vectorial" (más simple y confiable que flood-fill por píxeles en un canvas raster).

---

## 8. Roadmap de desarrollo (para trabajar con Claude Code en VS Code)

### Fase 0 – Setup
- [x] Inicializar repo Git
- [x] Crear estructura de carpetas (sección 4)
- [x] Configurar conexión MySQL (`.env`)
- [x] Ejecutar `schema.sql` inicial (validado localmente con MySQL 8 en Docker)

### Fase 1 – Núcleo de dibujo (sin login, sin grupos)
- [x] Galería de plantillas (listar plantillas desde BD, `svg_path` apunta a `public/assets/img/plantillas/`)
- [x] Canvas con trazo guía visible (SVG inline con `path.guia-trazo` punteado + `<canvas>` superpuesto)
- [x] Botón "Finalizar trazo" → mostrar paleta de colores
- [x] Guardar dibujo final como PNG en `uploads/` (por ahora en carpeta de sesión anónima; se asociará a `usuario_id` real en Fase 2)

### Fase 2 – Usuarios
- [x] Registro / login (sesiones PHP)
- [x] Perfil de usuario con historial de dibujos y monedas

### Fase 3 – Grupos
- [ ] Crear grupo (máx 10 miembros)
- [ ] Unirse a grupo (código de invitación o listado de grupos abiertos)
- [ ] Cerrar grupo automáticamente al llegar a 10 **o pasadas 48h desde su creación**, lo que ocurra primero

### Fase 4 – Votación y ranking
- [ ] Vista de votación (mostrar dibujos del grupo, excepto el propio)
- [ ] Guardar votos (1-5 estrellas), validar un voto por usuario/dibujo
- [ ] `RankingService`: calcular promedio y asignar puestos 1°-3° (solo para tabla de posiciones/prestigio)
- [ ] `MonedaService`: por cada dibujo, sumar sus estrellas recibidas × 5 y acreditar esas monedas a su autor (independiente del puesto en el ranking)

### Fase 5 – Pulido y responsive
- [ ] Adaptar UI a mobile (la mayoría probablemente use esto desde celular)
- [ ] Optimizar carga de imágenes (lazy loading, compresión)
- [ ] Panel admin básico para subir plantillas nuevas

### Fase 6 – Empaquetado Android (opcional, al final)
- [ ] Integrar Capacitor sobre el proyecto web
- [ ] Generar APK de prueba
- [ ] Ajustes de UI específicos para app (splash screen, ícono, permisos)

---

## 9. Cómo trabajar esto con Claude Code (flujo sugerido)

1. Crea el repo vacío en VS Code con la estructura de la sección 4.
2. Pega este archivo como `PLAN.md` en la raíz.
3. Abre Claude Code en la terminal integrada y dale contexto explícito, por ejemplo:
   > "Lee PLAN.md. Vamos a construir la Fase 1: núcleo de dibujo. Empieza creando `database/schema.sql` con las tablas de la sección 5, y luego el controlador `DibujoController.php` para guardar el PNG final en `public/uploads/dibujos_usuarios/`."
4. Trabaja **una fase a la vez** (no le pidas todo el proyecto de una vez) — así puedes revisar y probar cada parte antes de avanzar.
5. Pide a Claude Code que vaya actualizando los checkboxes `[ ]` → `[x]` de este mismo `PLAN.md` a medida que completas cada tarea, así el archivo queda como bitácora viva del proyecto.
6. Para la parte de canvas/SVG (la más visual), es útil pedirle que genere primero un HTML de prueba aislado (`test-canvas.html`) antes de integrarlo al flujo PHP completo.

---

## 10. Puntos abiertos que debes decidir antes de programar

- [x] **Monedas:** no es un premio fijo por puesto. Cada estrella recibida en los votos vale **5 monedas** para el autor del dibujo (`monedas = estrellas_recibidas_del_dibujo * 5`), sin importar si el dibujo quedó 1°, 2°, 3° o fuera del podio. El ranking 1°-3° (sección 5/`ranking_grupo`) sigue existiendo para prestigio/tabla de posiciones, pero **no determina el pago de monedas**; `MonedaService` calcula el pago por dibujo sumando sus votos, no por puesto. `ranking_grupo.monedas_otorgadas` pasa a ser informativo (guarda cuántas monedas generó ese dibujo, no un bono extra por posición).
- [ ] ¿Las plantillas las subes tú manualmente al inicio, o habrá un editor visual para crearlas?
- [x] **Cierre de grupo:** si no llega a 10 miembros, se cierra igual pasado un plazo fijo (ej. 48h desde su creación) y pasa a votación con los miembros que haya.
- [ ] ¿Habrá un límite de tiempo para completar el dibujo una vez unido al grupo?
- [ ] ¿Las monedas virtuales tendrán algún uso futuro (desbloquear plantillas premium, cosméticos, etc.) o son solo puntaje/prestigio?
