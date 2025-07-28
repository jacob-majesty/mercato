// Arquivo de JavaScript personalizado para o projeto Mercato

document.addEventListener('DOMContentLoaded', function() {
    console.log('Mercato: DOM completamente carregado e analisado.');

    // Exemplo de interatividade: Alerta ao clicar em um botão de "Adicionar ao Carrinho"
    // Supondo que você tenha botões com a classe 'add-to-cart-btn'
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Previne o comportamento padrão do link/botão
            const productId = this.dataset.productId; // Assume que você tem um data-product-id no botão
            alert('Produto ' + productId + ' adicionado ao carrinho! (Simulação)');
            // Aqui você faria uma requisição AJAX para o seu backend
        });
    });

    // Exemplo de efeito de rolagem suave para âncoras
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Adicione mais scripts personalizados aqui conforme a necessidade do seu frontend
});

// Exemplo de função global (evite muitas funções globais)
function showWelcomeMessage(userName) {
    console.log('Bem-vindo, ' + userName + ' ao Mercato!');
}
