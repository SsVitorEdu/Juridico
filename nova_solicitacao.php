<?php
// Arquivo: C:\xampp\htdocs\juridico\nova_solicitacao.php (CORRIGIDO SEM EMPRESA)

session_start();
require_once 'includes/conexao.php';

$_SESSION['gestor_id'] = 1;
$_SESSION['nome_gestor'] = 'Vitor Eduardo Lima da Rocha';

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_solicitante = $_SESSION['gestor_id'];
    $nome_solicitante = $_SESSION['nome_gestor'];
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];

    try {
        $sql_solicitacao = "INSERT INTO juridico_solicitacoes (id_solicitante, nome_solicitante, titulo, descricao, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql_solicitacao);
        $stmt->execute([$id_solicitante, $nome_solicitante, $titulo, $descricao, 'Nova']);
        
        $id_nova_solicitacao = $pdo->lastInsertId();

        if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] == 0) {
            $pasta_uploads = 'uploads/';
            $nome_arquivo_unico = uniqid() . '_' . basename($_FILES['anexo']['name']);
            $caminho_completo = $pasta_uploads . $nome_arquivo_unico;

            if (move_uploaded_file($_FILES['anexo']['tmp_name'], $caminho_completo)) {
                $sql_anexo = "INSERT INTO juridico_anexos (solicitacao_id, nome_arquivo, caminho_arquivo) VALUES (?, ?, ?)";
                $stmt_anexo = $pdo->prepare($sql_anexo);
                $stmt_anexo->execute([$id_nova_solicitacao, basename($_FILES['anexo']['name']), $caminho_completo]);
            }
        }
        $mensagem = "Solicitação enviada com sucesso! ID: " . $id_nova_solicitacao;

    } catch (PDOException $e) {
        $mensagem = "Erro ao enviar solicitação: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nova Solicitação Jurídica - Portal do Gestor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="brand"><span class="logo">RW</span><span>Portal do Gestor</span></div>
    </header>
    <div class="container">
        <div class="form-card">
            <h2 class="form-title">Nova Solicitação Jurídica</h2>
            <?php if (!empty($mensagem)): ?>
                <div class="message <?php echo strpos($mensagem, 'Erro') === false ? 'message-success' : 'message-error'; ?>"><?php echo $mensagem; ?></div>
            <?php endif; ?>
            <form action="nova_solicitacao.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título da Solicitação</label>
                    <input type="text" id="titulo" name="titulo" required>
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição da Solicitação</label>
                    <textarea id="descricao" name="descricao" rows="6" required></textarea>
                </div>
                <div class="form-group">
                    <label for="anexo">Anexar Arquivo (Opcional)</label>
                    <input type="file" id="anexo" name="anexo">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn">Enviar Solicitação</button>
                    <a href="dashboard_gestor.php" class="btn btn-secondary" style="text-decoration: none;">Voltar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>