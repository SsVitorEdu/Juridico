<?php
// Arquivo: C:\xampp\htdocs\juridico\dashboard_gestor.php (VERSÃO COM DATATABLES)

session_start();
require_once 'includes/conexao.php';

// --- LÓGICA DE TESTE ---
$_SESSION['gestor_id'] = 1;
$_SESSION['nome_gestor'] = 'Vitor Eduardo Lima da Rocha';
$_SESSION['nivel_acesso'] = 'gestor';
// --- Fim da Lógica de Teste ---

if (!isset($_SESSION['gestor_id'])) {
    // header('Location: ../login.php');
    // exit();
}

try {
    $id_do_gestor_logado = $_SESSION['gestor_id'];
    
    // Busca a lista de solicitações do gestor
    $sql_lista = "SELECT id, titulo, status, data_criacao, prazo_avaliacao FROM juridico_solicitacoes WHERE id_solicitante = ? ORDER BY data_criacao DESC";
    $stmt_lista = $pdo->prepare($sql_lista);
    $stmt_lista->execute([$id_do_gestor_logado]);
    $solicitacoes = $stmt_lista->fetchAll();

    // Busca as contagens para os cards
    $status_para_contar = ['Nova', 'Pendente', 'Aprovada', 'Recusada'];
    $contagem_status = [];
    foreach ($status_para_contar as $status) {
        $sql_contagem = "SELECT COUNT(*) as total FROM juridico_solicitacoes WHERE id_solicitante = ? AND status = ?";
        $stmt_contagem = $pdo->prepare($sql_contagem);
        $stmt_contagem->execute([$id_do_gestor_logado, $status]);
        $resultado = $stmt_contagem->fetch();
        $contagem_status[$status] = $resultado['total'];
    }
} catch (PDOException $e) {
    $solicitacoes = [];
    $contagem_status = array_fill_keys($status_para_contar, 0);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minhas Solicitações - Portal do Gestor</title>
    <link rel="stylesheet" href="css/style.css">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">

    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background-color: var(--card); padding: 20px; border-radius: 8px; border: 1px solid var(--line); }
        .stat-card h3 { margin-top: 0; font-size: 1rem; color: var(--muted); }
        .stat-card .count { font-size: 2.5rem; font-weight: 700; color: var(--text); }
        .solicitacoes-table { width: 100%; border-collapse: collapse; background-color: var(--card); }
        .solicitacoes-table th, .solicitacoes-table td { padding: 12px 15px; border-bottom: 1px solid var(--line); text-align: left; }
        .solicitacoes-table th { background-color: #f9fafb; font-weight: 600; cursor: pointer; } /* Cursor para indicar que é clicável */
        .solicitacoes-table tr:hover { background-color: #f3f4f6; }
        .status { padding: 4px 8px; border-radius: 12px; font-weight: 500; font-size: 0.8rem; text-transform: capitalize; }
        .status-nova { background-color: #DBEAFE; color: #1E40AF; } .status-pendente { background-color: #FEF3C7; color: #92400E; } .status-aprovada { background-color: #D1FAE5; color: #065F46; } .status-recusada { background-color: #FEE2E2; color: #991B1B; }
        .dataTables_filter input { border: 1px solid var(--line); border-radius: 4px; padding: 5px; }
    </style>
</head>
<body>
    <header class="main-header"><div class="brand"><span class="logo">RW</span><span>Portal do Gestor</span></div></header>
    <div class="container">
        <div class="page-header"><h2 class="form-title">Minhas Solicitações Jurídicas</h2><a href="nova_solicitacao.php" class="btn">Criar Nova Solicitação</a></div>
        <div class="stats-container">
            <div class="stat-card"><h3>Pendentes / Novas</h3><div class="count"><?php echo $contagem_status['Pendente'] + $contagem_status['Nova']; ?></div></div>
            <div class="stat-card"><h3>Aprovadas</h3><div class="count"><?php echo $contagem_status['Aprovada']; ?></div></div>
            <div class="stat-card"><h3>Recusadas</h3><div class="count"><?php echo $contagem_status['Recusada']; ?></div></div>
        </div>
        <div class="form-card">
            <table id="tabela-gestor" class="solicitacoes-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Status</th>
                        <th>Data de Abertura</th>
                        <th>Prazo de Avaliação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($solicitacoes)): ?>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <tr>
                                <td>#<?php echo $solicitacao['id']; ?></td>
                                <td><?php echo htmlspecialchars($solicitacao['titulo']); ?></td>
                                <td>
                                    <span style="display: none;"><?php echo htmlspecialchars($solicitacao['status']); ?></span>
                                    <span class="status status-<?php echo strtolower($solicitacao['status']); ?>"><?php echo htmlspecialchars($solicitacao['status']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($solicitacao['data_criacao'])); ?></td>
                                <td><?php echo !empty($solicitacao['prazo_avaliacao']) ? date('d/m/Y', strtotime($solicitacao['prazo_avaliacao'])) : 'Aguardando Jurídico'; ?></td>
                                <td><a href="solicitacao_view_gestor.php?id=<?php echo $solicitacao['id']; ?>">Acompanhar</a></td>
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
            $('#tabela-gestor').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
                }
            });
        });
    </script>
</body>
</html>