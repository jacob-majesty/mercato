<?php
// app/View/client/checkout.php

// Define o título da página
$title = 'Finalizar Compra - Mercato';

// Inicia o buffer de saída para capturar o HTML desta view
ob_start();
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white text-center">
                <h4 class="mb-0"><i class="fas fa-credit-card"></i> Finalizar Compra</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($cart) && !empty($cart->getItems())): ?>
                    <h5 class="mb-3">Revisão do Carrinho:</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Preço Unit.</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart->getItems() as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item->getProductName()) ?></td>
                                        <td class="text-center"><?= htmlspecialchars($item->getQuantity()) ?></td>
                                        <td class="text-end">R$ <?= number_format($item->getUnitPrice(), 2, ',', '.') ?></td>
                                        <td class="text-end">R$ <?= number_format($item->getTotal(), 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Subtotal:</th>
                                    <th class="text-end">R$ <?= number_format($cart->getTotalAmount(), 2, ',', '.') ?></th>
                                </tr>
                                <!-- Linha para desconto do cupom (será atualizada via JS ou após submissão) -->
                                <tr id="coupon-discount-row" style="display: none;">
                                    <th colspan="3" class="text-end text-success">Desconto do Cupom:</th>
                                    <th class="text-end text-success" id="coupon-discount-amount"></th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end fs-4">Total a Pagar:</th>
                                    <th class="text-end fs-4 text-primary" id="final-total-amount">R$ <?= number_format($cart->getTotalAmount(), 2, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <form id="checkoutForm" action="/checkout" method="POST">
                        <h5 class="mb-3">Informações de Entrega:</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="street" class="form-label">Rua</label>
                                <input type="text" class="form-control" id="street" name="street" required value="<?= htmlspecialchars($_POST['street'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="number" class="form-label">Número</label>
                                <input type="text" class="form-control" id="number" name="number" required value="<?= htmlspecialchars($_POST['number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="complement" class="form-label">Complemento (Opcional)</label>
                                <input type="text" class="form-control" id="complement" name="complement" value="<?= htmlspecialchars($_POST['complement'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="neighborhood" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="neighborhood" name="neighborhood" required value="<?= htmlspecialchars($_POST['neighborhood'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="city" name="city" required value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">Estado</label>
                                <input type="text" class="form-control" id="state" name="state" required value="<?= htmlspecialchars($_POST['state'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="zipCode" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="zipCode" name="zip_code" required value="<?= htmlspecialchars($_POST['zip_code'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label">País</label>
                                <input type="text" class="form-control" id="country" name="country" value="Brasil" required readonly>
                            </div>
                        </div>

                        <h5 class="mb-3">Método de Pagamento:</h5>
                        <div class="mb-3">
                            <select class="form-select" id="paymentMethod" name="payment_method" required>
                                <option value="">Selecione um método de pagamento</option>
                                <option value="credit_card" <?= (($_POST['payment_method'] ?? '') === 'credit_card') ? 'selected' : '' ?>>Cartão de Crédito</option>
                                <option value="boleto" <?= (($_POST['payment_method'] ?? '') === 'boleto') ? 'selected' : '' ?>>Boleto Bancário</option>
                                <option value="pix" <?= (($_POST['payment_method'] ?? '') === 'pix') ? 'selected' : '' ?>>PIX</option>
                            </select>
                        </div>

                        <h5 class="mb-3">Cupom de Desconto (Opcional):</h5>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="couponCode" name="coupon_code" placeholder="Insira seu cupom" value="<?= htmlspecialchars($_POST['coupon_code'] ?? '') ?>">
                            <button class="btn btn-outline-secondary" type="button" id="applyCouponBtn">Aplicar Cupom</button>
                        </div>
                        <div id="couponMessage" class="mb-3"></div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-check-circle"></i> Confirmar Pedido</button>
                        </div>
                    </form>

                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        Seu carrinho está vazio. Não é possível finalizar a compra. <a href="/" class="alert-link">Voltar para a loja.</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const couponCodeInput = document.getElementById('couponCode');
    const applyCouponBtn = document.getElementById('applyCouponBtn');
    const couponMessageDiv = document.getElementById('couponMessage');
    const subtotalAmount = <?= $cart->getTotalAmount() ?? 0 ?>; // Passa o subtotal do PHP
    const finalTotalAmountSpan = document.getElementById('final-total-amount');
    const couponDiscountRow = document.getElementById('coupon-discount-row');
    const couponDiscountAmountSpan = document.getElementById('coupon-discount-amount');
    const checkoutForm = document.getElementById('checkoutForm'); // Obtém o formulário

    // Função para aplicar o cupom (simulação no frontend, a validação real é no backend)
    applyCouponBtn.addEventListener('click', function() {
        const couponCode = couponCodeInput.value.trim();
        if (couponCode === '') {
            couponMessageDiv.innerHTML = '<div class="alert alert-warning">Por favor, insira um código de cupom.</div>';
            return;
        }

        // Simulação de chamada AJAX para validar e aplicar cupom
        // Em um projeto real, você faria um fetch() para uma rota como /api/apply-coupon
        // que retornaria o desconto e o novo total.
        console.log('Aplicando cupom: ' + couponCode);
        couponMessageDiv.innerHTML = '<div class="alert alert-info">Verificando cupom...</div>';

        // Exemplo de lógica de cupom simulada (isso deve vir do backend!)
        setTimeout(() => {
            let discount = 0;
            let message = '';
            let type = 'danger'; // Default to danger

            if (couponCode === 'DESCONTO10') {
                discount = subtotalAmount * 0.10; // 10% de desconto
                message = 'Cupom "DESCONTO10" aplicado com sucesso! Você ganhou 10% de desconto.';
                type = 'success';
            } else if (couponCode === 'FIXO20') {
                discount = 20.00; // R$ 20,00 de desconto fixo
                message = 'Cupom "FIXO20" aplicado com sucesso! Você ganhou R$ 20,00 de desconto.';
                type = 'success';
            } else {
                message = 'Cupom inválido ou expirado.';
                discount = 0; // Garante que o desconto seja 0 se o cupom for inválido
            }

            const newTotal = Math.max(0, subtotalAmount - discount); // Garante que o total não seja negativo

            couponMessageDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            finalTotalAmountSpan.textContent = 'R$ ' + newTotal.toFixed(2).replace('.', ',');

            if (discount > 0) {
                couponDiscountAmountSpan.textContent = '- R$ ' + discount.toFixed(2).replace('.', ',');
                couponDiscountRow.style.display = 'table-row';
            } else {
                couponDiscountRow.style.display = 'none';
            }

        }, 1000); // Simula um atraso de rede
    });

    // Intercepta a submissão do formulário para enviar via Fetch API (JSON)
    checkoutForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Impede a submissão padrão do formulário

        const formData = new FormData(checkoutForm);
        const jsonData = {};
        formData.forEach((value, key) => {
            jsonData[key] = value;
        });

        // Exibe uma mensagem de carregamento ou desabilita o botão
        const submitButton = checkoutForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...';

        fetch(checkoutForm.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json' // Indica que esperamos uma resposta JSON
            },
            body: JSON.stringify(jsonData)
        })
        .then(response => {
            // Verifica se a resposta é JSON antes de tentar fazer parse
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return response.json().then(data => ({ status: response.status, body: data }));
            } else {
                // Se não for JSON, lê como texto e lança um erro
                return response.text().then(text => {
                    throw new Error(`Resposta inesperada do servidor (não JSON): ${text}`);
                });
            }
        })
        .then(({ status, body }) => {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-check-circle"></i> Confirmar Pedido';

            if (status >= 200 && status < 300) { // Sucesso (2xx)
                alert(body.message || 'Pedido realizado com sucesso!');
                window.location.href = '/orders'; // Redireciona para o histórico de pedidos
            } else { // Erro (4xx ou 5xx)
                alert(body.message || 'Ocorreu um erro ao processar seu pedido.');
                // Opcional: exibir o erro em uma div específica no formulário
            }
        })
        .catch(error => {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-check-circle"></i> Confirmar Pedido';
            console.error('Erro na requisição de checkout:', error);
            alert('Ocorreu um erro ao comunicar com o servidor. Por favor, tente novamente.');
        });
    });
});
</script>

<?php
// Obtém o conteúdo do buffer e o passa para a variável $content
$content = ob_get_clean();

// Inclui o layout principal
require __DIR__ . '/../layout/main.php';
?>
