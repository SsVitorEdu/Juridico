<?php
// Arquivo: C:\xampp\htdocs\juridico\solicitacao_view.php (VERSÃO ATUALIZADA)

session_start();
require_once 'includes/conexao.php';

// --- LÓGICA DE TESTE: Simula um usuário logado ---
$_SESSION['gestor_id'] = 1;
$_SESSION['nome_gestor'] = 'Vitor Eduardo Lima da Rocha';
$_SESSION['nivel_acesso'] = 'gestor';
// --- Fim da Lógica de Teste ---

$solicitacao_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($solicitacao_id === 0) {
    die("Solicitação não encontrada.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_followup'])) {
    $mensagem = $_POST['mensagem'];
    $usuario = $_SESSION['nome_gestor'];

    if (!empty($mensagem)) {
        try {
            $sql = "INSERT INTO juridico_followups (solicitacao_id, usuario, mensagem) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$solicitacao_id, $usuario, $mensagem]);
            
            header("Location: solicitacao_view.php?id=" . $solicitacao_id);
            exit();

        } catch (PDOException $e) {
            $erro_followup = "Erro ao adicionar follow-up: " . $e->getMessage();
        }
    }
}

try {
    // Busca a solicitação
    $sql_solicitacao = "SELECT * FROM juridico_solicitacoes WHERE id = ?";
    $stmt_solicitacao = $pdo->prepare($sql_solicitacao);
    $stmt_solicitacao->execute([$solicitacao_id]);
    $solicitacao = $stmt_solicitacao->fetch();

    if (!$solicitacao) {
        die("Solicitação não encontrada.");
    }

    // Busca os follow-ups associados
    $sql_followups = "SELECT * FROM juridico_followups WHERE solicitacao_id = ? ORDER BY data ASC";
    $stmt_followups = $pdo->prepare($sql_followups);
    $stmt_followups->execute([$solicitacao_id]);
    $followups = $stmt_followups->fetchAll();
    
    // LÓGICA ADICIONADA: Busca os anexos originais
    $sql_anexos = "SELECT * FROM juridico_anexos WHERE solicitacao_id = ?";
    $stmt_anexos = $pdo->prepare($sql_anexos);
    $stmt_anexos->execute([$solicitacao_id]);
    $anexos = $stmt_anexos->fetchAll();

} catch (PDOException $e) {
    die("Erro ao carregar dados da solicitação: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Solicitação #<?php echo $solicitacao['id']; ?> - Portal do Gestor</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header class="main-header">
        <div class="brand"><span class="logo">RW</span><span>Portal do Gestor</span></div>
    </header>

    <div class="container">
        <div class="page-header">
            <h2 class="form-title">Acompanhamento da Solicitação #<?php echo $solicitacao['id']; ?></h2>
            <a href="dashboard_gestor.php" class="btn btn-secondary" style="text-decoration: none;">Voltar para Minhas Solicitações</a>
        </div>

        <div class="details-grid">
            <div class="details-card">
                <h3><?php echo htmlspecialchars($solicitacao['titulo']); ?></h3>
                <div class="details-info">
                    <p><strong>Solicitante:</strong> <?php echo htmlspecialchars($solicitacao['nome_solicitante']); ?></p>
                    <p><strong>Data de Abertura:</strong> <?php echo date('d/m/Y H:i', strtotime($solicitacao['data_criacao'])); ?></p>
                    <p><strong>Status:</strong> <span class="status status-<?php echo strtolower($solicitacao['status']); ?>"><?php echo htmlspecialchars($solicitacao['status']); ?></span></p>
                    <p><strong>Prazo de Avaliação:</strong> 
                        <strong><?php echo !empty($solicitacao['prazo_avaliacao']) ? date('d/m/Y', strtotime($solicitacao['prazo_avaliacao'])) : 'Aguardando definição do Jurídico'; ?></strong>
                    </p>
                    <hr style="border: 0; border-top: 1px solid var(--line); margin: 20px 0;">
                    <p><strong>Descrição:</strong><br><?php echo nl2br(htmlspecialchars($solicitacao['descricao'])); ?></p>

                    <div class="anexos-section">
                        <p><strong>Anexos Originais:</strong></p>
                        <?php if (!empty($anexos)): ?>
                            <?php foreach ($anexos as $anexo): ?>
                                <div class="anexo-item">
                                    <a href="<?php echo htmlspecialchars($anexo['caminho_arquivo']); ?>" download>
                                        📄 <?php echo htmlspecialchars($anexo['nome_arquivo']); ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: var(--muted);">Nenhum anexo foi enviado com esta solicitação.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="followup-card">
                <h3>Follow-ups</h3>
                <div class="followup-list">
                    <?php if (empty($followups)): ?>
                        <p>Nenhum follow-up adicionado ainda.</p>
                    <?php else: ?>
                        <?php foreach ($followups as $followup): ?>
                            <div class="followup-item">
                                <div class="followup-avatar">
                                    <?php echo strtoupper(substr($followup['usuario'], 0, 2)); ?>
                                </div>
                                <div class="followup-content">
                                    <div class="followup-header">
                                        <span class="author"><?php echo htmlspecialchars($followup['usuario']); ?></span>
                                        <span class="timestamp"><?php echo date('d/m/Y H:i', strtotime($followup['data'])); ?></span>
                                    </div>
                                    <div class="followup-body">
                                        <?php echo nl2br(htmlspecialchars($followup['mensagem'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="add-followup-form">
                    <form action="solicitacao_view.php?id=<?php echo $solicitacao_id; ?>" method="POST">
                        <div class="form-group">
                            <label for="mensagem">Adicionar mensagem:</label>
                            <textarea name="mensagem" id="mensagem" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="add_followup" class="btn">Inserir follow-up</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="page-actions">
            </div>
    </div>

</body>
</html>