<?php
/**
 * Configuração central do site Grupo Pé na Areia.
 * Preencha DB_NAME / DB_USER / DB_PASS com os dados criados no cPanel
 * (MySQL Databases) antes de subir para a hospedagem.
 */

// ---------- Banco de dados ----------
define('DB_HOST', 'localhost');
define('DB_NAME', 'troque_nome_do_banco');
define('DB_USER', 'troque_usuario_mysql');
define('DB_PASS', 'troque_senha_mysql');

// ---------- Contato ----------
define('WHATSAPP_PRINCIPAL', '5513988381441'); // (13) 98838-1441
define('WHATSAPP_SECUNDARIO', '5511982885108'); // (11) 98288-5108
define('EMAIL_CONTATO', 'rcosta2322@gmail.com');
define('EMAIL_CONTATO_2', 'contato@grupopenaareia.com.br');
define('SITE_URL', 'https://grupopenaareia.com.br');
define('CRECI', '232200-F');
define('ENDERECO', 'Bairro Florida Mirim, Mongaguá – SP');

// ---------- Redes sociais ----------
define('SOCIAL_INSTAGRAM', 'https://www.instagram.com/rcosta2322/');
define('SOCIAL_FACEBOOK', 'https://www.facebook.com/ronaldo.costa.868222');
define('SOCIAL_TIKTOK', 'https://www.tiktok.com/@ronaldocosta2581?_r=1&_t=ZS-99X6HmLJ3Ce');
define('SOCIAL_LINKEDIN', 'https://www.linkedin.com/in/ronaldo-costa-6b876a33/');
define('SOCIAL_YOUTUBE', 'https://www.youtube.com/@ronaldocosta9425');

// ---------- Segurança do login (painel) ----------
define('LOGIN_MAX_TENTATIVAS', 5);
define('LOGIN_BLOQUEIO_MINUTOS', 15);
define('SESSAO_INATIVIDADE_MINUTOS', 30);

// ---------- Upload de fotos ----------
define('UPLOAD_MAX_FOTOS', 10);
define('UPLOAD_MAX_TAMANHO_MB', 5);
define('UPLOAD_TIPOS_PERMITIDOS', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOAD_DIR', __DIR__ . '/uploads/imoveis');
define('UPLOAD_URL', '/uploads/imoveis');

// ---------- Sessão ----------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');
