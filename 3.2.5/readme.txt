=== Adsmonetizer ===
Tags: adsense, ads, posts
Requires at least: 3.6+
Tested up to: 6.4.3
Stable tag: 3.2.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Añade anuncios (AdSense, Amazon,...) por todo tu blog. Extremadamente sencillo, rápido, ligero y con gran flexibilidad.

== Description ==
NOW WE SUPPORT MORE LANGUAGES (LIKE ENGLISH)

Lee más información en: https://adsplugin.net

**AdSensei B30** inserta tus anuncios de AdSense (o de cualquier otro proveedor) en cualquier parte del contenido de tus posts de la forma más flexible y precisa que existe

= Características =
* Extremadamente sencillo y fácil de configurar. Sin pestañas, en único scroll podrás ver todos los ajustes.
* Shortcodes configurables(ver FAQ)
* Posibilidad de activar/desactivar anuncios y también de renombrar sus títulos.
* Compatible con AdSense y cualquier otro proveedor de anuncios que funcione de manera similar
* Hasta 10 anuncios insertables en cualquier parte del contenido de los posts
* Flexible: la inserción de los anuncios es configurable: al comienzo del post, a mitad del post, al final, después del párrafo 1,2,3…10, después de la primera, segunda o tercera etiqueta h2 o h3... etc
* Rapidez: al nivel de cualquier otro plugin de AdSense que puedas encontrar
* Ligero: ocupa menos de 20Kbs, frente a cientos de Kbs de plugins similares

== Installation ==
1. Puedes:
 * Subir la carpeta `adsensei-b30` al directorio `/wp-content/plugins/` vía FTP.
 * Subir el archivo ZIP completo vía *Plugins -> Añadir nuevo -> Subir* en el Panel de Administración de tu instalación de WordPress.
 * Buscar **AdSensei B30** en el buscador disponible en *Plugins -> Añadir nuevo* y pulsar el botón *Instalar ahora*.
2. Activar el plugin a través del menú *Plugins* en el Panel de Administración de WordPress.
3. Configurar el plugin con el botón *Ajustes* o en *Ajustes -> Enlaces permanentes*.
4. Listo, ahora ya puedes disfrutar de él.

== Frequently Asked Questions ==
= ¿Cómo puedo \'desactivar\' el plugin en una página o post concreto? =
   Puedes usar el shortcode [sin_anuncios_b30] en cualquier post o página para que el plugin no inserte ningún anuncio.
= ¿Cómo puedo insertar \'manualmente\' anuncioes en una página o post concreto? =
   Puedes anular la funcionalidad por defecto del plugin e insertar tus anuncios en sitios concretos usando shortcodes del tipo [anuncio_b30 id=x] siendo X el número de anuncio que quieras insertar. Por ejemplo para insertar el anuncio 1: [anuncio_b30 id=1]
= ¿Qué diferencia hay entre las alineaciones \'Izquierda Envuelto\' o \'Derecha Envuelto\' y simplemente \'Izquierda\' o Derecha\'? =
  Las alineaciones que llevan \'Envuelto\' en su descripción, se integrarán en el mismo párrafo que el texto, es decir, el texto del párrafo \'envolverá\' el anuncio. Si eliges una configuración \'no envuelta\' el anuncio no tendrá nada de texto a los lados: será como un párrafo propio.
  Te aconsejamos echar un vistazo a las capturas de pantalla para visualizarlo mejor: Capturas de pantalla

= ¿Cómo se configura? =

 * Pegar el código AdSense en cualquiera de los hasta 10 anuncios disponibles, indicar en que posición quieres que salga, elegir alineación y margen.
 * Se pueden configurar hasta 10 anuncios en total, cada uno en una posición diferente.
 * Pulsar botón guardar y el proceso habrá terminado

== Screenshots ==
1. Panel de administración del plugin: hasta 10 anuncios altamente configurables
2. Comparación de alineación \'Izquierda Envuelto\' vs \'Izquierda\' (disponible también \'Derecha Envuelto\', \'Derecha\' y \'Centrado\').

== Changelog ==
= 3.1.3 =
* Resolvemos vulnerabilidad XSS.
= 3.1.0 =
* Agregamos opción para migrar los adsense a la nueva versión
* Resolvemos conflictos de compatibilidad con algunos temas.
= 3.0.0 =
* Actualizado el plugin para ser compatible con las últimas versiones de WordPress, incluido WordPress 6.2.2.
* Resolvemos conflictos de compatibilidad con algunos temas.
* Nueva interfaz de usuario para nuevas instalaciones.
= 2.0.3 =
* Actualizado el plugin para ser compatible con las últimas versiones de WordPress, incluido WordPress 6.0.1.
* Resolvemos conflictos de compatibilidad con algunos temas.
= 2.0.2 =
* Agregamos formulario para el newsletter y actualizamos los estilos
= 2.0.1 =
* Resolvemos conflicto al actualizar el plugin
= 2.0 =
* Actualizado el plugin para ser compatible con las últimas versiones de WordPress, incluido WordPress 6.0.1.
= 1.9.9.9 =
* Añadido soporte para múltiples idiomas del plugin / Added support for other languages in the plugin code
= 1.9.9.8 =
* Arreglos menores.
= 1.9.9.7 =
* Actualizado el plugin para ser compatible con las últimas versiones de WordPress, incluido WordPress 5.7.1.
= 1.9.9.6 =
* Fix texto en home, paginaciones.
= 1.9.3.1 =
* Fix de la versión anterior, debido a un error con la localización del idioma
= 1.9.3 =
* Solucionada incidencia con la etiqueta  cuando estaba pegada a un párrafo
= 1.9.2 =
* Revertida última actualización debido a problema con aparición de barras de desplazamiento en los anuncios
= 1.9.1 =
* Mejorada la visualización de los anuncios en dispositivos moviles
= 1.9 =
* Fix bug offset 10
= 1.8.0   =
* Fix para arreglar bug de que a veces no se mostraba bien los anuncios según el filtro pc/móviles.
= 1.0.7.2 =
* Añadida funcionalidad para mostrar anunción en función del dispositivo (pc/tablets)
= 1.0.7.1 =
* Fix shortcode [sin_anuncios_b30]
= 1.0.7 =
* Los guardados de ajustes ahora se realizan sin necesidad de recargar la página
* Ligeros cambios en la implementación de los shortcodes, y ajustes visuales en el menu de administración.
= 1.0.6 =
* Añadida opción de añadir un nombre descriptivo a cada anuncio
* Añadida opción de activar/desactivar anuncios
* Añadida opción de usar anuncios únicamente como shorcodes
= 1.0.5 =
* Corregido botón paypal de donaciones
= 1.0.4 =
* Añadidos shortcodes para inserción de anuncios
= 1.0.3.1 =
* Corrige el fallo Parse error: syntax error, unexpected ‘[‘ in adsensei-b30/adSenseiB30.php on line 39, en versiones de wordpress que corran sobre versiones de PHP antiguas (< 5.4)
= 1.0.3 =
* Pequeñas mejoras varias.
= 1.0.2 =
* Añadido botón de salvar opciones al principio del panel de administración. Añadido shortcode [sin_anuncios_b30] para no mostrar ningún anuncio en la página o post que se inserte
= 1.0.1 =
* Arreglados bugs de filtrado \'sólo en posts\' y el fondo amarillo en los anuncios
= 1.0 =
* Primera versión liberada

== Upgrade Notice ==
Mejorada la compatibilidad con las últimas versiones de WordPress.
