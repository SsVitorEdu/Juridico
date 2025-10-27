<?php
// Arquivo: C:\xampp\htdocs\juridico\solicitacao_view_juridico.php (VERSÃO FINAL COMPLETA)

session_start();
require_once 'includes/conexao.php';

// --- LÓGICA DE TESTE ---
$_SESSION['nome_gestor'] = 'Analista Jurídico';
// --- Fim da Lógica de Teste ---

$solicitacao_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($solicitacao_id === 0) { die("ID da solicitação inválido."); }

// LÓGICA DE PROCESSAMENTO DE FORMULÁRIOS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['definir_prazo'])) {
        $prazo = $_POST['prazo_avaliacao'];
        if (!empty($prazo)) {
            $sql = "UPDATE juridico_solicitacoes SET prazo_avaliacao = ?, status = 'Pendente' WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$prazo, $solicitacao_id]);
            header("Location: solicitacao_view_juridico.php?id=" . $solicitacao_id);
            exit();
        }
    }
    if (isset($_POST['finalizar_solicitacao'])) {
        $status_final = $_POST['status_final'];
        if ($status_final === 'Aprovada' || $status_final === 'Recusada') {
            $sql = "UPDATE juridico_solicitacoes SET status = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status_final, $solicitacao_id]);
            header("Location: solicitacao_view_juridico.php?id=" . $solicitacao_id);
            exit();
        }
    }
    if (isset($_POST['add_followup'])) {
        $mensagem = $_POST['mensagem'];
        $usuario = $_SESSION['nome_gestor'];
        if (!empty($mensagem)) {
            $sql = "INSERT INTO juridico_followups (solicitacao_id, usuario, mensagem) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$solicitacao_id, $usuario, $mensagem]);
            header("Location: solicitacao_view_juridico.php?id=" . $solicitacao_id);
            exit();
        }
    }
}

// Busca os dados da solicitação, anexos e follow-ups
try {
    $sql_solicitacao = "SELECT * FROM juridico_solicitacoes WHERE id = ?";
    $stmt_solicitacao = $pdo->prepare($sql_solicitacao);
    $stmt_solicitacao->execute([$solicitacao_id]);
    $solicitacao = $stmt_solicitacao->fetch();

    if (!$solicitacao) { die("Solicitação com ID #" . $solicitacao_id . " não encontrada."); }

    $sql_followups = "SELECT * FROM juridico_followups WHERE solicitacao_id = ? ORDER BY data ASC";
    $stmt_followups = $pdo->prepare($sql_followups);
    $stmt_followups->execute([$solicitacao_id]);
    $followups = $stmt_followups->fetchAll();
    
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
    <title>Analisar Solicitação #<?php echo $solicitacao['id']; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="brand"><span class="logo">RW</span><span>Portal do Gestor</span></div>
    </header>

    <div class="container">
        <div class="page-header">
            <h2 class="form-title">Analisar Solicitação #<?php echo $solicitacao['id']; ?></h2>
            <a href="dashboard_juridico.php" class="btn btn-secondary" style="text-decoration: none;">Voltar para o Painel</a>
        </div>

        <div class="details-grid">
            
            <div class="juridico-actions-card">
                <?php if ($solicitacao['status'] === 'Nova'): ?>
                    <h3>Ações de Triagem</h3>
                    <p style="color: var(--muted); margin-top: -10px; font-size: 0.9rem;">O primeiro passo é definir um prazo para iniciar a análise.</p>
                    <form action="solicitacao_view_juridico.php?id=<?php echo $solicitacao_id; ?>" method="POST">
                        <div class="form-group"><label for="prazo_avaliacao">Definir Prazo de Avaliação</label><input type="date" id="prazo_avaliacao" name="prazo_avaliacao" required></div>
                        <button type="submit" name="definir_prazo" class="btn">Definir Prazo e Iniciar Análise</button>
                    </form>
                <?php else: ?>
                    <h3>Status da Análise</h3>
                    <p style="color: var(--muted); margin-top: -10px; font-size: 0.9rem;">A solicitação já foi triada. Acompanhe os detalhes abaixo e adicione follow-ups conforme necessário.</p>
                    <div class="details-info" style="margin-top: 20px;">
                        <p><strong>Prazo Definido:</strong> <strong><?php echo date('d/m/Y', strtotime($solicitacao['prazo_avaliacao'])); ?></strong><small style="color: var(--muted); display: block;">O prazo não pode ser alterado após definido.</small></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="details-card">
                <h3><?php echo htmlspecialchars($solicitacao['titulo']); ?></h3>
                <div class="details-info">
                    <p><strong>Solicitante:</strong> <?php echo htmlspecialchars($solicitacao['nome_solicitante']); ?></p>
                    <p><strong>Status:</strong> <span class="status status-<?php echo strtolower($solicitacao['status']); ?>"><?php echo htmlspecialchars($solicitacao['status']); ?></span></p>
                    <hr style="border: 0; border-top: 1px solid var(--line); margin: 20px 0;">
                    <p><strong>Descrição:</strong><br><?php echo nl2br(htmlspecialchars($solicitacao['descricao'])); ?></p>
                    <div class="anexos-section">
                        <p><strong>Anexos Originais:</strong></p>
                        <?php if (!empty($anexos)): ?>
                            <?php foreach ($anexos as $anexo): ?>
                                <div class="anexo-item"><a href="<?php echo htmlspecialchars($anexo['caminho_arquivo']); ?>" download>📄 <?php echo htmlspecialchars($anexo['nome_arquivo']); ?></a></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: var(--muted);">Nenhum anexo foi enviado.</p>
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
                                <div class="followup-avatar"><?php echo strtoupper(substr($followup['usuario'], 0, 2)); ?></div>
                                <div class="followup-content">
                                    <div class="followup-header"><span class="author"><?php echo htmlspecialchars($followup['usuario']); ?></span><span class="timestamp"><?php echo date('d/m/Y H:i', strtotime($followup['data'])); ?></span></div>
                                    <div class="followup-body"><?php echo nl2br(htmlspecialchars($followup['mensagem'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="add-followup-form">
                    <form action="solicitacao_view_juridico.php?id=<?php echo $solicitacao_id; ?>" method="POST">
                        <div class="form-group"><label for="mensagem">Adicionar mensagem:</label><textarea name="mensagem" id="mensagem" rows="4" required></textarea></div>
                        <button type="submit" name="add_followup" class="btn">Inserir follow-up</button>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($solicitacao['status'] === 'Pendente'): ?>
            <div class="page-actions">
                <form action="solicitacao_view_juridico.php?id=<?php echo $solicitacao_id; ?>" method="POST" class="form-actions">
                    <input type="hidden" name="status_final" value="Aprovada">
                    <button type="submit" name="finalizar_solicitacao" class="btn btn-success">Aprovar Solicitação</button>
                </form>
                <form action="solicitacao_view_juridico.php?id=<?php echo $solicitacao_id; ?>" method="POST" class="form-actions">
                    <input type="hidden" name="status_final" value="Recusada">
                    <button type="submit" name="finalizar_solicitacao" class="btn btn-danger">Recusar Solicitação</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>