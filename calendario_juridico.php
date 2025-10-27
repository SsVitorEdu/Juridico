<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Calendário - Jurídico</title>
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js'></script>
    
    <link rel="stylesheet" href="css/style.css"> 
    
    <style>
        /* Estilos do Modal (copiados do seu calendario.php) */
        .modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;overflow:auto;background-color:rgba(0,0,0,0.4)}
        .modal-content{background-color:#fefefe;margin:10% auto;padding:20px;border:1px solid #888;width:80%;max-width:600px;border-radius:8px;position:relative}
        .close-btn{color:#aaa;float:right;font-size:28px;font-weight:bold;position:absolute;top:10px;right:20px;cursor:pointer}
        .close-btn:hover,.close-btn:focus{color:black;text-decoration:none}
        .modal-event-list{list-style-type:none;padding:0;margin:0}
        .modal-event-item{display:flex;border-left:5px solid #ccc;padding:15px 10px;margin-bottom:10px;background-color:#f9f9f9;border-radius:4px}
        .modal-event-item .event-details{flex-grow:1}
        .modal-event-item .event-title{font-weight:bold;font-size:1.1rem;margin-bottom:5px;color:#000}
        .modal-event-item .event-info{font-size:.9rem;color:#555;margin-bottom:3px}
        .modal-event-item .event-info strong{color:#111}
        .modal-event-item .event-actions{margin-top:10px;border-top:1px dashed #ddd;padding-top:10px}
        .modal-event-item .event-actions .btn-action{display:inline-block;padding:5px 10px;border:none;border-radius:4px;text-decoration:none;font-size:.85rem;font-weight:500;margin-right:5px;cursor:pointer}
        .modal-event-item .event-actions .edit{background-color:#007bff;color:white}
        
        /* Estilo do Calendário */
        #calendario-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div id="eventosDiaModal" class="modal">
        <div class="modal-content"> 
            <span class="close-btn">&times;</span> 
            <h3 id="modalTituloDia">...</h3> 
            <div id="modalListaEventos">...</div> 
        </div>
    </div>

    <div id="calendario-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Calendário de Prazos Jurídicos</h2>
            <a href="dashboard_juridico.php" style="text-decoration: none;">&larr; Voltar ao Painel</a>
        </div>
        <div id="calendario"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- Variáveis Globais (baseado no seu calendario.php) ---
        const calendarEl = document.getElementById('calendario');
        const modal = document.getElementById('eventosDiaModal');
        const modalTitulo = document.getElementById('modalTituloDia');
        const modalLista = document.getElementById('modalListaEventos');
        const closeModalBtn = modal.querySelector('.close-btn');

        // --- Lógica do Modal ---
        function closeModal() { modal.style.display = 'none'; }
        if(closeModalBtn) { closeModalBtn.onclick = closeModal; }
        window.onclick = function(event) { if (event.target == modal) { closeModal(); } }

        function formatarDataClicada(date) {
            return date.toLocaleDateString('pt-BR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        /**
         * FUNÇÃO DO MODAL - SIMPLIFICADA PARA O JURÍDICO
         * Abre o modal e o preenche com as solicitações do dia clicado.
         */
        function mostrarEventosDoDia(date) {
            modalTitulo.innerHTML = formatarDataClicada(date);
            
            const allEvents = calendar.getEvents();
            
            const eventosDoDia = allEvents.filter(function(event) {
                if (!event.start) return false;
                return event.start.toDateString() === date.toDateString();
            });

            if (eventosDoDia.length > 0) {
                let htmlLista = '<ul class="modal-event-list">';
                
                eventosDoDia.sort((a, b) => (a.title > b.title) ? 1 : -1); // Ordena por título

                eventosDoDia.forEach(function(event) {
                    const props = event.extendedProps;
                    const cor = event.backgroundColor || '#007bff';
                    
                    htmlLista += `<li class="modal-event-item" style="border-left-color: ${cor};">`;
                    htmlLista += `<div class="event-details">`;
                    htmlLista += `<div class="event-title">${event.title}</div>`;
                    
                    // Informações específicas do Jurídico
                    if (props.status) { 
                        htmlLista += `<div class="event-info"><strong>Status:</strong> ${props.status}</div>`; 
                    }

                    // Botão de Ação (link para a página de análise)
                    htmlLista += `<div class="event-actions">
                                    <a href="solicitacao_view_juridico.php?id=${event.id}" class="btn-action edit">Analisar Solicitação</a>
                                 </div>`;
                    
                    htmlLista += `</div></li>`; // Fecha event-details e li
                });
                htmlLista += '</ul>';
                modalLista.innerHTML = htmlLista;
            } else {
                modalLista.innerHTML = '<p style="padding: 10px 0;">Nenhum prazo de solicitação para este dia.</p>';
            }

            modal.style.display = 'block';
        }

        // --- Configuração do FullCalendar ---
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'pt-br',
            initialView: 'dayGridMonth',

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            
            buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', day: 'Dia' },
            height: 'auto',
            navLinks: true,
            
            // Eventos de clique
            dateClick: function(info) { mostrarEventosDoDia(info.date); },
            eventClick: function(info) { 
                mostrarEventosDoDia(info.event.start); 
                info.jsEvent.preventDefault(); 
            },
            
            // Fonte de eventos (BUSCANDO DO NOSSO NOVO ARQUIVO)
            eventSources: [ 
                { 
                    url: 'buscar_eventos_juridico.php'
                } 
            ]
        });

        // Renderiza o calendário
        calendar.render();
    });
    </script>

</body>
</html>