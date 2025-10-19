<?php
/**
 * GPM Dashboard - Painel Principal
 * Exibe gráficos, formulário de registro e listagem de equipes
 */
session_start();
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

// --- INÍCIO DA CORREÇÃO (SINTAXE PDO) ---

$conn = getConnection(); // $conn agora é um objeto PDO

// Busca dados de produtividade das equipes
$sql = "SELECT * FROM vw_produtividade ORDER BY nome";
$stmt = $conn->query($sql); // 1. query() em PDO retorna um PDOStatement
$equipes = $stmt->fetchAll(PDO::FETCH_ASSOC); // 2. fetchAll() busca todos os dados de uma vez

// Busca lista de equipes para o formulário
$sqlEquipes = "SELECT id, nome FROM equipes ORDER BY nome";
$resultEquipes = $conn->query($sqlEquipes); // 3. $resultEquipes agora é um PDOStatement

$conn = null; // 4. Em PDO, fechamos a conexão definindo-a como null

// --- FIM DA CORREÇÃO ---
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPM Dashboard - Gestão de Produtividade</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo-section">
                    <svg width="40" height="40" viewBox="0 0 50 50" fill="none">
                        <rect width="50" height="50" rx="10" fill="#4A90E2"/>
                        <path d="M15 35V25L25 15L35 25V35H28V28H22V35H15Z" fill="white"/>
                    </svg>
                    <div>
                        <h1>GPM Dashboard</h1>
                        <p>Gestão de Equipes de Campo</p>
                    </div>
                </div>
                <div class="user-section">
                    <span>👤 <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                    <a href="logout.php" class="btn-logout">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div id="message" class="message" style="display: none;"></div>

        <div class="dashboard-grid">
            <section class="card">
                <h2>📝 Registrar Serviços</h2>
                <form id="formServico" class="form-servico">
                    <div class="form-group">
                        <label for="equipe_id">Equipe:</label>
                        <select id="equipe_id" name="equipe_id" required>
                            <option value="">Selecione uma equipe</option>
                            
                            <?php while($equipe = $resultEquipes->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $equipe['id']; ?>">
                                    <?php echo htmlspecialchars($equipe['nome']); ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantidade">Quantidade de Serviços:</label>
                        <input 
                            type="number" 
                            id="quantidade" 
                            name="quantidade" 
                            min="1" 
                            required
                            placeholder="Ex: 15"
                        >
                    </div>

                    <div class="form-group">
                        <label for="data_registro">Data:</label>
                        <input 
                            type="date" 
                            id="data_registro" 
                            name="data_registro" 
                            value="<?php echo date('Y-m-d'); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="observacao">Observações (opcional):</label>
                        <textarea 
                            id="observacao" 
                            name="observacao" 
                            rows="3"
                            placeholder="Detalhes sobre os serviços realizados..."
                        ></textarea>
                    </div>

                    <button type="submit" class="btn-primary">Registrar Serviços</button>
                </form>
            </section>

            <section class="card">
                <h2>📊 Produtividade por Equipe</h2>
                <div class="chart-container">
                    <canvas id="chartProdutividade"></canvas>
                </div>
            </section>

            <section class="card card-full">
                <h2>👥 Resumo das Equipes</h2>
                <div class="table-responsive">
                    <table class="table-equipes">
                        <thead>
                            <tr>
                                <th>Equipe</th>
                                <th>Total de Serviços</th>
                                <th>Registros</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaEquipes">
                            <?php foreach($equipes as $equipe): ?>
                            <tr>
                                <td>
                                    <span class="equipe-badge" style="background-color: <?php echo $equipe['cor']; ?>"></span>
                                    <?php echo htmlspecialchars($equipe['nome']); ?>
                                </td>
                                <td><strong><?php echo number_format($equipe['total_servicos'], 0, ',', '.'); ?></strong></td>
                                <td><?php echo $equipe['registros']; ?> lançamento(s)</td>
                                <td>
                                    <?php 
                                    $status = $equipe['total_servicos'] > 50 ? 'success' : 
                                              ($equipe['total_servicos'] > 20 ? 'warning' : 'danger');
                                    $statusText = $equipe['total_servicos'] > 50 ? 'Excelente' : 
                                                  ($equipe['total_servicos'] > 20 ? 'Regular' : 'Baixa');
                                    ?>
                                    <span class="status-badge status-<?php echo $status; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <script>
        // NENHUMA MUDANÇA AQUI - $equipes já está sendo populado corretamente
        const dadosEquipes = <?php echo json_encode($equipes); ?>;

        // Configuração do gráfico de barras
        const ctx = document.getElementById('chartProdutividade').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dadosEquipes.map(e => e.nome),
                datasets: [{
                    label: 'Serviços Realizados',
                    data: dadosEquipes.map(e => e.total_servicos),
                    backgroundColor: dadosEquipes.map(e => e.cor),
                    borderColor: dadosEquipes.map(e => e.cor),
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 10
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Manipulação do formulário via AJAX
        document.getElementById('formServico').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const messageDiv = document.getElementById('message');

            try {
                // Envia dados para o servidor
                const response = await fetch('add_service.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                // Exibe mensagem de sucesso ou erro
                messageDiv.className = 'message ' + (result.success ? 'message-success' : 'message-error');
                messageDiv.textContent = result.message;
                messageDiv.style.display = 'block';

                // Se sucesso, recarrega a página para atualizar dados
                if (result.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }

                // Esconde mensagem após 5 segundos
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 5000);

            } catch (error) {
                messageDiv.className = 'message message-error';
                messageDiv.textContent = 'Erro ao processar requisição: ' + error.message;
                messageDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>