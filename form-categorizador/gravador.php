<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomeproduto = $_POST['nomeproduto'];
    $idproduto   = $_POST['idproduto'];
    $idcategoria = $_POST['idcategoria'];

    // Validação do ID
    if (!isset($idproduto) || !is_numeric($idproduto) || $idproduto <= 0) {
        header("Location: ficha.php?mensagem=ID do produto inválido.");
        exit();
    }

    $verifica = $pdo->prepare("
        SELECT p.nome AS nome_produto, c.nome AS categoria
        FROM prod p
        INNER JOIN categorias c ON p.idcategoria = c.id
        WHERE p.id = ?
    ");
    $verifica->execute([$idproduto]);
    $produto = $verifica->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        // Produto duplicado encontrado. Agora buscar os dados personalizados.
        $dadosStmt = $pdo->prepare("
            SELECT cam.nome AS campo, pd.valor, cam.tipodedado
            FROM produtosdados pd
            INNER JOIN campos cam ON pd.idcampo = cam.id
            WHERE pd.idproduto = ?
        ");
        $dadosStmt->execute([$idproduto]);
        $campos = $dadosStmt->fetchAll(PDO::FETCH_ASSOC);

        // Construir a mensagem detalhada
        $mensagem = "Já existe um produto com o ID $idproduto<br>";
        $mensagem .= "<b>Nome</b>: {$produto['nome_produto']}<br>";
        $mensagem .= "<b>Categoria</b>: {$produto['categoria']}<br><br>";
        $mensagem .= "<h5 class='mb-2'>Dados personalizados</h5>";

        foreach ($campos as $campo) {
            $mensagem .= "<b>{$campo['campo']}:</b> {$campo['valor']}<br>";
        }

        // Redirecionar com a mensagem codificada para URL (pode exibir em ficha.php)
        header("Location: ficha.php?mensagem=" . urlencode($mensagem));
        exit();
    }


    try {
        // Inserir produto
        $stmt = $pdo->prepare("INSERT INTO prod(id, nome, idcategoria) VALUES (?, ?, ?)");
        $stmt->execute([$idproduto, $nomeproduto, $idcategoria]);

        // Inserir campos adicionais
        for ($i = 0; $i < 999; $i++) {
            if (isset($_POST[$i])) {
                $valorCampo = $_POST[$i];
                $stmtCampo = $pdo->prepare("INSERT INTO produtosdados(idproduto, idcampo, valor) VALUES (?, ?, ?)");
                $stmtCampo->execute([$idproduto, $i, $valorCampo]);
            }
        }

        header("Location: ficha.php?mensagem=Gravado com sucesso!&categoria=" . $idcategoria);
        exit();
    } catch (PDOException $e) {
        header("Location: ficha.php?mensagem=Erro ao gravar: " . urlencode($e->getMessage()));
        exit();
    }
}

































