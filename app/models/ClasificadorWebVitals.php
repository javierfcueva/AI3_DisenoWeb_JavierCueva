<?php

declare(strict_types=1);

/**
 * Clasifica una auditoría según los umbrales de Core Web Vitals.
 *
 * Cada métrica es buena, por mejorar o deficiente. La auditoría es:
 * - Crítica, si alguna métrica es deficiente.
 * - Aprobada, si las tres métricas son buenas.
 * - Mejorable, en los demás casos.
 *
 * El checklist de calidad no interviene: se informa aparte.
 */
final class ClasificadorWebVitals
{
    public const BUENA = 'buena';
    public const POR_MEJORAR = 'por_mejorar';
    public const DEFICIENTE = 'deficiente';

    public const APROBADA = 'aprobada';
    public const MEJORABLE = 'mejorable';
    public const CRITICA = 'critica';

    /**
     * Límites superiores, incluidos, de "buena" y de "por mejorar". Se expresan como enteros
     * en la unidad más pequeña de cada columna para comparar sin errores de redondeo:
     * LCP en centésimas de segundo (DECIMAL(5,2)) y CLS en milésimas (DECIMAL(5,3)).
     */
    private const UMBRALES = [
        'lcp_segundos' => ['decimales' => 2, 'buena' => 250, 'por_mejorar' => 400], // 2,5 s y 4 s
        'inp_ms' => ['decimales' => 0, 'buena' => 200, 'por_mejorar' => 500],       // 200 ms y 500 ms
        'cls' => ['decimales' => 3, 'buena' => 100, 'por_mejorar' => 250],          // 0,1 y 0,25
    ];

    private const ETIQUETAS = [
        self::BUENA => 'Buena',
        self::POR_MEJORAR => 'Por mejorar',
        self::DEFICIENTE => 'Deficiente',
        self::APROBADA => 'Aprobada',
        self::MEJORABLE => 'Mejorable',
        self::CRITICA => 'Crítica',
    ];

    /**
     * @param array<string, mixed> $auditoria Fila con lcp_segundos, inp_ms y cls.
     * @return array{resultado: string, metricas: array<string, string>}
     */
    public static function clasificar(array $auditoria): array
    {
        $metricas = [];

        foreach (array_keys(self::UMBRALES) as $metrica) {
            $metricas[$metrica] = self::calificar($metrica, $auditoria[$metrica]);
        }

        $resultado = match (true) {
            in_array(self::DEFICIENTE, $metricas, true) => self::CRITICA,
            array_values(array_unique($metricas)) === [self::BUENA] => self::APROBADA,
            default => self::MEJORABLE,
        };

        return ['resultado' => $resultado, 'metricas' => $metricas];
    }

    /**
     * Califica una métrica: buena, por mejorar o deficiente.
     */
    public static function calificar(string $metrica, string|int $valor): string
    {
        $umbral = self::UMBRALES[$metrica];
        $entero = self::aEntero((string) $valor, $umbral['decimales']);

        return match (true) {
            $entero <= $umbral['buena'] => self::BUENA,
            $entero <= $umbral['por_mejorar'] => self::POR_MEJORAR,
            default => self::DEFICIENTE,
        };
    }

    /**
     * Cuenta las auditorías de cada clasificación y calcula el porcentaje de aprobadas.
     *
     * @param list<array{resultado: string}> $clasificaciones
     * @return array{total: int, aprobada: int, mejorable: int, critica: int, porcentaje_aprobacion: float}
     */
    public static function resumir(array $clasificaciones): array
    {
        $conteo = [self::APROBADA => 0, self::MEJORABLE => 0, self::CRITICA => 0];

        foreach ($clasificaciones as $clasificacion) {
            $conteo[$clasificacion['resultado']]++;
        }

        $total = count($clasificaciones);

        return [
            'total' => $total,
            ...$conteo,
            // Con división exacta PHP devuelve un entero; se convierte para que el tipo sea siempre float.
            'porcentaje_aprobacion' => $total === 0 ? 0.0 : (float) ($conteo[self::APROBADA] * 100 / $total),
        ];
    }

    public static function etiqueta(string $clave): string
    {
        return self::ETIQUETAS[$clave];
    }

    /**
     * Convierte un decimal en texto, como "2.5" o "0.100", en un entero exacto con la cantidad
     * de decimales indicada: aEntero("2.5", 2) es 250. La base de datos devuelve los DECIMAL
     * como texto, y así no se pasa por float.
     */
    private static function aEntero(string $valor, int $decimales): int
    {
        [$entero, $fraccion] = array_pad(explode('.', trim($valor), 2), 2, '');
        $fraccion = str_pad(substr($fraccion, 0, $decimales), $decimales, '0');

        return (int) $entero * 10 ** $decimales + (int) $fraccion;
    }
}
