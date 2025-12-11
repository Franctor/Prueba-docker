<?php

$host = 'db';
$dbname = 'encuesta';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
}


$stmt = $pdo->query("SELECT si, no FROM votos WHERE id = 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['voto'] === 'si') {
        $data['si']++;
    }
    if ($_POST['voto'] === 'no') {
        $data['no']++;
    }

    $update = $pdo->prepare("UPDATE votos SET si = :si, no = :no WHERE id = 1");
    $update->execute([
        ':si' => $data['si'],
        ':no' => $data['no']
    ]);
}

?>

<p>¿Independizar Linares de la provincia de Jaén?</p>

<form method="post">
    <button name="voto" value="si">Sí</button>
    <button name="voto" value="no">No</button>
</form>

<h1>Resultados</h1>
<p>Sí: <?= $data['si'] ?></p>
<p>No: <?= $data['no'] ?></p>

<p>Servidor: <?= gethostname() ?></p>
