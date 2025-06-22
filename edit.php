<?php
require_once 'conexao.php';
session_start();

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ficha.php?mensagem=ID do produto inválido.");
    exit();
}

$id_produto = $_GET['id'];

// Processar o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomeproduto = $_POST['nomeproduto'];
    $idcategoria = $_POST['idcategoria'];

    try {
        // Atualizar produto
        $stmt = $pdo->prepare("UPDATE prod SET nome = ?, idcategoria = ? WHERE id = ?");
        $stmt->execute([$nomeproduto, $idcategoria, $id_produto]);

        // Atualizar campos adicionais
        // Primeiro, excluir os dados existentes
        $stmtDelete = $pdo->prepare("DELETE FROM produtosdados WHERE idproduto = ?");
        $stmtDelete->execute([$id_produto]);

        // Inserir novos valores dos campos
        for ($i = 0; $i < 999; $i++) {
            if (isset($_POST[$i])) {
                $valorCampo = $_POST[$i];
                $stmtCampo = $pdo->prepare("INSERT INTO produtosdados(idproduto, idcampo, valor) VALUES (?, ?, ?)");
                $stmtCampo->execute([$id_produto, $i, $valorCampo]);
            }
        }

        header("Location: ficha.php?mensagem=Produto atualizado com sucesso!&categoria=" . $idcategoria);
        exit();
    } catch (PDOException $e) {
        header("Location: ficha.php?mensagem=Erro ao atualizar: " . urlencode($e->getMessage()));
        exit();
    }
}

// Buscar dados do produto
$stmt = $pdo->prepare("SELECT * FROM prod WHERE id = ?");
$stmt->execute([$id_produto]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header("Location: ficha.php?mensagem=Produto não encontrado.");
    exit();
}

// Buscar categoria do produto
$idcategoria = $produto['idcategoria'];

// Buscar valores dos campos do produto
$stmtCampos = $pdo->prepare("
    SELECT pd.idcampo, pd.valor 
    FROM produtosdados pd 
    WHERE pd.idproduto = ?
");
$stmtCampos->execute([$id_produto]);
$camposValores = [];
while ($campo = $stmtCampos->fetch(PDO::FETCH_ASSOC)) {
    $camposValores[$campo['idcampo']] = $campo['valor'];
}

// Buscar categorias para o dropdown
$sql = "SELECT * FROM categorias";
$consulta = $pdo->query($sql);
$htmlprojetos = "";
$htmlprojetos = $htmlprojetos . '<select id="idcategoria" name="idcategoria" onchange="RecarregaCategoria(this.value)">';
$selecionado = "";
while ($exibe = $consulta->fetch(PDO::FETCH_ASSOC)) {
    if ($exibe['id'] == $idcategoria) {
        $selecionado = " selected ";
    }
    $htmlprojetos = $htmlprojetos . '<option value="' . $exibe['id'] . '" ' . $selecionado . '">' . $exibe['nome'] . "</option>";
    $selecionado = " ";
}
$htmlprojetos = $htmlprojetos . "</select>";
$categorias = $htmlprojetos;

// Buscar campos da categoria
$sql = "SELECT C.id, C.nome, C.tipodedado FROM campos C INNER JOIN camposcategorias CT ON C.id = CT.idcampo WHERE CT.idcategoria = " . $idcategoria;
$consulta = $pdo->query($sql);
$htmlprojetos = "";

$htmlprojetos = $htmlprojetos . '<form method="post" action="edit.php?id=' . $id_produto . '">';
$htmlprojetos = $htmlprojetos . "<table>";
$htmlprojetos = $htmlprojetos . "<tr><td>Código do produto:</td><td><input id='idproduto' name='idproduto' value='" . $id_produto . "' readonly></input></td><tr>";
$htmlprojetos = $htmlprojetos . "<tr><td>Nome do produto:</td><td><input id='nomeproduto' name='nomeproduto' value='" . htmlspecialchars($produto['nome']) . "'></input></td><tr>";
$htmlprojetos = $htmlprojetos . "<tr><td>Categoria:</td><td>" . $categorias . "</td><tr>";

while ($exibe = $consulta->fetch(PDO::FETCH_ASSOC)) {
    $valorCampo = isset($camposValores[$exibe['id']]) ? htmlspecialchars($camposValores[$exibe['id']]) : '';
    
    switch ($exibe['tipodedado']) {
        case 'numerico':
            $htmlprojetos = $htmlprojetos . "<tr><td>" . $exibe['nome'] . ":</td><td><input placeholder='" . $exibe['tipodedado'] . "' type='number' id='" . $exibe['id'] . "' name='" . $exibe['id'] . "' value='" . $valorCampo . "'></input></td><tr>";
            break;
        case 'texto':
            $htmlprojetos = $htmlprojetos . "<tr><td>" . $exibe['nome'] . ":</td><td><input placeholder='" . $exibe['tipodedado'] . "' type='text' id='" . $exibe['id'] . "' name='" . $exibe['id'] . "' value='" . $valorCampo . "'></input></td><tr>";
            break;
        case 'data':
            $htmlprojetos = $htmlprojetos . "<tr><td>" . $exibe['nome'] . ":</td><td><input type='date' id='" . $exibe['id'] . "' name='" . $exibe['id'] . "' value='" . $valorCampo . "'></input></td><tr>";
            break;
        default:
            $htmlprojetos = $htmlprojetos . "<tr><td>" . $exibe['nome'] . ":</td><td><input placeholder='texto' id='" . $exibe['id'] . "' name='" . $exibe['id'] . "' value='" . $valorCampo . "'></input></td><tr>";
            break;
    }
}
$htmlprojetos = $htmlprojetos . "</table>";
$htmlprojetos = $htmlprojetos . "<br><br><input type='submit' value='Atualizar Produto'></form>";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <title>Editar Produto</title>
    <script>
        function RecarregaCategoria(valor) {
            window.location.href = "edit.php?id=<?php echo $id_produto; ?>&categoria=" + valor;
        }
    </script>
</head>
<body>
    <div class="container mt-4">
        <center><h3>Editar Produto</h3></center>
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <?php
                if (isset($_GET['mensagem'])) {
                    echo '<div class="alert alert-info">' . $_GET['mensagem'] . '</div>';
                }
                echo $htmlprojetos;
                ?>
                <br>
                <a href="ficha.php" class="btn btn-secondary">Voltar para Lista</a>
            </div>
        </div>
    </div>
</body>
</html>