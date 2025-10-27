<?php
// Define o tipo de conteúdo como JSON
header('Content-Type: application/json');

// Inclui sua conexão com o banco de dados
// (Vi 'conexao.php' na sua estrutura de arquivos)
include 'includes/conexao.php'; 

// Prepara a consulta SQL para buscar as solicitações
// Usamos o 'prazo_avaliacao' como a data do evento no calendário
$sql = "SELECT id, titulo, prazo_avaliacao, status FROM juridico_solicitacoes";

$result = $conn->query($sql);

$eventos = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        // Formata os dados para o FullCalendar
        $eventos[] = [
            'id'    => $row['id'],           // ID da solicitação
            'title' => $row['titulo'],       // O título do evento
            'start' => $row['prazo_avaliacao'], // A data do evento
            'status' => $row['status']      // Propriedade extra para usar depois
        ];
    }
}

// Fecha a conexão
$conn->close();

// Retorna os eventos em formato JSON
echo json_encode($eventos);
?>