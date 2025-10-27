<?php
// Arquivo: C:\xampp\htdocs\juridico\dashboard_juridico.php (VERSÃO COMPLETA E FINAL)

session_start();
require_once 'includes/conexao.php';

// --- LÓGICA DE TESTE ---
$_SESSION['gestor_id'] = 2;
$_SESSION['nome_gestor'] = 'Analista Jurídico';
$_SESSION['nivel_acesso'] = 'juridico';
// --- Fim da Lógica de Teste ---

if ($_SESSION['nivel_acesso'] !== 'juridico') {
    // die("Acesso negado.");
}

try {
    // 1. Busca a lista completa de solicitações para a tabela
    $sql_lista = "
        SELECT id, titulo, nome_solicitante, status, data_criacao, prazo_avaliacao 
        FROM juridico_solicitacoes 
        ORDER BY 
            FIELD(status, 'Nova', 'Pendente', 'Aprovada', 'Recusada'), 
            data_criacao DESC
    ";
    $stmt_lista = $pdo->prepare($sql_lista);
    $stmt_lista->execute();
    $solicitacoes = $stmt_lista->fetchAll();

    // 2. Busca os totais para os cards de status
    $contagem_status = [];
    $stmt_novas = $pdo->query("SELECT COUNT(*) FROM juridico_solicitacoes WHERE status = 'Nova'");
    $contagem_status['novas'] = $stmt_novas->fetchColumn();
    $stmt_pendentes = $pdo->query("SELECT COUNT(*) FROM juridico_solicitacoes WHERE status = 'Pendente'");
    $contagem_status['pendentes'] = $stmt_pendentes->fetchColumn();
    $sql_atrasados = "SELECT COUNT(*) FROM juridico_solicitacoes WHERE status = 'Pendente' AND prazo_avaliacao < CURDATE()";
    $stmt_atrasados = $pdo->query($sql_atrasados);
    $contagem_status['atrasados'] = $stmt_atrasados->fetchColumn();
    $sql_vencer = "SELECT COUNT(*) FROM juridico_solicitacoes WHERE status = 'Pendente' AND prazo_avaliacao BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
    $stmt_vencer = $pdo->query($sql_vencer);
    $contagem_status['vencer'] = $stmt_vencer->fetchColumn();
    $stmt_aprovadas = $pdo->query("SELECT COUNT(*) FROM juridico_solicitacoes WHERE status = 'Aprovada'");
    $contagem_status['aprovadas'] = $stmt_aprovadas->fetchColumn();
    $stmt_recusadas = $pdo->query("SELECT COUNT(*) FROM juridico_solicitacoes WHERE status = 'Recusada'");
    $contagem_status['recusadas'] = $stmt_recusadas->fetchColumn();

} catch (PDOException $e) {
    $solicitacoes = [];
    $contagem_status = array_fill_keys(['novas', 'pendentes', 'atrasados', 'vencer', 'aprovadas', 'recusadas'], 0);
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Jurídico - Portal do Gestor</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .stats-container { display: grid; grid-template-columns: repeat(6, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background-color: var(--card); padding: 20px; border-radius: 8px; border: 1px solid var(--line); }
        .stat-card h3 { margin-top: 0; font-size: 1rem; color: var(--muted); }
        .stat-card .count { font-size: 2.5rem; font-weight: 700; color: var(--text); }
        .solicitacoes-table { width: 100%; border-collapse: collapse; background-color: var(--card); }
        .solicitacoes-table th, .solicitacoes-table td { padding: 12px 15px; border-bottom: 1px solid var(--line); text-align: left; }
        .solicitacoes-table th { background-color: #f9fafb; font-weight: 600; cursor: pointer; }
        .solicitacoes-table tr { transition: background-color 0.2s; }
        .solicitacoes-table tr:hover { background-color: #f3f4f6; }
        .tr-nova { border-left: 4px solid #60A5FA; } .tr-pendente { border-left: 4px solid #FBBF24; }
        .tr-atrasado { border-left: 4px solid #F87171; background-color: #FEF2F2; }
        .tr-aprovada { border-left: 4px solid #34D399; } .tr-recusada { border-left: 4px solid #9CA3AF; }
        .status { padding: 4px 8px; border-radius: 12px; font-weight: 500; font-size: 0.8rem; text-transform: capitalize; }
        .status-nova { background-color: #DBEAFE; color: #1E40AF; } .status-pendente { background-color: #FEF3C7; color: #92400E; }
        .status-aprovada { background-color: #D1FAE5; color: #065F46; } .status-recusada { background-color: #FEE2E2; color: #991B1B; }
        .dataTables_filter input { border: 1px solid var(--line); border-radius: 4px; padding: 5px; }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="brand"><span class="logo">RW</span><span>Portal do Gestor</span></div>
    </header>

    <div class="container">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="form-title" style="margin-bottom: 0;">Painel de Controle Jurídico</h2>
            
            <a href="calendario_juridico.php" style="padding: 8px 12px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: 500;">
                Ver Calendário
            </a>
            </div>

        <div class="stats-container">
            <div class="stat-card"><h3>Novas Solicitações</h3><div class="count"><?php echo $contagem_status['novas']; ?></div></div>
            <div class="stat-card"><h3>Em Andamento</h3><div class="count"><?php echo $contagem_status['pendentes']; ?></div></div>
            <div class="stat-card" style="background-color: #FEE2E2;"><h3>Atrasados</h3><div class="count"><?php echo $contagem_status['atrasados']; ?></div></div>
            <div class="stat-card" style="background-color: #FFFBEB;"><h3>Próximo de Vencer (7d)</h3><div class="count"><?php echo $contagem_status['vencer']; ?></div></div>
            <div class="stat-card"><h3>Aprovadas</h3><div class="count"><?php echo $contagem_status['aprovadas']; ?></div></div>
            <div class="stat-card"><h3>Recusadas</h3><div class="count"><?php echo $contagem_status['recusadas']; ?></div></div>
        </div>

        <div class="form-card">
            <table id="tabela-solicitacoes" class="solicitacoes-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Título</th><th>Solicitante</th><th>Status</th><th>Data de Abertura</th><th>Prazo de Avaliação</th><th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($solicitacoes)): ?>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <?php
                                $row_class = '';
                                if ($solicitacao['status'] === 'Pendente' && !empty($solicitacao['prazo_avaliacao']) && strtotime($solicitacao['prazo_avaliacao']) < strtotime(date('Y-m-d'))) {
                                    $row_class = 'tr-atrasado';
                                } else {
                                    $row_class = 'tr-' . strtolower($solicitacao['status']);
                                }
                            ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td>#<?php echo $solicitacao['id']; ?></td>
                                <td><?php echo htmlspecialchars($solicitacao['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($solicitacao['nome_solicitante']); ?></td>
                                <td>
                                    <span style="display: none;"><?php echo htmlspecialchars($solicitacao['status']); ?></span>
                                    <span class="status status-<?php echo strtolower($solicitacao['status']); ?>"><?php echo htmlspecialchars($solicitacao['status']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($solicitacao['data_criacao'])); ?></td>
                                <td><?php echo !empty($solicitacao['prazo_avaliacao']) ? date('d/m/Y', strtotime($solicitacao['prazo_avaliacao'])) : '<strong>A definir</strong>'; ?></td>
                                <td><a href="solicitacao_view_juridico.php?id=<?php echo $solicitacao['id']; ?>">Analisar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function() {
            $('#tabela-solicitacoes').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
                }
            });
        });
    </script>
</body>
</html>