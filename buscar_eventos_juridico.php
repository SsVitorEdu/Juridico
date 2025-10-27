<?php
// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');

// Inclui sua conexão PDO (a mesma usada no dashboard_juridico.php)
require_once 'includes/conexao.php';

$eventos = [];

try {
    // Busca todas as solicitações que tenham um prazo de avaliação definido
    $sql = "
        SELECT id, titulo, prazo_avaliacao, status 
        FROM juridico_solicitacoes
        WHERE prazo_avaliacao IS NOT NULL
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $solicitacoes = $stmt->fetchAll();

    $hoje = date('Y-m-d');

    foreach ($solicitacoes as $sol) {
        
        // --- Lógica de Cores (baseada no seu dashboard_juridico.php) ---
        $cor = '#9CA3AF'; // Padrão (Recusada)
        
        if ($sol['status'] === 'Pendente') {
            // Verifica se está atrasado
            if (strtotime($sol['prazo_avaliacao']) < strtotime($hoje)) {
                $cor = '#F87171'; // Vermelho (Atrasado)
            } else {
                $cor = '#FBBF24'; // Amarelo (Pendente)
            }
        } elseif ($sol['status'] === 'Aprovada') {
            $cor = '#34D399'; // Verde (Aprovada)
        } elseif ($sol['status'] === 'Nova') {
            $cor = '#60A5FA'; // Azul (Nova)
        }
        // -----------------------------------------------------------------

        $eventos[] = [
            'id'    => $sol['id'],
            'title' => $sol['titulo'],
            'start' => $sol['prazo_avaliacao'], // Data do evento
            'allDay' => true, // O prazo vale para o dia todo
            'color' => $cor,  // Cor de fundo do evento
            'borderColor' => $cor, // Cor da borda
            // Propriedades extras para usar no modal
            'extendedProps' => [
                'status' => $sol['status']
            ]
        ];
    }

} catch (PDOException $e) {
    // Em caso de erro, envia um array vazio
    // (Você pode querer logar o erro $e->getMessage())
}

// Retorna os eventos em formato JSON
echo json_encode($eventos);
?>