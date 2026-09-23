<?php
/**
 * Guarda de sessão do painel administrativo.
 * Inclua este arquivo no topo de toda página protegida (depois de db.php/functions.php).
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

function painelEstaAutenticado(): bool
{
    return !empty($_SESSION['painel_usuario_id']);
}

function painelExigirLogin(): void
{
    if (!painelEstaAutenticado()) {
        redirecionar('/painel/login.php');
    }

    $agora = time();
    $limiteSegundos = SESSAO_INATIVIDADE_MINUTOS * 60;

    if (!empty($_SESSION['painel_ultimo_acesso']) && ($agora - $_SESSION['painel_ultimo_acesso']) > $limiteSegundos) {
        session_unset();
        session_destroy();
        session_start();
        redirecionar('/painel/login.php?expirado=1');
    }

    $_SESSION['painel_ultimo_acesso'] = $agora;
}
