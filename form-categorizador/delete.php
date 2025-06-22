<?php
require_once 'conexao.php';
session_start();

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    header("Location: ficha.php?mensagem=ID do produto não fornecido.");
    exit();
}

$id_produto = $_GET['id'];

// Verificar se o ID é numérico
if (!is_numeric($id_produto)) {
    header("Location: ficha.php?mensagem=ID do produto deve ser numérico.");
    exit();
}

// Converter para inteiro para garantir
$id_produto = (int)$id_produto;

// Verificar se o produto existe e obter informações básicas
$stmt = $pdo->prepare("SELECT p.*, c.nome AS nome_categoria 
                      FROM prod p 
                      JOIN categorias c ON p.idcategoria = c.id 
                      WHERE p.id = ?");
$stmt->execute([$id_produto]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header("Location: ficha.php?mensagem=Produto com ID $id_produto não encontrado.");
    exit();
}

// Buscar todos os campos e valores do produto
$stmtCampos = $pdo->prepare("
    SELECT 
        cp.nome AS nome_campo,
        cp.tipodedado,
        pd.valor
    FROM 
        produtosdados pd
    JOIN 
        campos cp ON pd.idcampo = cp.id
    WHERE 
        pd.idproduto = ?
    ORDER BY 
        cp.nome
");
$stmtCampos->execute([$id_produto]);
$campos = $stmtCampos->fetchAll(PDO::FETCH_ASSOC);

// Se a confirmação foi enviada
if (isset($_GET['confirmar']) && $_GET['confirmar'] == 'sim') {
    try {
        // Iniciar transação
        $pdo->beginTransaction();
        
        // Primeiro excluir os dados relacionados na tabela produtosdados
        $stmtDeleteDados = $pdo->prepare("DELETE FROM produtosdados WHERE idproduto = ?");
        $stmtDeleteDados->execute([$id_produto]);
        
        // Depois excluir o produto
        $stmtDeleteProd = $pdo->prepare("DELETE FROM prod WHERE id = ?");
        $stmtDeleteProd->execute([$id_produto]);
        
        // Confirmar transação
        $pdo->commit();
        
        header("Location: ficha.php?mensagem=Produto excluído com sucesso!");
        exit();
    } catch (PDOException $e) {
        // Reverter em caso de erro
        $pdo->rollBack();
        header("Location: ficha.php?mensagem=Erro ao excluir: " . urlencode($e->getMessage()));
        exit();
    }
}

// Buscar categoria do produto para retornar após cancelamento
$idcategoria = $produto['idcategoria'];

// Se chegou aqui, exibir a página de confirmação
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <title>Excluir Produto</title>
    <style>
        .product-details {
            background-color:rgb(220, 238, 255);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .field-name {
            font-weight: bold;
            color: #495057;
        }
        .field-value {
            color: #212529;
        }
        .warning-icon {
            font-size: 3rem;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">Confirmar Exclusão</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="warning-icon">⚠️</div>
                            <p class="alert alert-warning">
                                <strong>Atenção!</strong> Você está prestes a excluir o produto abaixo:
                            </p>
                        </div>
                        
                        <div class="product-details">
                            <div class="row mb-2">
                                <div class="col-md-3 field-name">ID:</div>
                                <div class="col-md-9 field-value"><?php echo $produto['id']; ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-3 field-name">Nome:</div>
                                <div class="col-md-9 field-value"><?php echo htmlspecialchars($produto['nome']); ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-3 field-name">Categoria:</div>
                                <div class="col-md-9 field-value"><?php echo htmlspecialchars($produto['nome_categoria']); ?></div>
                            </div>
                            
                            <?php if (count($campos) > 0): ?>
                                <hr>
                                <h5 class="mb-3">Detalhes do Produto</h5>
                                <?php foreach ($campos as $campo): ?>
                                    <div class="row mb-2">
                                        <div class="col-md-3 field-name"><?php echo htmlspecialchars($campo['nome_campo']); ?>:</div>
                                        <div class="col-md-9 field-value">
                                            <?php 
                                            if ($campo['tipodedado'] == 'data' && !empty($campo['valor'])) {
                                                // Formatar data para o padrão brasileiro
                                                $data = new DateTime($campo['valor']);
                                                echo $data->format('d/m/Y');
                                            } else {
                                                echo htmlspecialchars($campo['valor']); 
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info mt-3">Este produto não possui campos adicionais.</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="ficha.php?categoria=<?php echo $idcategoria; ?>" class="btn btn-secondary">Cancelar</a>
                            <a href="delete.php?id=<?php echo $id_produto; ?>&confirmar=sim" class="btn btn-danger">Confirmar Exclusão</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>