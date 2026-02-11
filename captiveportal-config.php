<?php
/**
 * Configurações do Captive Portal
 */

// --- AMBIENTE DE PRODUÇÃO ---
define("DEBUG", false);
define("DBHOST", "localhost"); // Se o MySQL estiver no próprio pfSense
define("DBUSER", "radius");
define("DBPASS", "radpass");   // Altere para sua senha real
define("DBNAME", "radius");

// Configurações de exibição e identificação
$brand = "NOME DA SUA EMPRESA";  // Aparecerá no topo do card e Termos LGPD
$localName = "NOME DO CLIENTE";   // Nome do local onde o Wi-Fi está instalado
$linkSite = "www.seusite.com.br";
$identificator = "CLIENTE_01";    // ID para diferenciar leads de clientes diferentes no banco

// Controle de Campos (Para compatibilidade com o index.php que criamos)
$askForEmailAddress = false; // Estamos usando WhatsApp no lugar
$askForTermsOfUse   = true;

// --- IDIOMAS ---
$validLanguages = array('pt', 'en'); 
$language = "pt"; // Padrão Brasil

/**
 * Função de Tradução (Simplificada para os campos atuais)
 */
function t($string) {
    global $language, $brand, $localName;

    $texts = array(
        'pt' => array(
            'pageTitle_string' => "Acesso Wi-Fi",
            'welcome_string' => "Bem-vindo",
            'welcomeMessage_string' => "Conecte-se à rede do $localName.",
            'connect_string' => "Liberar Acesso",
            'termsOfUse_string' => "Termos de Uso",
            'termsOfUseAccept_string' => "Eu aceito os",
            'databaseConnectErrorMessage_string' => "Erro ao conectar ao banco de dados.",
            'macAdressErrorMessage_string' => "Não foi possível identificar seu dispositivo (MAC)."
        ),
        'en' => array(
            'pageTitle_string' => "Wi-Fi Access",
            'welcome_string' => "Welcome",
            'welcomeMessage_string' => "Connect to $localName network.",
            'connect_string' => "Connect Now",
            'termsOfUse_string' => "Terms of Use",
            'termsOfUseAccept_string' => "I accept the",
            'databaseConnectErrorMessage_string' => "Database connection error.",
            'macAdressErrorMessage_string' => "MAC Address not found."
        )
    );

    // Retorna a tradução ou a própria chave se não encontrar
    return isset($texts[$language][$string]) ? $texts[$language][$string] : $string;
}

// Formatação de data simples para logs
$today = date('d/m/Y');

// O seu retorno dinâmico original adaptado
if (isset($$string)) {
    return $$string; 
} else {
    // Para as novas chamadas de tradução via função t()
    return t($string);
}
?>