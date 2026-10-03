# Estilos públicos con Bootstrap 5 y el diseño habitual

## Alcance

La tienda carga `dist/css/pgcv-public.min.css`, generado desde Bootstrap
5.3.8 mediante `build/scss/pgcv-public.scss`. El encabezado público ya no
carga el CSS base de Bootstrap 3 ni la hoja transitoria
`bootstrap5-public-compat.css`. Conserva el tema AdminLTE 2, sus colores,
Font Awesome y la integración actual de DataTables.

Se personalizan las variables de Sass para conservar tipografía de 14 px,
rejilla con cortes en 768/992/1200 px, contenedores de 750/970/1170 px,
separación de 30 px, botones, campos y modales de 600 px en escritorio.
Las reglas propias mantienen las clases existentes de navegación,
formularios horizontales, grupos de cantidad, tablas y paginación. Las
columnas apiladas conservan sus márgenes verticales. No se modifica el
CSS original dentro de `node_modules`.

Los dos iconos de recuperación que dependían de Glyphicons utilizan ahora
Font Awesome. No cambian campos, CSRF, rutas ni la lógica del servidor.

Esta fase no completa la migración de AdminLTE. Administración sigue
utilizando Bootstrap 3 y su runtime reducido; DataTables mantiene su
adaptador anterior. La auditoría de npm continúa señalando un paquete
moderado, `bootstrap` 3.4.1. No retirarlo hasta convertir sus consumidores.

## Compilación

Sass 1.105.1 está fijado como dependencia de desarrollo en el archivo de
versiones. La generación se comprobó con Node 24.14.1.

```powershell
npm ci --ignore-scripts
npm run sync:frontend
```

Para regenerar solamente el CSS:

```powershell
node tools/build_public_styles.mjs
```

El generador verifica las versiones instaladas de Bootstrap y Sass, imprime
el número de avisos de obsolescencia de Sass y escribe el archivo minificado.
La compilación actual imprime un aviso por `@import`; no impide generar el
CSS. El sincronizador compila antes de copiar los demás recursos. Conservar
el archivo generado en Git: el hosting no necesita Node ni Sass para servirlo.

La personalización sigue la [guía oficial de Sass de Bootstrap](https://getbootstrap.com/docs/5.3/customize/sass/).
La distribución conserva el aviso de licencia MIT de Bootstrap; la licencia
completa está en `bower_components/bootstrap5/LICENSE`.

## Comprobaciones y límites

Se compararon las medidas de portada, navegación, contacto, acceso y
modales antes y después. En escritorio se conservan el contenedor de
1170 px, carrusel de 825 × 350 px, campos de 34 px y pie de página.
En 375 px se conservan las medidas de la cabecera, carrusel y pie; la altura
acumulada del contenido difiere 2 px respecto a la primera captura.
Se comprobaron apertura/cierre del menú, las siete categorías, flechas y
Escape, indicadores y avance del carrusel, y apertura/cierre de modales.

`tests/fixtures/bootstrap5_public_modals.php` utiliza la plantilla real de
perfil y datos ficticios, además de controles representativos de carrito
y entrega. La opción local `?baseline=1` permite comparar con el CSS anterior.
La tabla de escritorio conserva las dimensiones de sus controles después
de adaptar bordes, separación y márgenes de los grupos de entrada.
El fixture solo admite GET, intercepta formularios y no carga base de datos,
correo ni sesión persistente. Está restringido a peticiones locales y se
excluye del hosting.

Las capturas quedan en `storage/visual-review/`; las medidas privadas,
en `storage/backups/bootstrap-public-css-review/`. No se confirmó ningún
pedido ni se envió correo. La comprobación con datos ficticios no sustituye
una revisión posterior del perfil autenticado y la facturación completa.

## Reversión y continuación

Para volver a la fase anterior, restaurar juntos el encabezado público,
la hoja transitoria y los iconos previos desde Git. El JavaScript público
de Bootstrap 5 puede conservarse como en la fase 010. No requiere una
migración ni restauración de base de datos.

El siguiente bloque es administración, AdminLTE y sus plugins. Conservar
el diseño habitual, comprobar tablas y modales y evitar entrar en el panel
que dispara alertas sin un transporte controlado o autorización de correo.
