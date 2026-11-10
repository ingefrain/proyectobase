# Guía operativa

Proyecto Base es una base Laravel reutilizable. Los dominios de los proyectos derivados se definen en sus propios repositorios.

## Fuentes de verdad

El código está en este repositorio site. El material auxiliar está en ../xtras y la documentación en ../xtras/docs.
Antes de implementar, identifica la tarea y consulta la guía pertinente. Si no sabes cuál corresponde, empieza por ../xtras/docs/INDICE_DOCUMENTACION.md.

- ROADMAP.md para fases planificadas.
- COMPONENTES_REUTILIZABLES.md antes de crear vistas o componentes.
- POLITICA_DATOS_PRUEBA.md para seeders, factories, fixtures y limpieza.
- DERIVAR_NUEVO_PROYECTO.md para crear un proyecto independiente.
- PREPARACION_PROYECTOS_DERIVADOS.md para límites y dependencias operativas.

Todos esos documentos están en ../xtras/docs. Las migraciones definen el esquema.

## Convenciones visuales

Revisa las vistas existentes. Usa superficie administrativa centrada o amplia y grid responsive. Cada campo agrupa label, control, ayuda y error. Evita tarjetas limitadas por max-width pegadas a la izquierda.
Reutiliza Dropzone, Markdown, Toggle para estados reversibles, Notify para feedback temporal, Alert para información persistente y Modal/Confirm Dialog para confirmaciones. Evita window.alert() y window.confirm(). Revisa los componentes propios antes de introducir otra dependencia.

## Datos y seguridad

Seeders solo estructurales e idempotentes. Factories para tests y fixtures pequeñas por escenario. No crear precarga DEMO global ni depender de registros de desarrollo.
No usar migrate:fresh en desarrollo o producción. Revisa migraciones con migrate --pretend. Conserva los datos manuales del usuario.
No modificar .env sin autorización explícita ni mostrar secretos. Conserva autorización server-side y actor real durante impersonación. No force push.

## Documentación y derivación

Al introducir una convención transversal, decide si corresponde a esta guía, al índice o a una guía temática. Mantén este archivo compacto.
Un proyecto derivado debe conservar el índice operativo y copiar la documentación pertinente, adaptar nombre y propósito y verificar las referencias a xtras. No presupongas que los documentos externos se incluyen en Git.
