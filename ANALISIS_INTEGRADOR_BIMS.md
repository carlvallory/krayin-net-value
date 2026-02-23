# Análisis del Integrador de Facturación (BIMS) y Repercusiones en WooCommerce

Este documento recopila todos los descubrimientos realizados al analizar el código fuente en Python del proyecto `integrador-facturacion-electronica` (específicamente `core/views.py`), y detalla cómo estas configuraciones preexistentes afectan a nuestro plugin `woocommerce-krayin-crm` para el cálculo del Valor Neto e IVA.

## Contexto
El integrador en Python se encarga de leer los pedidos de WooCommerce y enviarlos al sistema BIMS para la facturación electrónica. La forma en que este script procesa los datos financieros nos revela cómo los productos y los impuestos están realmente cargados en la base de datos de WooCommerce.

---

## 1. Tratamiento del Total Bruto y los Impuestos Nativos
### Descubrimiento en Python:
```python
item_total_with_tax = float(item.get("total", 0)) + float(item.get("total_tax", 0))
price_per_item = item_total_with_tax / item_quantity
```
El script en Python se ve obligado a sumar el `total` (que en la API de WooCommerce representa el subtotal del ítem sin impuestos) con el `total_tax` (el impuesto calculado) para obtener el precio real que pagó el cliente. BIMS espera recibir el precio unitario con el IVA ya incluido.

### Implicancias para WooCommerce:
Esto confirma que en la tienda de WooCommerce **SÍ se están calculando impuestos de forma nativa**. WooCommerce está separando el valor neto del impuesto en las órdenes.

### Recomendación/Solución para `woocommerce-krayin-crm`:
*   **Ajuste del cálculo del IVA personalizado:** Si en el plugin de Krayin usamos un cálculo de IVA porcentual personalizado (ej. `10%`), debemos tener mucho cuidado de no aplicarlo sobre `$item->get_total()` si este valor ya viene *sin* el IVA de WooCommerce. 
*   **Fórmula Correcta:** Deberíamos emular a Python sumando `$item->get_total() + $item->get_total_tax()` para obtener el **Bruto Total Absoluto** y, sobre *ese* monto, hacer la resta matemática `Bruto - (Bruto / 1.10)` para encontrar el monto del IVA a descontar. Si no lo hacemos así, estaríamos descontando el IVA dos veces.

---

## 2. Hardcoding de Productos y Precios Especiales
### Descubrimiento en Python:
```python
elif search_id in [19657, 14372, 8421, 3681, 24482]:
    # Lógica hardcodeada para productos con precios especiales...
    sale_products.append({
        "product_id": bims_id,
        "quantity": 1.00,
        "price": round(float(item.get("total", 0)), 2),
    })
```
El desarrollador original tuvo que interceptar IDs específicos de productos e inyectarles una lógica matemática distinta a la del resto del catálogo. 

### Implicancias para WooCommerce:
El catálogo de WooCommerce es irregular. Estos productos probablemente tienen "mañas" en su configuración (quizás están marcados incorrectamente como imponibles o exentos en su ficha de producto). En lugar de arreglar el catálogo, se parcheó el código.

### Recomendación/Solución para `woocommerce-krayin-crm`:
*   Nuestro plugin confía ciegamente en la bandera `$product->is_taxable()` de WooCommerce.
*   Si alguno de esos IDs hardcodeados requiere que se le descuente (o no) el IVA y su configuración en WooCommerce está equivocada, Krayin registrará un Valor Neto erróneo para esas ventas específicas.
*   **Solución a largo plazo:** Auditar en el administrador de WooCommerce la pestaña "General > Estado del impuesto" de los IDs `19657`, `14372`, `8421`, `3681` y `24482`, para asegurar que su flag imponible sea correcta.

---

## 3. Descuentos del 100% y Entradas de "Cortesía"
### Descubrimiento en Python:
```python
if total == 0 and discount > 0:
    logger.info(f"Order {order_id} skipped: 100% discount.")
    return Response(data={"status": "Descuento 100%"})

if order.get("payment_method_title") == "Cortesía":
    return Response(data={"status": "Cortesía"})
```
El integrador directamente **ignora** y no procesa ventas ficticias de $0 o métodos de pago de cortesía.

### Implicancias para WooCommerce:
Existen pedidos legítimos que entran a WooCommerce con valor $0 (generalmente invitaciones, pases de staff o premios). 

### Recomendación/Solución para `woocommerce-krayin-crm`:
*   Por defecto, nuestro plugin de Krayin procesará estos pedidos y creará Leads con Valor Bruto: 0 y Valor Neto: 0. 
*   Esto es matemáticamente correcto, y útil a nivel CRM para registrar el nombre y email del cliente, así como sus parámetros UTM (origen de tráfico). 
*   No obstante, si el departamento comercial no quiere ver tratos de $0 ensuciando el Pipeline, se podría agregar un filtro `if ( $order_total <= 0 ) return;` en la función `map_order_to_lead()`. Por ahora, se sugiere mantenerlos por el valor del dato del cliente.

---

## 4. Tipos de Documento y Ausencia de SKU
### Descubrimiento en Python:
El integrador tiene validaciones extensas que rechazan la sincronización si a los productos les falta el SKU, o si el SKU es "0". 

### Implicancias para WooCommerce:
Esto indica que el catálogo padece ocasionalmente de productos mal creados sin SKU. 

### Recomendación/Solución para `woocommerce-krayin-crm`:
*   Nuestro plugin no requiere SKUs para mapear los valores financieros totales, por lo que será más resistente (tolerante a fallos) que el integrador BIMS en este aspecto. Seguiremos recibiendo todos los Leads sin importar que tengan o no SKU.

---

## Conclusión
El comportamiento del integrador Python sugiere que la API nativa de WooCommerce está enviando impuestos disgregados. Para que el **Porcentaje de IVA Personalizado** de nuestro plugin Krayin funcione a la perfección sin descontar el IVA dos veces, debemos asegurarnos de aplicar la fórmula inversa siempre tomando como base el Subtotal + Impuestos que provee la línea del ítem en WooCommerce.

*Documento creado para revisión y estudio futuro. No se aplicarán cambios al código de importación sin previa autorización.*
