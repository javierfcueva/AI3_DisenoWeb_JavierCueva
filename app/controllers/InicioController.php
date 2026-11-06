<?php

declare(strict_types=1);

final class InicioController
{
    public function index(): void
    {
        renderizar('inicio', ['titulo' => 'Inicio']);
    }
}
