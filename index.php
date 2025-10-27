<?php
// Arquivo: C:\xampp\htdocs\juridico\index.php (O nosso "Porteiro Automático")

session_start();

// --- LÓGICA DE TESTE ---
// Para testar, você precisará definir a sessão ANTES de acessar esta página.
// Por exemplo, crie um arquivo de teste que defina a sessão e depois redirecione para cá.
// Exemplo de como a sessão deve estar:
// $_SESSION['gestor_id'] = 1;
// $_SESSION['nome_gestor'] = 'Vitor Eduardo Lima da Rocha';
// $_SESSION['nivel_acesso'] = 'gestor'; // ou 'juridico'
// --- Fim da Lógica de Teste ---


// 1. O porteiro verifica se o usuário está logado e se tem um nível de acesso.
if (isset($_SESSION['gestor_id']) && isset($_SESSION['nivel_acesso'])) {

    // 2. Se o nível de acesso for 'juridico', ele manda para o dashboard do jurídico.
    if ($_SESSION['nivel_acesso'] == 'juridico') {
        header('Location: dashboard_juridico.php');
        exit(); // Encerra o script para garantir que nada mais seja executado.
    }
    // 3. Para qualquer outro nível de acesso (como 'gestor'), ele manda para o dashboard do gestor.
    else {
        header('Location: dashboard_gestor.php');
        exit();
    }

} else {
    // 4. Se o usuário não estiver logado, ele é expulso para a página de login principal.
    // ATENÇÃO: O caminho '../login.php' presume que o login do seu portal principal está na pasta htdocs.
    // Ajuste este caminho se necessário.
    echo "Você não está logado. Redirecionando para o login...";
    // header('Location: ../login.php'); 
    exit();
}
?>