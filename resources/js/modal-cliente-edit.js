
document.addEventListener("DOMContentLoaded", function() {
    // Seleciona todos os botões de edição usando a classe "edit-btn"
    const editButtons = document.querySelectorAll(".edit-btn");

    // Para cada botão de edição encontrado, adiciona um evento de clique
    editButtons.forEach(button => {
        button.addEventListener("click", function(event) {
            // Previne o comportamento padrão do clique
            event.preventDefault();
            
            // Obtém o ID do cliente do atributo "data-id" do botão clicado
            const clientId = button.getAttribute("data-id");
            
            // Chama a função para carregar os dados do cliente no modal
            loadClientData(clientId);
        });
    });

    // Função responsável por carregar os dados do cliente e preencher o modal
    function loadClientData(clientId) {
        // Aqui você pode usar a API Fetch ou AJAX para fazer uma requisição ao servidor
        // E obter os dados do cliente com base no "clientId"
        fetch(`/clientes/${clientId}/edit`)
            .then(response => response.json())
            .then(data => {
                // Preencher os campos do modal com os dados recebidos do servidor
                document.getElementById("Cliente").value = data.Cliente;
                document.getElementById("Nome").value = data.Nome;
                document.getElementById("Fac_Mor").value = data.Fac_Mor;
                document.getElementById("NumContrib").value = data.NumContrib;
                document.getElementById("Pais").value = data.Pais;
                document.getElementById("NomeFiscal").value = data.NomeFiscal;
                document.getElementById("tipoDoc").value = data.tipoDoc;

                // Exibe o modal
                $('#editClientModal').modal('show');
            })
            .catch(error => console.error('Erro ao carregar os dados do cliente:', error));
    }
});

