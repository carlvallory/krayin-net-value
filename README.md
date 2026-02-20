# Krayin Net Value Package

Un paquete personalizado para Krayin CRM que habilita la gestión híbrida de **Valores Netos (Net Value)** y **Valores Brutos (Gross Value)** en los Leads.

## 🚀 El Problema
Por defecto, Krayin CRM maneja un único monto por Lead a través del campo nativo `lead_value`. Cuando las oportunidades de venta provienen de pasarelas de pago (como PagoPar o Bancard), el monto total facturado (Bruto) difiere del dinero real que ingresa a la cuenta (Neto) debido a las comisiones bancarias o de la pasarela.

Guardar ambos datos es crítico:
*   **Valor Bruto:** Necesario para conciliación, facturación y enviar el estado de cuenta al cliente.
*   **Valor Neto:** Necesario para calcular los ingresos reales (Caja/KPI) en los Dashboards Financieros.

## 💡 La Solución (Arquitectura Híbrida)
Este paquete implementa una solución de **Opción B Híbrida** 100% aislada que no toca el núcleo (core) de Krayin:

1.  **Migración de Columna Física:** Crea una nueva columna `net_value` en la tabla SQL `leads`, permitiendo que el Dashboard de Krayin y otros paquetes como `KrayinFinancialReports` realicen consultas y sumatorias (`SUM(net_value)`) a altísima velocidad.
2.  **Sistema EAV como Fallback (Backup):** Krayin usa un diseño Entidad-Atributo-Valor. El plugin base de WooCommerce ahora enviará el neto a través de la API REST nativa como un atributo extra llamado `custom_net_value`. 
3.  **Observer/Event Listener:** Este paquete registra el `LeadSaveListener`. Cada vez que Krayin guarda o actualiza un Lead, el Listener toma automáticamente el valor de `custom_net_value` (EAV) y lo inyecta en la columna rápida `net_value`.
4.  **Resiliencia Reversible:** Si desinstalas este paquete, la columna física `net_value` desaparecerá restaurando intacto el core de Krayin, pero **nunca perderás los importes financieros** porque siempre existirán respaldados en las tablas dinámicas de atributos `custom_net_value`.

---

## ⚙️ Instalación en Producción

Sigue estos pasos para instalar el paquete en tu entorno Krayin CRM en vivo.

### Paso 1: Copiar Archivos
Sube la carpeta `KrayinNetValue` por FTP o SSH al directorio de paquetes de tu Krayin:
```text
/tu_proyecto_krayin/packages/CarlVallory/KrayinNetValue
```

### Paso 2: Registrar el Paquete
Abre el archivo maestro `composer.json` que se encuentra en la raíz principal de Krayin (`/tu_proyecto_krayin/composer.json`).

Busca el bloque `"require": { ... }` y agrega la dependencia de desarrollo:
```json
"require": {
    "carlvallory/krayin-net-value": "@dev"
}
```

Asegúrate de que tus repositorios locales estén mapeados en el mismo archivo `composer.json`:
```json
"repositories": [
    {
        "type": "path",
        "url": "packages/*/*",
        "options": {
            "symlink": true
        }
    }
]
```

### Paso 3: Despliegue (Consola)
Ingresa por SSH a la raíz de tu proyecto Krayin CRM y ejecuta los siguientes comandos de despliegue:

```bash
# 1. Instalar dependencias para enlazar el paquete local
composer update carlvallory/krayin-net-value

# 2. Ejecutar las migraciones de Base de Datos para crear la columna 'net_value'
php artisan migrate

# 3. Limpiar las cachés de Laravel y Eventos
php artisan optimize:clear
```

La migración, además de crear la columna vacía, ejecutará un script que automáticamente buscará historiales antiguos de Leads y migrará su atributo EAV `custom_net_value` hacia la nueva columna para que tus Dashboards retroactivos reflejen los montos desde el día 1.

---

## 🛠️ Modificaciones Satélites Requeridas
Para que este paquete cobre sentido, recuerda que tu ecosistema debe cumplir 2 factores externos:

1.  **WooCommerce Plugin:** Debes actualizar el archivo `class-data-mapper.php` del plugin `woocommerce-krayin-crm` para que aplique los cálculos de comisión de PagoPar y envíe la clave `custom_net_value` en su payload hacia Krayin.
2.  **Krayin Financial Reports:** Debes ir a tu otro paquete `CarlVallory/KrayinFinancialReports` (específicamente al `FinancialReportController.php`) y reemplazar todas las consultas que digan `sum('lead_value')` por `sum('net_value')`. (Este cambio ya se realizó en el entorno de desarrollo local).
