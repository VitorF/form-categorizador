<?php
require_once 'conexao.php';
session_start(); 

// Verificar se uma categoria específica foi selecionada
if(isset($_GET['categoria'])){
    $query = $_GET['categoria'];
    $filtroCategoria = true;
} else {
    $query = '9999';
    $filtroCategoria = false;
}

// Verificar se há mensagem para exibir e qual estilo colocar em cada uma
if(isset($_GET['mensagem'])){
    switch ($_GET['mensagem']) {
        case 'Produto atualizado com sucesso!':
            $msg = "<div class='alert alert-success'>$_GET[mensagem]</div>";
            break;
        case 'Gravado com sucesso!':
            $msg = "<div class='alert alert-success'>$_GET[mensagem]</div>";
            break;
        case 'Produto excluído com sucesso!':
            $msg = "<div class='alert alert-success'>$_GET[mensagem]</div>";
            break;
        case 'Erro ao gravar':
            $msg = "<div class='alert alert-danger'>$_GET[mensagem]</div>";

        default:
            $msg = "<div class='alert alert-warning'>$_GET[mensagem]</div>";
            break;
    }
} else {
    $msg = '';
}

// Buscar todas as categorias para o dropdown e para os botões
$sql = "SELECT * FROM categorias";
$consulta = $pdo->query($sql);
$categorias_array = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Construir o dropdown de categorias
$htmlprojetos = "";
$htmlprojetos = $htmlprojetos . '<select id="idcategoria" name="idcategoria" onchange="RecarregaCategoria(this.value)">';
$selecionado = "";
$consulta->execute(); // Reexecutar a consulta para usar novamente
while($exibe=$consulta->fetch(PDO::FETCH_ASSOC)){
    if($exibe['id'] == $query){
        $selecionado = " selected ";
    }
    $htmlprojetos = $htmlprojetos . '<option value="' . $exibe['id'] . '" ' . $selecionado .  '">' . $exibe['nome'] . "</option>";
    $selecionado = " ";
}
$htmlprojetos = $htmlprojetos . "</select>";
$categorias = $htmlprojetos;

// Buscar campos da categoria selecionada
$sql = "SELECT C.id, C.nome, C.tipodedado FROM campos C INNER JOIN camposcategorias CT ON C.id = CT.idcampo WHERE CT.idcategoria = " . $query;
$consulta = $pdo->query($sql);
$htmlprojetos = "";

// Construir o formulário de cadastro
$htmlprojetos = $htmlprojetos . '<form method="post" action="gravador.php">';
$htmlprojetos = $htmlprojetos . "<table>";
$htmlprojetos = $htmlprojetos . "<tr><td>Código do produto:</td><td><input type='number' placeholder='numerico' id='idproduto' name='idproduto'></input></td><tr>";
$htmlprojetos = $htmlprojetos . "<tr><td>Nome do produto:</td><td><input type='text' placeholder='texto' id='nomeproduto' name='nomeproduto'></input></td><tr>";
$htmlprojetos = $htmlprojetos . "<tr><td>Categoria:</td><td>" . $categorias . "</td><tr>";

while($exibe=$consulta->fetch(PDO::FETCH_ASSOC)) {
    switch ($exibe['tipodedado']) {
        case 'numerico':
             $htmlprojetos = $htmlprojetos . "<tr><td>" . $exibe['nome'] . ":</td><td><input placeholder='" . $exibe['tipodedado'] . "' type='number' id='" . $exibe['id'] . "' name='" . $exibe['id'] . "'></input></td><tr>";
             break;
        case 'texto':
             $htmlprojetos = $htmlprojetos . "<tr><td>" . $exibe['nome'] . ":</td><td><input placeholder='" . $exibe['tipodedado'] . "' type='text' id='" . $exibe['id'] . "' name='" . $exibe['id'] . "'></input></td><tr>";
             break;
        case 'data':
             $htmlprojetos = $htmlprojetos . "<tr><td>" . $exibe['nome'] . ":</td><td><input type='date' id='" . $exibe['id'] . "' name='" . $exibe['id'] . "'></input></td><tr>";
             break;
        default:
           $htmlprojetos = $htmlprojetos . "<tr><td>" . $exibe['nome'] . ":</td><td><input placeholder='texto' id='" . $exibe['id'] . "' name='" . $exibe['id'] . "'></input></td><tr>";
            break;
    }
}
$htmlprojetos = $htmlprojetos . "</table>";
$htmlprojetos = $htmlprojetos . "<br><br><input type='submit' value='Gravar Produto' class='btn btn-success'></form>";

// Pesquisa
$searchTerm = '';
if (isset($_GET["search"]) && $_GET["search"] !== '') {
    $searchTerm = $_GET['search'];
    $searchParam = '%' . $searchTerm . '%';

    // Se tiver uma categoria selecionada, filtra por categoria também
    if ($filtroCategoria && $query != '9999') {
        $stmt = $pdo->prepare("
            SELECT 
                p.id AS id_produto,
                p.nome AS nome_produto,
                c.nome AS nome_categoria,
                cp.nome AS nome_campo,
                pd.valor AS valor_campo
            FROM 
                prod p
            JOIN 
                categorias c ON p.idcategoria = c.id
            LEFT JOIN 
                produtosdados pd ON pd.idproduto = p.id
            LEFT JOIN 
                campos cp ON pd.idcampo = cp.id
            WHERE 
                p.idcategoria = ? AND
                (CAST(p.id AS CHAR) LIKE ? OR
                p.nome LIKE ? OR
                pd.valor LIKE ?)
            ORDER BY 
                p.id, cp.id
        ");
        $stmt->bindValue(1, $query, PDO::PARAM_INT);
        $stmt->bindValue(2, $searchParam, PDO::PARAM_STR);
        $stmt->bindValue(3, $searchParam, PDO::PARAM_STR);
        $stmt->bindValue(4, $searchParam, PDO::PARAM_STR);
    } else {
        // Pesquisa sem filtro de categoria
        $stmt = $pdo->prepare("
            SELECT 
                p.id AS id_produto,
                p.nome AS nome_produto,
                c.nome AS nome_categoria,
                cp.nome AS nome_campo,
                pd.valor AS valor_campo
            FROM 
                prod p
            JOIN 
                categorias c ON p.idcategoria = c.id
            LEFT JOIN 
                produtosdados pd ON pd.idproduto = p.id
            LEFT JOIN 
                campos cp ON pd.idcampo = cp.id
            WHERE 
                CAST(p.id AS CHAR) LIKE ? OR
                p.nome LIKE ? OR
                c.nome LIKE ? OR
                pd.valor LIKE ?
            ORDER BY 
                p.id, cp.id
        ");
        $stmt->bindValue(1, $searchParam, PDO::PARAM_STR);
        $stmt->bindValue(2, $searchParam, PDO::PARAM_STR);
        $stmt->bindValue(3, $searchParam, PDO::PARAM_STR);
        $stmt->bindValue(4, $searchParam, PDO::PARAM_STR);
    }
    $stmt->execute();
} else {
    // Sem pesquisa, mas com filtro de categoria
    if ($filtroCategoria && $query != '9999') {
        $stmt = $pdo->prepare("
            SELECT 
                p.id AS id_produto,
                p.nome AS nome_produto,
                c.nome AS nome_categoria,
                cp.nome AS nome_campo,
                pd.valor AS valor_campo
            FROM 
                prod p
            JOIN 
                categorias c ON p.idcategoria = c.id
            LEFT JOIN 
                produtosdados pd ON pd.idproduto = p.id
            LEFT JOIN 
                campos cp ON pd.idcampo = cp.id
            WHERE 
                p.idcategoria = ?
            ORDER BY 
                p.id, cp.id
        ");
        $stmt->bindValue(1, $query, PDO::PARAM_INT);
    } else {
        // Sem filtro: retorna todos os produtos
        $stmt = $pdo->prepare("
            SELECT 
                p.id AS id_produto,
                p.nome AS nome_produto,
                c.nome AS nome_categoria,
                cp.nome AS nome_campo,
                pd.valor AS valor_campo
            FROM 
                prod p
            JOIN 
                categorias c ON p.idcategoria = c.id
            LEFT JOIN 
                produtosdados pd ON pd.idproduto = p.id
            LEFT JOIN 
                campos cp ON pd.idcampo = cp.id
            ORDER BY 
                p.id, cp.id
        ");
    }
    $stmt->execute();
}

// Buscar campos específicos da categoria para cabeçalho da tabela
$campos_categoria = [];
if ($filtroCategoria && $query != '9999') {
    $stmtCampos = $pdo->prepare("
        SELECT C.id, C.nome
        FROM campos C 
        INNER JOIN camposcategorias CT ON C.id = CT.idcampo 
        WHERE CT.idcategoria = ?
    ");
    $stmtCampos->execute([$query]);
    $campos_categoria = $stmtCampos->fetchAll(PDO::FETCH_ASSOC);
}

// Organizar os dados para exibição
$produtos = [];
$produtos_dados = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id_produto = $row['id_produto'];
    
    if (!isset($produtos[$id_produto])) {
        $produtos[$id_produto] = [
            'id' => $row['id_produto'],
            'nome' => $row['nome_produto'],
            'categoria' => $row['nome_categoria'],
            'campos' => []
        ];
    }
    
    if ($row['nome_campo'] && $row['valor_campo']) {
        $produtos[$id_produto]['campos'][$row['nome_campo']] = $row['valor_campo'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <title>Gerenciamento de Produtos</title>
    <script>
        function searchData() {
            var search = document.getElementById("pesquisar");
            var currentUrl = window.location.href;
            var baseUrl = currentUrl.split('?')[0];
            var params = new URLSearchParams(window.location.search);

            params.set('search', search.value);

            // Manter o filtro de categoria se existir
            if (params.has('categoria')) {
                // Já está definido, não precisa fazer nada
            }

            window.location.href = baseUrl + '?' + params.toString();
        }

        function RecarregaCategoria(valor) {
            window.location.href = "ficha.php?categoria=" + valor;
        }

        function filtrarPorCategoria(categoriaId) {
            var currentUrl = window.location.href;
            var baseUrl = currentUrl.split('?')[0];
            var params = new URLSearchParams(window.location.search);
            
            params.set('categoria', categoriaId);
            
            // Manter a pesquisa se existir
            if (params.has('search') && params.get('search') !== '') {
                // Já está definido, não precisa fazer nada
            } else {
                params.delete('search');
            }
            
            window.location.href = baseUrl + '?' + params.toString();
        }

        function mostrarTodos() {
            var currentUrl = window.location.href;
            var baseUrl = currentUrl.split('?')[0];
            window.location.href = baseUrl;
        }
    </script>
    <style>
        .box-search {
            display: flex;
            justify-content: center;
            gap: 1%;
            margin-bottom: 20px;
        }
        
        .category-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .category-buttons .btn {
            min-width: 120px;
        }
        
        .active-category {
            border: 2px solid #0d6efd;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <center><h3>Sistema de Gerenciamento de Produtos</h3></center>
        
        <?php echo $msg; ?>
        
        <!-- Botões de categorias -->
        <div class="category-buttons">
            <button class="btn btn-outline-secondary <?php echo (!$filtroCategoria) ? 'active-category' : ''; ?>" onclick="mostrarTodos()">
                Todos os Produtos
            </button>
            
            <?php foreach ($categorias_array as $cat): ?>
                <button class="btn btn-outline-primary <?php echo ($filtroCategoria && $query == $cat['id']) ? 'active-category' : ''; ?>" 
                        onclick="filtrarPorCategoria(<?php echo $cat['id']; ?>)">
                    <?php echo htmlspecialchars($cat['nome']); ?>
                </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Formulário de cadastro -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Cadastro de Produto</h5>
            </div>
            <div class="card-body">
                <?php echo $htmlprojetos; ?>
            </div>
        </div>
        
        <!-- Barra de pesquisa -->
        <div class="box-search">
            <input type="search" class="form-control w-50" placeholder="Pesquisar" 
                   id="pesquisar" value="<?php echo htmlspecialchars($searchTerm); ?>" 
                   onkeydown="if(event.key === 'Enter'){ searchData(); }">
            <button class="btn btn-primary" onclick="searchData()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                </svg>
            </button>
        </div>

        <!-- Tabela de produtos -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nome do produto</th>
                        <?php if (!$filtroCategoria): ?>
                            <th scope="col">Categoria</th>
                            <th scope="col">Campo</th>
                            <th scope="col">Valor</th>
                        <?php else: ?>
                            <?php foreach ($campos_categoria as $campo): ?>
                                <th scope="col"><?php echo htmlspecialchars($campo['nome']); ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($filtroCategoria && !empty($produtos)) {
                        // Exibição por categoria com colunas específicas
                        foreach ($produtos as $produto) {
                            echo "<tr>";
                            echo "<td>" . $produto['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($produto['nome']) . "</td>";
                            
                            // Exibir valores para cada campo da categoria
                            foreach ($campos_categoria as $campo) {
                                $valor = isset($produto['campos'][$campo['nome']]) ? $produto['campos'][$campo['nome']] : '-';
                                echo "<td>" . htmlspecialchars($valor) . "</td>";
                            }
                            
                            // Botões de ação
                            echo "<td>
                                <a class='btn btn-sm btn-primary' href='edit.php?id={$produto['id']}'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pencil' viewBox='0 0 16 16'>
                                        <path d='M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325'/>
                                    </svg>
                                </a>
                                <a class='btn btn-sm btn-danger' href='delete.php?id={$produto['id']}'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash' viewBox='0 0 16 16'>
                                        <path d='M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z'/>
                                        <path d='M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z'/>
                                    </svg>
                                </a>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        // Exibição padrão (sem filtro de categoria)
                        $stmt->execute(); // Reexecutar a consulta
                        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . $data['id_produto'] . "</td>";
                            echo "<td>" . htmlspecialchars($data['nome_produto']) . "</td>";
                            echo "<td>" . htmlspecialchars($data['nome_categoria']) . "</td>";
                            echo "<td>" . htmlspecialchars($data['nome_campo'] ?? '-') . "</td>";
                            echo "<td>" . htmlspecialchars($data['valor_campo'] ?? '-') . "</td>";
                            echo "<td>
                                <a class='btn btn-sm btn-primary' href='edit.php?id={$data['id_produto']}'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-pencil' viewBox='0 0 16 16'>
                                        <path d='M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325'/>
                                    </svg>
                                </a>
                                <a class='btn btn-sm btn-danger' href='delete.php?id={$data['id_produto']}'>
                                    <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-trash' viewBox='0 0 16 16'>
                                        <path d='M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z'/>
                                        <path d='M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z'/>
                                    </svg>
                                </a>
                            </td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"></script>
</body>
</html>